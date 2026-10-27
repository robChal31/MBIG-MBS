<?php
session_start();
include 'db_con.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = require 'config.php';

if (!isset($_SESSION['username'])) {
    die("Unauthorized Access. Silakan login terlebih dahulu.");
}

// ===================== FUNGSI SEND EMAIL (SAMA PERSIS) =====================
function sendEmail($email, $name, $subject, $message, $config, $fileUrl, $cc = [], $extraAttachments = []) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP(); 
        $mail->Host       = $config['host']; 
        $mail->SMTPAuth   = true; 
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password']; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $config['port'] ?? 465;

        $mail->setFrom('mbigbenefit@mentarigroups.com', 'Benefit Auto Mailer');

        $file_path = 'draft-benefit/' . $fileUrl . '.xlsx';
        if (file_exists($file_path)) {
            $mail->addAttachment($file_path, $fileUrl . '.xlsx');
        }

        foreach ($extraAttachments as $att) {
            if (!empty($att['content']) && !empty($att['name'])) {
                $mail->addStringAttachment($att['content'], $att['name'], PHPMailer::ENCODING_BASE64, $att['mime'] ?? 'application/octet-stream');
            }
        }

        $mail->addAddress($email, $name);
        foreach ($cc as $value) {
            $mail->addCC($value['email'], $value['name']);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->send();
        return ['status' => true, 'message' => 'Email berhasil dikirim ke ' . $email];
    } catch (Exception $e) {
        return ['status' => false, 'message' => "Gagal kirim ke $email. Error: {$mail->ErrorInfo}"];
    }
}

// Helper untuk ambil token approval
function getToken($conn, $id_draft, $approver_id) {
    $sql = "SELECT token FROM draft_approval WHERE id_draft = $id_draft AND id_user_approver = $approver_id ORDER BY id_draft_approval DESC LIMIT 1";
    $res = mysqli_query($conn, $sql);
    return ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res)['token'] : '';
}

// 🔥 HELPER BARU: Ambil Notes berdasarkan status (1 = Approve, 2 = Reject)
function getLatestNotes($conn, $id_draft, $status) {
    $sql = "SELECT notes FROM draft_approval WHERE id_draft = $id_draft AND status = $status ORDER BY id_draft_approval DESC LIMIT 1";
    $res = mysqli_query($conn, $sql);
    return ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res)['notes'] : '';
}
// ===================== END FUNGSI =====================

$message_result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_draft'])) {
    $id_draft = (int) $_POST['id_draft'];

    // 1. Ambil Data Draft, EC, dan Sekolah
    $sql_draft = "SELECT db.*, ec.username AS ec_email, ec.generalname AS ec_name, ec.sa_email, 
                         IFNULL(sc.name, db.school_name) AS school_name, prog.name as program_name
                  FROM draft_benefit db 
                  LEFT JOIN user ec ON ec.id_user = db.id_ec
                  LEFT JOIN schools sc ON sc.id = db.school_name
                  LEFT JOIN programs prog ON (prog.code = db.program OR prog.name = db.program)
                  WHERE db.id_draft = $id_draft";
    $draft = mysqli_fetch_assoc(mysqli_query($conn, $sql_draft));

    if (!$draft) {
        $message_result = "<div class='alert alert-danger'>Draft ID $id_draft tidak ditemukan.</div>";
    } else {
        $school_name = $draft['school_name'];
        $program     = strtoupper($draft['program_name'] ?? $draft['program']);
        $ec_email    = $draft['ec_email'];
        $ec_name     = $draft['ec_name'];
        $fileUrl     = $draft['fileUrl'];
        $year        = $draft['year'] ?? 1;
        $saemail     = $draft['sa_email'];
        $id_ec       = $draft['id_ec'];

        // 2. Ambil Data Leader
        $sql_leader = "SELECT lead1.username as lead1_email, lead1.generalname as lead1_name, lead1.id_user as lead1_id,
                              lead2.username as lead2_email, lead2.generalname as lead2_name, lead2.id_user as lead2_id,
                              lead3.username as lead3_email, lead3.generalname as lead3_name, lead3.id_user as lead3_id
                       FROM user_segments us 
                       LEFT JOIN user lead1 ON lead1.id_user = us.leadid 
                       LEFT JOIN user lead2 ON lead2.id_user = us.leadid2 
                       LEFT JOIN user lead3 ON lead3.id_user = us.leadid3 
                       WHERE us.id_user = '$id_ec' AND us.segment_id = '{$draft['segment']}' LIMIT 1";
        $leaders = mysqli_fetch_assoc(mysqli_query($conn, $sql_leader));

        // 3. Ambil Baris Approval Terakhir (untuk cek siapa yang sedang pending)
        $sql_latest = "SELECT da.*, u.generalname as approver_name FROM draft_approval da 
                       LEFT JOIN user u ON u.id_user = da.id_user_approver 
                       WHERE da.id_draft = $id_draft ORDER BY da.id_draft_approval DESC LIMIT 1";
        $latest = mysqli_fetch_assoc(mysqli_query($conn, $sql_latest));
        $app_id = $latest['id_user_approver'] ?? null;
        $approver_name = $latest['approver_name'] ?? '';

        // 4. Tentukan Status & Kirim Email
        if ($draft['status'] == 2) {
            // ==========================================
            // === STATUS: REJECTED ===
            // ==========================================
            $latest_notes = getLatestNotes($conn, $id_draft, 2); // Ambil notes dari yang reject

            $subject = $year == 1 ? "Pengajuan $program $school_name BELUM DISETUJUI" : "Pengajuan perubahan PK Tahun Ke-$year $school_name BELUM DISETUJUI";
            $message = 'Wah, sedikit lagi nih! Formulir yang kamu ajukan masih perlu diperbaiki. 
            SEGERA Lakukan revisi sesuai notes dari Leader kamu dan ajukan kembali ke Top Leader. 
            Kamu pasti bisa, semangat ya!<br><br>

            <strong>Notes dari Approver:</strong><br>
            <div style="background:#f8f9fa;padding:10px;border-left:4px solid #dc3545;margin-top:6px;">
                '.nl2br(htmlspecialchars($latest_notes)).'
            </div>';

            $approver_query = "SELECT u.generalname as name, u.username as email 
                                FROM `draft_approval` AS da 
                                LEFT JOIN user as u on u.id_user = da.id_user_approver
                                where da.id_draft = $id_draft AND da.status = 1";
            $result = $conn->query($approver_query);
            $cc = [];
            if ($result->num_rows > 0) {  
                while ($row = $result->fetch_assoc()) {
                    $cc[] = ['email' => $row['email'], 'name' => $row['name']];
                }
            }
            $send_result = sendEmail($ec_email, $ec_name, $subject, $message, $config, $fileUrl, $cc);
            $message_result = "<div class='alert alert-warning'>Status: <b>Rejected</b>. Mengirim ulang email penolakan ke EC beserta Notes dari Approver yang reject.</div>";

        } elseif ($draft['confirmed'] == 1) {
            // ==========================================
            // === STATUS: CONFIRMED (HoS Admin) ===
            // ==========================================
            $latest_notes = getLatestNotes($conn, $id_draft, 1); // Ambil notes dari Admin/HoS
            
            $cc = [['email' => $draft['ec_email'], 'name' => $draft['ec_name']]];
            
            $sql_unit = "SELECT  db.*, dbl.id_template, br.code, user.username, user.generalname
                        FROM draft_benefit db 
                        LEFT JOIN draft_benefit_list dbl ON db.id_draft = dbl.id_draft
                        LEFT JOIN benefit_role br ON br.id_template = dbl.id_template 
                        LEFT JOIN user ON (CASE WHEN br.code = 'mkt' THEN user.role = 'admin' ELSE user.role = br.code END)
                        WHERE db.verified = 1 AND db.id_draft = $id_draft GROUP BY br.code";
            $res_unit = mysqli_query($conn, $sql_unit);
            while ($u = $res_unit->fetch_assoc()) {   
                $cc[] = ['email' => $u['username'], 'name' => $u['generalname']];
            }
            $cc[] = ['email' => "yully@mentaribooks.com", 'name' => "Yully"];

            $subject = $year == 1 ? "Program $program di $school_name Telah Berhasil Dikonfirmasi" : "Program perubahan PK Tahun Ke-$year di $school_name Telah Berhasil Dikonfirmasi";
            $message = "<p>Kami ingin menginformasikan bahwa program $program untuk $school_name telah berhasil dikonfirmasi oleh Head of Sales Admin.</p>
                        <p> Namun, untuk manfaat PDMTA, Beasiswa S2, Studi Banding dan Assessment, Admin wajib melakukan konfirmasi ulang kepada AR/ACCT terkait dengan ketentuan pembayaran sekolah.</p>
                        <p>Mohon untuk memperbarui data secara berkala di MBS selama perencanaan dan implementasi benefit berlangsung.<p>
                        <p>Sarangheyo, Kamsahamnida💖💖💖</p>" . '<br><br>
                        <strong>Notes dari Approver:</strong><br>
                        <div style="background:#f8f9fa;padding:10px;border-left:4px solid #dc3545;margin-top:6px;">'.nl2br(htmlspecialchars($latest_notes)).'</div>';

            $send_result = sendEmail('secretary@mentaribooks.com', 'Putri', $subject, $message, $config, $fileUrl, $cc);
            $message_result = "<div class='alert alert-info'>Status: <b>Confirmed</b>. Mengirim email konfirmasi ke Secretary (cc: EC & Unit Bisnis) beserta Notes.</div>";

        } elseif ($draft['status'] == 1) {
            // ==========================================
            // === STATUS: FINAL APPROVED (Top Leader) ===
            // ==========================================
            $latest_notes = getLatestNotes($conn, $id_draft, 1); // Ambil notes dari Top Leader

            $extraAttachments = [];
            if (file_exists('draft-benefit-doc-render.php')) {
                require_once 'draft-benefit-doc-render.php';
                if (function_exists('renderBenefitDoc')) {
                    $docContent = renderBenefitDoc($conn, (int)$id_draft, ['mhtml' => true]);
                    if ($docContent !== '') {
                        $safeSchool = preg_replace('/[^A-Za-z0-9_\- ]/', '', $school_name);
                        $extraAttachments[] = ['name' => 'Benefit - ' . $safeSchool . '.doc', 'content' => $docContent, 'mime' => 'application/msword'];
                    }
                }
            }

            /* ===== Fetch PIC dari school_pic ===== */
            $pic_rows = [];
            $pic_q = mysqli_query($conn, "SELECT name, jabatan, no_tlp, email FROM school_pic WHERE id_draft = '$id_draft' ORDER BY id ASC");
            if ($pic_q && mysqli_num_rows($pic_q) > 0) {
                while ($p = mysqli_fetch_assoc($pic_q)) { $pic_rows[] = $p; }
            }
            
            $pic_html = '';
            if (!empty($pic_rows)) {
                $pic_html .= '<br><br><strong>Informasi PIC Sekolah:</strong>
                    <table cellpadding="8" cellspacing="0" border="0" style="border-collapse:collapse; width:100%; margin-top:8px; font-size:13px; background:#ffffff; border:1px solid #dee2e6;">
                        <thead><tr style="background:#f1f3f5;">
                            <th style="text-align:left; border:1px solid #dee2e6; padding:8px;">Nama</th>
                            <th style="text-align:left; border:1px solid #dee2e6; padding:8px;">Jabatan</th>
                            <th style="text-align:left; border:1px solid #dee2e6; padding:8px;">No. Telp</th>
                            <th style="text-align:left; border:1px solid #dee2e6; padding:8px;">Email</th>
                        </tr></thead><tbody>';
                foreach ($pic_rows as $p) {
                    $pic_html .= '<tr>
                        <td style="border:1px solid #dee2e6; padding:8px;">' . htmlspecialchars($p['name'] ?? '-') . '</td>
                        <td style="border:1px solid #dee2e6; padding:8px;">' . htmlspecialchars($p['jabatan'] ?? '-') . '</td>
                        <td style="border:1px solid #dee2e6; padding:8px;">' . htmlspecialchars($p['no_tlp'] ?? '-') . '</td>
                        <td style="border:1px solid #dee2e6; padding:8px;">' . htmlspecialchars($p['email'] ?? '-') . '</td>
                    </tr>';
                }
                $pic_html .= '</tbody></table>';
            }

            $cc = [];
            if(!empty($saemail)) $cc[] = ['email' => $saemail, 'name' => explode('@', $saemail)[0]];
            $cc[] = ['email' => "ar@mentaribooks.com", 'name' => "AR"];
            $cc[] = ['email' => "secretary@mentaribooks.com", 'name' => "Putri"];
            $cc[] = ['email' => "yully@mentaribooks.com", 'name' => "Yully"];
            $cc[] = ['email' => "novitasari@mentaribooks.com", 'name' => "Novi"];

            $subject = $year == 1 ? "Yeay, formulir $school_name sudah disetujui!" : "Yeay, formulir perubahan PK Tahun Ke-$year $school_name sudah disetujui!";
            $message = "<p>Yeay, formulir $program buat $school_name sudah disetujui! Sekarang kamu bisa download formulirnya dan ajukan ke divisi terkait dengan happy-happy!</p>"
                . '<br><br>
                    <strong>Notes dari Approver:</strong><br>
                    <div style="background:#f8f9fa;padding:10px;border-left:4px solid #dc3545;margin-top:6px;">' . nl2br(htmlspecialchars($latest_notes)) . '</div>' . $pic_html;

            $send_result = sendEmail($ec_email, $ec_name, $subject, $message, $config, $fileUrl, $cc, $extraAttachments);
            $message_result = "<div class='alert alert-success'>Status: <b>Final Approved</b>. Mengirim email persetujuan beserta dokumen Word & PIC ke EC (dengan Notes dari Top Leader).</div>";

        } else {
            // ==========================================
            // === STATUS: PENDING (Tahap Approval) ===
            // ==========================================
            if ($app_id == $leaders['lead1_id']) {
                // Pending Leader 1 (Fase Submit PK)
                $token = getToken($conn, $id_draft, $leaders['lead1_id']);
                
                // Email ke EC
                sendEmail($ec_email, $ec_name, 'Woohoo, Pengajuan kamu sudah berhasil diajukan! Untuk program ' . $program. '  ' . $school_name, 'Wah, keren abis! Kamu sudah selesai isi formulir manfaat kerja sama ' . $program . ' untuk ' . $school_name . '. Selanjutnya, formulir kamu akan kita teruskan ke Leader untuk diperiksa, ya!', $config, $fileUrl);
                
                // Email ke Leader 1
                $messageLeader = "<style>* { font-family: Helvetica, sans-serif; } .container { width: 80%; margin: auto; }</style>
                    <div class='container'>
                        <p>$ec_name! Telah mengajukan formulir <strong>$program</strong> untuk <strong>$school_name</strong></p>
                        <p>Ayo, cepat-cepat dicek agar bisa segera diajukan ke Top Leader! Sukses untuk kita bersama! 👍😊</p>
                        <p>Silakan klik tombol berikut untuk approval dan pastikan akun kamu <strong>sudah login</strong> terlebih dahulu.</p>
                        <p style='margin: 20px 0px;'><a href='https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$token' style='background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px;' target='_blank'>Redirect me!</a></p>
                        <div style='border-bottom: 1px solid #ddd;'></div>
                        <p>Jika tombol tidak berfungsi dengan benar, silakan salin tautan berikut dan tambahkan ke peramban Anda </p>
                        <p style='color: #0096c7'>https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$token</p>
                        <div style='text-align: center; margin-top: 35px;'><span style='text-align: center; font-size: .85rem; color: #333'>Mentari Benefit System</span></div>
                    </div>";
                
                $ccList = [];
                if ($leaders['lead1_id'] == 70) {
                    $ccList[] = ['email' => "yully@mentaribooks.com", 'name' => "Yully"];
                }
                $send_result = sendEmail($leaders['lead1_email'], $leaders['lead1_name'], 'Keren, '.$ec_name.' telah mengajukan formulir '.$program.' untuk '.$school_name, $messageLeader, $config, $fileUrl, $ccList);
                
                $message_result = "<div class='alert alert-info'>Status: <b>Menunggu Leader 1</b>. Mengirim ulang email ke EC & Leader 1.</div>";

            } elseif ($app_id == $leaders['lead2_id']) {
                // Pending Leader 2 (Leader 1 sudah approve)
                $token = getToken($conn, $id_draft, $leaders['lead2_id']);
                $latest_notes = getLatestNotes($conn, $id_draft, 1); // Notes dari Leader 1
                
                sendEmail($ec_email, $ec_name, $year == 1 ? "Mantap! Formulir PK $school_name kamu sudah disetujui oleh Leader" : "Mantap! Formulir Perubahan PK Tahun Ke-$year $school_name kamu sudah disetujui oleh Leader", "<p>Mantap! Formulir kamu sudah disetujui oleh Leader, sekarang kita akan ajukan formulir kerja sama $program untuk $school_name ke Leader {$leaders['lead2_name']}.</p><p> Jika ada yang perlu direvisi atau disetujui, kita bakal kasih tau kamu lewat email.</p><p> Terima kasih.</p>", $config, $fileUrl);
                
                $msgL2 = "<style>* { font-family: Helvetica, sans-serif; } .container { width: 80%; margin: auto; }</style>
                    <div class='container'>
                        <p>$ec_name telah mengajukan formulir <strong>$program</strong> untuk <strong>$school_name</strong></p>
                        <p>Ayo, cepat-cepat dicek agar bisa segera diajukan ke Top Leader! Sukses untuk kita bersama! 👍😊</p>
                        <p>Silakan klik tombol berikut untuk approval dan pastikan akun kamu <strong>sudah login</strong> terlebih dahulu.</p>
                        <p style='margin: 20px 0px;'><a href='https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$token' style='background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px;' target='_blank'>Redirect me!</a></p>
                        <div style='border-bottom: 1px solid #ddd;'></div>
                        <p>Jika tombol tidak berfungsi dengan benar, silakan salin tautan berikut dan tambahkan ke peramban Anda </p>
                        <p style='color: #0096c7'>https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$token</p>
                        <div style='text-align: center; margin-top: 35px;'><span style='text-align: center; font-size: .85rem; color: #333'>Mentari Benefit System</span></div>
                    </div>";
                $send_result = sendEmail($leaders['lead2_email'], $leaders['lead2_name'], $year == 1 ? "Keren $approver_name telah menyetujui formulir $program untuk $school_name" : "Keren $approver_name telah menyetujui formulir perubahan PK Tahun Ke-$year $program untuk $school_name.", $msgL2, $config, $fileUrl);
                
                $message_result = "<div class='alert alert-info'>Status: <b>Menunggu Leader 2</b>. Mengirim ulang email ke EC & Leader 2 (beserta Notes dari Leader 1).</div>";

            } elseif ($app_id == $leaders['lead3_id'] || $app_id == 70) {
                // Pending Top Leader (Leader 2 sudah approve)
                $token = getToken($conn, $id_draft, $app_id);
                $latest_notes = getLatestNotes($conn, $id_draft, 1); // Notes dari Leader 2
                
                $cc = [
                    ['email' => "kelly@mentarigroups.com", 'name' => "Kelly"],
                    ['email' => "yully@mentaribooks.com", 'name' => "Yully"]
                ];
                if ($app_id == 16) {
                    $cc[] = ['email' => "santo@mentaribooks.com", 'name' => "Santo"];
                } else {
                    $cc[] = ['email' => "dwinanto@mentaribooks.com", 'name' => "Dwinanto"];
                }
                if(!empty($saemail)) $cc[] = ['email' => $saemail, 'name' => explode('@', $saemail)[0]];

                sendEmail($ec_email, $ec_name, 'Mentari Benefit | Formulir '.$school_name.' sudah disetujui oleh leader', $year == 1 ? "Yeay, formulir $program buat $school_name sudah disetujui oleh leader! Menunggu pengecekan dari secretary dan persetujuan top leader" : "Yeay, formulir perubahan PK Tahun Ke-$year $program buat $school_name sudah disetujui oleh leader! Menunggu pengecekan dari secretary dan persetujuan top leader", $config, $fileUrl, $cc);
                
                $msgTop = "<style>* { font-family: Helvetica, sans-serif; } .container { width: 80%; margin: auto; }</style>
                    <div class='container'>
                        <p>$ec_name telah mengajukan formulir <strong>$program</strong> untuk <strong>$school_name</strong></p>
                        <p>Wah, seru banget nih! $ec_name sudah menunggu kamu untuk memeriksa formulir $program di $school_name. Jika ada beberapa hal yang belum disetujui, berikan arahan dan masukan dengan baik dan konstruktif untuk membantu tim meningkatkan formulirnya.</p>
                        <p>Silakan klik tombol berikut untuk approval dan pastikan akun kamu <strong>sudah login</strong> terlebih dahulu.</p>
                        <p style='margin: 20px 0px;'><a href='https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$token' style='background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px;' target='_blank'>Redirect me!</a></p>
                        <div style='border-bottom: 1px solid #ddd;'></div>
                        <p>Jika tombol tidak berfungsi dengan benar, silakan salin tautan berikut dan tambahkan ke peramban Anda </p>
                        <p style='color: #0096c7'>https://mentarigroups.com/benefit/approve-draft-benefit-form.php?id_draft=$id_draft&token=$token</p>
                        <div style='text-align: center; margin-top: 35px;'><span style='text-align: center; font-size: .85rem; color: #333'>Mentari Benefit System</span></div>
                    </div>" . '<br><br>
                        <strong>Notes dari Approver:</strong><br>
                        <div style="background:#f8f9fa;padding:10px;border-left:4px solid #dc3545;margin-top:6px;">'.nl2br(htmlspecialchars($latest_notes)).'</div>';
                
                $send_result = sendEmail($leaders['lead3_email'], $leaders['lead3_name'], $year == 1 ? "Mentari Benefit | Formulir $school_name sedang menunggu pengecekan dan persetujuan Anda" : "Mentari Benefit | Formulir perubahan PK Tahun Ke-$year $school_name sedang menunggu persetujuan Anda", $msgTop, $config, $fileUrl);
                
                $message_result = "<div class='alert alert-info'>Status: <b>Menunggu Top Leader</b>. Mengirim ulang email ke EC (beserta CC) & Top Leader (beserta Notes dari Leader 2).</div>";

            } elseif ($app_id == 5) {
                // Pending Novi (Admin/HoS sudah verifikasi)
                $latest_notes = getLatestNotes($conn, $id_draft, 1); // Notes dari Admin
                $subject = $year == 1 ? "Program $program di $school_name Telah Berhasil diverifikasi" : "Program perubahan PK Tahun Ke-$year di $school_name Telah Berhasil diverifikasi";
                $message = "<p>Kami ingin menginformasikan bahwa program $program untuk $school_name telah berhasil diverifikasi oleh Marketing Secretary.</p><p> Mohon untuk segera mengecek dan konfirmasi program tersebut.</p><p>Sarangheyo, Kamsahamnida💖💖💖</p>" . '<br><br><strong>Notes dari Approver:</strong><br><div style="background:#f8f9fa;padding:10px;border-left:4px solid #dc3545;margin-top:6px;">'.nl2br(htmlspecialchars($latest_notes)).'</div>';
                $send_result = sendEmail('novitasari@mentaribooks.com', 'Novi', $subject, $message, $config, $fileUrl);
                $message_result = "<div class='alert alert-info'>Status: <b>Menunggu Verifikasi Admin</b>. Mengirim ulang email ke Novi (beserta Notes dari Admin).</div>";
            } else {
                $message_result = "<div class='alert alert-warning'>Gagal mendeteksi alur approval. Pastikan data <i>draft_approval</i> tidak kosong.</div>";
            }
        }

        if (isset($send_result)) {
            $alert_class = $send_result['status'] ? 'alert-success' : 'alert-danger';
            $message_result .= "<div class='alert $alert_class'>{$send_result['message']}</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auto-Resend Email - Mentari Benefit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { margin-top: 50px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0 !important; }
    </style>
</head>
<body>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h5 class="mb-0">⚡ Auto-Resend Email Tool</h5>
                    <small>Menggunakan template & Notes 100% original</small>
                </div>
                <div class="card-body p-4">
                    
                    <?= $message_result ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold">ID Draft Benefit</label>
                            <input type="number" name="id_draft" class="form-control form-control-lg text-center" placeholder="Contoh: 2329" required autofocus>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" onclick="this.innerHTML='Mendeteksi & Mengirim...'; this.disabled=true; this.form.submit();">
                            🚀 Deteksi & Kirim
                        </button>
                    </form>

                </div>
            </div>
            <div class="mt-3 text-center">
                <a href="draft-benefit.php" class="text-decoration-none">&larr; Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
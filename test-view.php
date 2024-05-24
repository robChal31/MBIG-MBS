

<style>
    * {
        font-family: Helvetica, sans-serif;
    }
    .container {
        width: 80%;
        margin: auto;
    }
</style>



<div class="container">
    <p>
        $_SESSION['generalname'].'! telah mengajukan formulir <strong> '.$program.' </strong> untuk <strong>'.$school_name.'.</strong> 
    </p>
    <p>Ayo, cepat dicek agar bisa segera diajukan ke Top Leader! Jangan lupa untuk memberikan semangat dan dukungan untuk tim kamu, ya! Sukses untuk kita bersama!</p>
    <br>Apabila sudah sesuai, silakan klik tombol berikut untuk approval.
    <p style="margin: 20px 0px;">
        <a href="https://mentarigroups.com/benefit/draft-approval.php?tok='.$tokenLeader.'&stat=1&idr='.$id_draft.'" style="background:#f77f00; color:#ffffff; font-weight:bold; text-decoration:none; padding: 10px 20px; border-radius: 8px; " target="_blank">
            Approve / Setujui!
        </a>
    </p>
    <div style="border-bottom: 1px solid #ddd;"></div>
    <p>Jika tombol tidak berfungsi dengan benar, silakan salin tautan berikut dan tambahkan ke peramban Anda </p>
    <p style="color: #0096c7">https://mentarigroups.com/benefit/draft-approval.php?tok='.$tokenLeader.'&stat=1&idr='.$id_draft.'</p>
    <div style="text-align: center; margin-top: 35px;">
        <span style="text-align: center; font-size: .85rem; color: #333">Mentari Benefit System</span>
    </div>
</div>
       
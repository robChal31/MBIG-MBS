<style>
  table.dataTable tbody td {
      padding : 2px !important;
      vertical-align: middle !important;
      text-align: center !important;
      font-size: .9rem !important;
  }

  table.dataTable thead th {
      font-size: .9rem !important;
  }
</style>

<?php
include 'header.php';

$posts      = $_POST ?? NULL;


?>

  <!-- Content Start -->
  <div class="content">
    <?php include 'navbar.php'; ?>

    <div class="container-fluid p-4">

      <div class="row">
        <div class="bg-whites rounded h-100 p-4">
          <div class="col-12">
            <div class="container py-4" style="font-size: .85rem;">
              <h5 class="text-center mb-4">REGULASI PERUBAHAN MANFAAT PROGRAM TAHUN KE-2 ATAU 3</h5>

              <p>Dokumen Persetujuan untuk Education Consultant (EC)</p>
              <p>Sebelum mengajukan perubahan manfaat program tahun ke-2, <strong>Education Consultant (EC)</strong> wajib membaca, memahami, dan menyetujui seluruh regulasi dan prosedur pengajuan yang tercantum di bawah ini.</p>
              
              <h6>A. Ketentuan Umum</h6>
              <ol>
                  <li>Perubahan manfaat program tahun ke-2 atau 3 diperbolehkan hanya jika terdapat penambahan omset minimal Rp10.000.000, yang berasal dari penambahan judul atau kuantitas (bukan dari kenaikan harga).</li>
                  <li>Pengajuan perubahan manfaat wajib diajukan oleh EC melalui alur sistem MBS dan harus didiskusikan terlebih dahulu dengan Leader dan PIC yang bertanggung jawab di sekolah.</li>
                  <li>Perubahan manfaat hanya berlaku khusus untuk tahun ke-2 atau 3. Tidak diperkenankan untuk melakukan perubahan pada manfaat tahun pertama maupun tahun ketiga.</li>
                  <li>Manfaat tahun sebelumnya akan otomatis dinonaktifkan apabila perubahan manfaat tahun ke-2 atau 3 telah disetujui.</li>
                  <li>Alokasi benefit pada pengajuan perubahan manfaat tidak boleh lebih rendah dari tahun pertama.</li>
                  <li>Setelah disetujui oleh Leader Marketing, PI akan diproses oleh Sales Admin (SA).</li>
                  <li>Setiap perubahan manfaat akan diikuti oleh pembuatan Amandemen/Addendum, yang harus diketahui dan ditandatangani oleh pihak sekolah.</li>
                  <li>Setiap kontrak yang dibatalkan oleh sekolah, maka sekolah wajib membuat surat pembatalan kontrak yang mencantumkan alasan dari sekolah.</li>
              </ol>
              
              <h6>B. Prosedur Pengajuan Perubahan</h6>
              <ol>
                  <li>EC membuat draft benefit di MBS.</li>
                  <li>Sistem akan menampilkan regulasi ini untuk dipahami dan disetujui.</li>
                  <li>Setelah menyetujui regulasi, EC mengisi kolom yang tersedia</li>
                  <li>EC memilih tahun ke-2 atau 3 untuk diajukan perubahan, lalu sistem akan menampilkan program yang sesuai.</li>
                  <li>Setelah mengisi semua kolom dan menekan tombol <strong style="text-decoration: underline">Proceed</strong>, sistem akan menampilkan Formulir Tahun ke-1.</li>
                  <li>EC dapat melakukan penyesuaian pada Formulir Perhitungan dan Formulir Manfaat sesuai dengan pemesanan dan kebutuhan sekolah.</li>
                  <li>Proses persetujuan akan mengikuti alur yang sama seperti pengajuan program tahun pertama.</li>
                  <li>Setelah disetujui oleh seluruh pihak terkait, EC dan SA akan menerima email berisi lampiran excel dengan subjek:<br>
                      <strong style="text-decoration: underline">FORMULIR PERUBAHAN TAHUN KE-2 [NAMA SEKOLAH] TELAH DISETUJUI</strong></li>
                  <li>Lampiran tersebut akan berisi dua sheet yaitu:
                      <ul>
                          <li>Formulir Tahun ke-1 dan Formulir Tahun ke-2</li>
                      </ul>
                      yang berfungsi sebagai pembanding dan rekam jejak program bagi leader dan admin.</li>
              </ol>
              
              <div class="highlight">
                  <p><strong>Dengan ini saya menyatakan telah membaca, memahami, dan menyetujui seluruh regulasi dan prosedur pengajuan perubahan manfaat program tahun ke-2 sebagaimana tertulis di atas.</strong></p>
                  <p>Saya siap mengikuti seluruh ketentuan yang berlaku dan bertanggung jawab atas setiap perubahan yang diajukan.</p>
              </div>
              
              <div class="d-flex justify-content-end mt-4">
                  <a class="btn btn-outline-primary" style="font-size: 13px" href="update-benefit-ec-input.php">
                      ✅ Ya, Saya Setuju dan Ingin Melanjutkan ke Tahap Pengajuan
                  </a>
              </div>
            </div>
          </div>
        </div>

      </div>
    
    <!-- Form End -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


<script type="text/javascript">


</script>
<?php include 'footer.php'; ?>
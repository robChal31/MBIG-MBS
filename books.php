<?php
include 'db_con.php';
include 'header.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if ($_SESSION['role'] != "admin") {
    $_SESSION['toast_status'] = 'Error';
    $_SESSION['toast_msg'] = 'Unauthorized Access';
    header('Location: ./draft-pk.php');
    exit();
}

/* =====================
   GET series_id (OPTIONAL)
   ===================== */
$series_id = $_GET['series_id'] ?? null;

/* =====================
   QUERY BOOKS
   ===================== */
$where = "WHERE book.is_active = 1";
if (!empty($series_id)) {
    $series_id = mysqli_real_escape_string($conn, $series_id);
    $where .= " AND book.book_series_id = '$series_id'";
}

$books = [];
$sql = "SELECT book.*, series.name AS series_name, subject.name AS subject_name, level.name AS level_name 
          FROM books AS book 
          LEFT JOIN book_series AS series ON series.id = book.book_series_id
          LEFT JOIN subjects AS subject ON subject.id = series.subject_id
          LEFT JOIN levels AS level ON level.id = series.level_id
          $where ORDER BY book.created_at DESC";
$res = mysqli_query($conn, $sql);
if (mysqli_num_rows($res) > 0) {
    $books = mysqli_fetch_all($res, MYSQLI_ASSOC);
}
?>

<style>
/* === UNIFIED STYLE (SAMA DENGAN PAGE LAIN) === */
.table-responsive { overflow-x: auto; }

table.dataTable thead th,
table.dataTable tbody td {
    font-size: .65rem !important;
    vertical-align: middle !important;
}

table.dataTable tbody td {
    padding: 6px !important;
}

.select2-container { z-index: 2050 !important; }

.modal { z-index: 1050; }
.modal-backdrop { z-index: 1040; }
</style>

<div class="content">
<?php include 'navbar.php'; ?>

<div class="container-fluid p-4">
  <div class="row">
    <div class="col-12">

      <div class="card rounded h-100 p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                  <h6 class="fw-semibold mb-0">Books</h6>
                  <small class="text-muted">Manage books</small>
              </div>
              <button type="button" class="btn btn-primary btn-sm" data-action="create" data-bs-toggle="modal" data-bs-target="#bookModal" id="add_book" data-bookseries="<?= $series_id ?>">
                <i class="fas fa-plus me-2"></i> Add Book
              </button>
          </div>

          <div class="table-responsive">
              <table class="table table-hover align-middle" id="table_id">
                  <thead>
                      <tr>
                          <th style="width:5%">ID</th>
                          <th>Item Code</th>
                          <th>Series Name</th>
                          <th>Subject</th>
                          <th>Level</th>
                          <th>Name</th>
                          <th>Grade</th>
                          <th>Type</th>
                          <th>Price</th>
                          <th>Is Active</th>
                          <th>Created At</th>
                          <th>Updated At</th>
                          <th>Deleted At</th>
                          <th class="text-center" style="width:10%">Action</th>
                      </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($books as $book): ?>
                      <tr>
                          <td class="text-center"><?= $book['id'] ?></td>
                          <td><?= $book['kode_barang'] ?></td>
                          <td><?= $book['series_name'] ?></td>
                          <td><?= $book['subject_name'] ?></td>
                          <td><?= $book['level_name'] ?></td>
                          <td><?= $book['name'] ?></td>
                          <td><?= $book['grade'] ?></td>
                          <td><?= $book['type'] ?></td>
                          <td><?= number_format($book['price'], 0, ',', '.') ?></td>
                          <td class="text-center">
                            <?= $book['is_active']
                              ? "<i class='fa fa-check-circle text-success'></i>"
                              : "<i class='fa fa-minus-circle text-danger'></i>" ?>
                          </td>
                          <td><?= $book['created_at'] ?></td>
                          <td><?= $book['updated_at'] ?></td>
                          <td><?= $book['deleted_at'] ?></td>
                          <td class="text-center">
                              <div class="dropdown">
                                  <i class="fas fa-ellipsis-v text-muted"
                                    data-bs-toggle="dropdown"
                                    style="cursor:pointer"></i>

                                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                      <li>
                                          <a class="dropdown-item" data-id="<?= $book['id'] ?>" data-bookseries="<?= $book['book_series_id'] ?>" data-action="edit" data-bs-toggle="modal" data-bs-target="#bookModal">
                                              <i class="fa fa-pen me-2"></i> Edit
                                          </a>
                                      </li>

                                      <li>
                                          <a class="dropdown-item text-danger del-book" data-id="<?= $book['id'] ?>">
                                              <i class="fa fa-trash me-2"></i> Delete
                                          </a>
                                      </li>
                                  </ul>
                              </div>
                          </td>
                      </tr>
                  <?php endforeach; ?>
                  </tbody>
              </table>
          </div>
      </div>

    </div>
  </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="bookModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Book</h5>
            <button type="button" class="btn-close close"></button>
        </div>
        <div class="modal-body" id="bookModalBody">Loading...</div>
    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

    $('.select2').select2();

    $('#bookModal').on('show.bs.modal', function (e) {
      let id = $(e.relatedTarget).data('id') ?? 0;
      let bookSeriesId = $(e.relatedTarget).data('bookseries') ?? null;
      let action = $(e.relatedTarget).data('action');
      $('.modal-title').text(action === 'create' ? 'Create Book' : 'Edit Book');

      $.post('input-book.php', { id_book: id, book_series_id: bookSeriesId }, function(res){
          $('#bookModalBody').html(res);
          $('.select2').select2({ dropdownParent: $('#bookModal') });
      });
    });

});

/* DELETE */
$(document).on('click','.del-book',function(){
    let id = $(this).data('id');
    Swal.fire({
        title:'Are you sure?',
        icon:'warning',
        showCancelButton:true
    }).then((r)=>{
        if(r.isConfirmed){
            $.post('delete-book.php',{id:id},function(res){
                let data = JSON.parse(res);
                Swal.fire(data.status,data.message,data.status);
                if(data.status==='success'){
                    setTimeout(()=>location.reload(),800);
                }
            });
        }
    });
});

$(document).on('click','.close',()=>$('#bookModal').modal('hide'));
</script>

<?php include 'footer.php'; ?>

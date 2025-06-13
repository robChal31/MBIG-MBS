<style>
    select {
        max-width: 400px;
        word-wrap: break-word;
    }

    textarea {
        width: 100%;
    }

    .benefit-desc {
      transition: width 0.5s ease;
      text-align: start !important;
    }

    .benefit-desc:hover {
      width: 40% !important;
    }
    
    .benefit-ket {
        display: none;
    }

    table.dataTable tbody td {
      padding : 5px !important;
      vertical-align: middle !important;
      font-size: .7rem !important;
    }

    table.dataTable tbody td.benefit-desc{
      text-align: start !important;
    }

    table.dataTable thead th {
        font-size: .7rem !important;
    }

    .select2-container {
        z-index: 2050 !important;
    }

    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
    }

</style>
<?php include 'header.php'; ?>

<?php

  if($_SESSION['role'] != "admin"){
    $_SESSION['toast_status'] = 'Error';
    $_SESSION['toast_msg'] = 'Unauthorized Access';
    header('Location: ./draft-pk.php');
    exit();
  }
  
  $books = [];
  $books_q = "SELECT * FROM books AS book WHERE book.is_active = 1";
  $books_exec = mysqli_query($conn, $books_q);
  if (mysqli_num_rows($books_exec) > 0) {
    $books = mysqli_fetch_all($books_exec, MYSQLI_ASSOC);    
  }
    
?>
  <!-- Content Start -->
  <div class="content">
      <?php include 'navbar.php'; ?>

      <div class="container-fluid p-4">
          <div class="row">
              <div class="col-12">
                  <div class="bg-whites rounded h-100 p-4">
                    <div class="d-flex justify-content-between">
                      <div class="">
                        <h6 class="mb-4">Books</h6>
                      </div>
                      <div class="">
                        <button type="button" class="btn btn-primary btn-sm" data-action="create" data-bs-toggle="modal" data-bs-target="#bookModal" id="add_book">Add Book</button>
                      </div>
                    </div>
                    <div class="table-responsive">
                      <table class="table" id="table_id">
                          <thead>
                              <tr>
                              <th style="width: 5%;">Id</th>
                                  <th scope="col">Name</th>
                                  <th scope="col">Created at</th>
                                  <th scope="col">Updated at</th>
                                  <th scope="col">Action</th>
                              </tr>
                          </thead>
                          <tbody>
                             <?php foreach($books as $book) { ?>
                                <tr>
                                    <td class="text-center"><?= $book['id'] ?></td>
                                    <td><?= $book['name'] ?></td>
                                    <td><?= $book['created_at'] ?></td>
                                    <td><?= $book['updated_at'] ?></td>
                                    <td class="text-center">
                                      <span data-id="<?= $book['id'] ?>" data-action='edit' data-bs-toggle='modal' data-bs-target='#bookModal' class='btn btn-outline-primary btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Edit'><i class='fas fa-pen'></i></span>
                                      
                                      <?php if($_SESSION['role'] == "admin") { ?>
                                        <span data-id="<?= $book['id'] ?>" class='del-book btn btn-outline-danger btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Delete'><i class='fas fa-trash'></i></span>
                                      <?php } ?>
                                    </td>
                            <?php } ?>
                          </tbody>
                      </table>
                    </div>
                  </div>
              </div>
          </div>
      </div>
      <!-- Form End -->

      <div class="modal fade" id="bookModal" tabindex="-1" role="dialog" aria-labelledby="bookModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="bookModalBody">
                Loading...
            </div>
            </div>
        </div>
      </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">

    $(document).ready(function(){

      $('.select2').select2();

      var bookModal = document.getElementById('bookModal');
      bookModal.addEventListener('show.bs.modal', function (event) {
          var rowid = event.relatedTarget.getAttribute('data-id')
          let action = event.relatedTarget.getAttribute('data-action');

          var modalTitle = bookModal.querySelector('.modal-title')
          modalTitle.textContent = action == 'create' ?  "Create Book" : "Edit Book";
         
          $.ajax({
              url: 'input-book.php',
              type: 'POST',
              data: {
                id_book: rowid,
              },
              success: function(data) {
                  $('#bookModalBody').html(data);
                  $('.select2').select2({
                    dropdownParent: $('#bookModal')
                  });
              }
          });
      })

      var addTemplateModal = document.getElementById('add_book');
      addTemplateModal.addEventListener('show.bs.modal', function (event) {
          var rowid = 0;
          let action = event.relatedTarget.getAttribute('data-action');

          var modalTitle = addTemplateModal.querySelector('.modal-title')
          modalTitle.textContent = action == 'create' ?  "Create Book" : "Edit Book";
         
          $.ajax({
              url: 'input-book.php',
              type: 'POST',
              data: {
                id_book: rowid,
              },
              success: function(data) {
                  $('#bookModalBody').html(data);
                  $('.select2').select2({
                    dropdownParent: $('#bookModal')
                  });
              }
          });
      })

    });

    $('.del-book').on('click', function() {
      var id = $(this).data('id');
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'delete-book.php',
            type: 'POST',
            data: {
              id: id
            },
            beforeSend: function() {
              Swal.fire({
                  title: 'Loading...',
                  html: 'Please wait while we save your data.',
                  allowOutsideClick: false,
                  didOpen: () => {
                      Swal.showLoading()
                  }
              });
            },
            success: function(res) {
              let data = JSON.parse(res);
              Swal.close();
              if(data.status == 'success') {
                Swal.fire({
                  title: "Deleted!",
                  text: data.message,
                  icon: "success"
                });
                setTimeout(function() {
                  location.reload();
                }, 1000);
              }else {
                Swal.fire({
                  title: "Failed!",
                  text: data.message,
                  icon: "error"
                });
              }
            } 
          });
        }
      });
    });

    $(document).on('click', '.close', function() {
        $('#bookModal').modal('hide');
    });

</script>
<?php include 'footer.php'; ?>
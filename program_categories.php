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

  
  $program_categories = [];
  $program_categories_q = "SELECT * FROM program_categories AS program WHERE program.deleted_at IS NULL";
  $program_categories_exec = mysqli_query($conn, $program_categories_q);
  if (mysqli_num_rows($program_categories_exec) > 0) {
    $program_categories = mysqli_fetch_all($program_categories_exec, MYSQLI_ASSOC);    
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
                        <h6 class="mb-4">Program Categories</h6>
                      </div>
                      <div class="">
                        <button type="button" class="btn btn-primary btn-sm" data-action="create" data-bs-toggle="modal" data-bs-target="#programCategoryModal" id="add_program">Add Program Category</button>
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
                             <?php $no = 1; foreach($program_categories as $program_category) { ?>
                                <tr>
                                    <td class="text-center"><?= $no ?></td>
                                    <td class="text-start"><?= $program_category['name'] ?></td>
                                    <td class="text-start"><?= $program_category['created_at'] ?></td>
                                    <td class="text-start"><?= $program_category['updated_at'] ?></td>
                                    <td class="text-center">
                                      <span data-id="<?= $program_category['id'] ?>" data-action='edit' data-bs-toggle='modal' data-bs-target='#programCategoryModal' class='btn btn-outline-primary btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Edit'><i class='fas fa-pen'></i></span>

                                      <?php if($_SESSION['role'] == "admin") { ?>
                                        <span data-id="<?= $program_category['id'] ?>" class='del-prog btn btn-outline-danger btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Delete'><i class='fas fa-trash'></i></span>
                                      <?php } ?>
                                    </td>
                            <?php $no++; } ?>
                          </tbody>
                      </table>
                    </div>
                  </div>
              </div>
          </div>
      </div>
      <!-- Form End -->

      <div class="modal fade" id="programCategoryModal" tabindex="-1" role="dialog" aria-labelledby="programCategoryModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="programCategoryModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="programCategoryModalBody">
                Loading...
            </div>
            </div>
        </div>
      </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">

    $(document).ready(function(){

      $('.select2').select2();

      var programCategoryModal = document.getElementById('programCategoryModal');
      programCategoryModal.addEventListener('show.bs.modal', function (event) {
          var rowid = event.relatedTarget.getAttribute('data-id')
          let action = event.relatedTarget.getAttribute('data-action');

          var modalTitle = programCategoryModal.querySelector('.modal-title')
          modalTitle.textContent = action == 'create' ?  "Create Program" : "Edit Program";
         
          $.ajax({
              url: 'input-program-category.php',
              type: 'POST',
              data: {
                id_program_category: rowid,
              },
              success: function(data) {
                  $('#programCategoryModalBody').html(data);
                  $('.select2').select2({
                    dropdownParent: $('#programCategoryModal')
                  });
              }
          });
      })

      var addprogramCategoryModal = document.getElementById('add_program');
      addprogramCategoryModal.addEventListener('show.bs.modal', function (event) {
          var rowid = 0;
          let action = event.relatedTarget.getAttribute('data-action');

          var modalTitle = addprogramCategoryModal.querySelector('.modal-title')
          modalTitle.textContent = action == 'create' ?  "Create Program" : "Edit Program";
         
          $.ajax({
              url: 'input-program-category.php',
              type: 'POST',
              data: {
                id_program_category: rowid,
              },
              success: function(data) {
                  $('#programCategoryModalBody').html(data);
                  $('.select2').select2({
                    dropdownParent: $('#programCategoryModal')
                  });
              }
          });
      })

    });

    $('.del-prog').on('click', function() {
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
            url: 'delete-program-category.php',
            type: 'POST',
            data: {
              id_program_category: id
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
              Swal.close()
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
        $('#programCategoryModal').modal('hide');
    });

</script>
<?php include 'footer.php'; ?>
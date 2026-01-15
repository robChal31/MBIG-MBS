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

  
  $programs = [];
  $programs_q = "SELECT program.*, cat.name as category 
                  FROM programs AS program 
                  LEFT JOIN program_categories as cat on cat.id = program.program_category_id
                  ";
  $programs_exec = mysqli_query($conn, $programs_q);
  if (mysqli_num_rows($programs_exec) > 0) {
    $programs = mysqli_fetch_all($programs_exec, MYSQLI_ASSOC);    
  }
    
?>
  <!-- Content Start -->
  <div class="content">
      <?php include 'navbar.php'; ?>

      <div class="container-fluid p-4">
        <div class="row">
          <div class="col-12">
            <div class="card rounded h-100 p-4 shadow">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h5 class="fw-bold mb-0">Programs</h5>
                  <small class="text-muted">Manage available programs</small>
                </div>

                <button
                  type="button"
                  class="btn btn-primary btn-sm fw-semibold"
                  data-action="create"
                  data-bs-toggle="modal"
                  data-bs-target="#programModal"
                  id="add_program">
                  <i class="bi bi-plus"></i> Add Program
                </button>
              </div>


              <div class="table-responsive">
                <table class="table" id="table_id">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Id</th>
                            <th scope="col">Code</th>
                            <th scope="col">Name</th>
                            <th scope="col">Category</th>
                            <th scope="col">Is PK</th>
                            <th scope="col">Is Dynamic</th>
                            <th scope="col">Created at</th>
                            <th scope="col">Updated at</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach($programs as $program) { ?>
                          <tr>
                              <td class="text-center"><?= $program['id'] ?></td>
                              <td class="text-start"><?= $program['code'] ?></td>
                              <td class="text-start"><?= $program['name'] ?></td>
                              <td class="text-start"><?= $program['category'] ?? 'None' ?></td>
                              <td class="text-center">
                                <?= $program['is_pk']
                                  ? "<i class='fa fa-check-circle text-success'></i>"
                                  : "<i class='fa fa-minus-circle text-muted'></i>" ?>
                              </td>

                              <!-- IS DYNAMIC -->
                              <td class="text-center">
                                <?= $program['is_dynamic']
                                  ? "<i class='fa fa-check-circle text-success'></i>"
                                  : "<i class='fa fa-minus-circle text-muted'></i>" ?>
                              </td>
                              <td class="text-start"><?= $program['created_at'] ?></td>
                              <td class="text-start"><?= $program['updated_at'] ?></td>
                              <td class="text-center">
                                <div class="dropdown">
                                  <i class="fas fa-ellipsis-v text-muted"
                                    data-bs-toggle="dropdown"
                                    style="cursor:pointer"></i>

                                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                      <a class="dropdown-item"
                                        data-id="<?= $program['id'] ?>"
                                        data-action="edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#programModal">
                                        <i class="fas fa-pen me-2"></i> Edit
                                      </a>
                                    </li>

                                    <?php if($_SESSION['role'] == "admin") { ?>
                                    <li>
                                      <a class="dropdown-item text-danger del-prog"
                                        data-id="<?= $program['id'] ?>">
                                        <i class="fas fa-trash me-2"></i> Delete
                                      </a>
                                    </li>
                                    <?php } ?>
                                  </ul>
                                </div>
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

      <div class="modal fade" id="programModal" tabindex="-1" role="dialog" aria-labelledby="programModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="programModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="programModalBody">
                Loading...
            </div>
            </div>
        </div>
      </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">

    $(document).ready(function(){

      $('.select2').select2();

      var programModal = document.getElementById('programModal');
      programModal.addEventListener('show.bs.modal', function (event) {
          var rowid = event.relatedTarget.getAttribute('data-id')
          let action = event.relatedTarget.getAttribute('data-action');

          var modalTitle = programModal.querySelector('.modal-title')
          modalTitle.textContent = action == 'create' ?  "Create Program" : "Edit Program";
         
          $.ajax({
              url: 'input-program.php',
              type: 'POST',
              data: {
                id_program: rowid,
              },
              success: function(data) {
                  $('#programModalBody').html(data);
                  $('.select2').select2({
                    dropdownParent: $('#programModal')
                  });
              }
          });
      })

      var addProgramModal = document.getElementById('add_program');
      addProgramModal.addEventListener('show.bs.modal', function (event) {
          var rowid = 0;
          let action = event.relatedTarget.getAttribute('data-action');

          var modalTitle = addProgramModal.querySelector('.modal-title')
          modalTitle.textContent = action == 'create' ?  "Create Program" : "Edit Program";
         
          $.ajax({
              url: 'input-program.php',
              type: 'POST',
              data: {
                id_program: rowid,
              },
              success: function(data) {
                  $('#programModalBody').html(data);
                  $('.select2').select2({
                    dropdownParent: $('#programModal')
                  });
              }
          });
      })

    });

    $('.del-prog').on('click', function() {
      var id = $(this).data('id');
      console.log(id)
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
            url: 'delete-program.php',
            type: 'POST',
            data: {
              id_program: id
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
        $('#programModal').modal('hide');
    });

</script>
<?php include 'footer.php'; ?>
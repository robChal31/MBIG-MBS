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
  $programs_q = "SELECT * FROM programs AS program WHERE program.is_active = 1";
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
                  <div class="bg-white rounded h-100 p-4">
                    <div class="d-flex justify-content-between">
                      <div class="">
                        <h6 class="mb-4">Programs</h6>
                      </div>
                      <div class="">
                        <button type="button" class="btn btn-primary btn-sm" data-action="create" data-bs-toggle="modal" data-bs-target="#programModal" id="add_program">Add Program</button>
                      </div>
                    </div>
                    <div class="table-responsive">
                      <table class="table" id="table_id">
                          <thead>
                              <tr>
                                  <th style="width: 5%;">Id</th>
                                  <th scope="col">Code</th>
                                  <th scope="col">Name</th>
                                  <th scope="col">Is PK</th>
                                  <th scope="col">Created at</th>
                                  <th scope="col">Updated at</th>
                                  <th scope="col">Action</th>
                              </tr>
                          </thead>
                          <tbody>
                             <?php $no = 1; foreach($programs as $program) { ?>
                                <tr>
                                    <td class="text-center"><?= $no ?></td>
                                    <td class="text-start"><?= $program['code'] ?></td>
                                    <td class="text-start"><?= $program['name'] ?></td>
                                    <td class="text-start"><?= $program['is_pk'] ? "<span><i class='fa fa-check'></i></span>" : '' ?></td>
                                    <td class="text-start"><?= $program['created_at'] ?></td>
                                    <td class="text-start"><?= $program['updated_at'] ?></td>
                                    <td class="text-center">
                                      <span data-id="<?= $program['id'] ?>" data-action='edit' data-bs-toggle='modal' data-bs-target='#programModal' class='btn btn-outline-primary btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Edit'><i class='fas fa-pen'></i></span>

                                      <?php if($_SESSION['role'] == "admin") { ?>
                                        <span data-id="<?= $program['id'] ?>" class='del-prog btn btn-outline-danger btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Delete'><i class='fas fa-trash'></i></span>
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
                ...
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
            success: function(res) {
              let data = JSON.parse(res);
              console.log((data));
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
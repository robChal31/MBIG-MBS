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
      /* text-align: center !important; */
      font-size: .65rem !important;
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

  
  $templates = [];
  $draft_templates_q = "SELECT * 
                        FROM draft_template_benefit AS dtb
                        LEFT JOIN benefit_role AS br ON br.id_template = dtb.id_template_benefit
                        WHERE dtb.is_active = 1";
  $draft_exec = mysqli_query($conn, $draft_templates_q);
  if (mysqli_num_rows($draft_exec) > 0) {
    $templates = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);    
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
                        <h6 class="mb-4">Benefit Templates</h6>
                      </div>
                      <div class="">
                        <button type="button" class="btn btn-primary btn-sm" data-action="create" data-bs-toggle="modal" data-bs-target="#templateModal" id="add_template">Add Template</button>
                      </div>
                    </div>
                    <div class="table-responsive">
                      <table class="table" id="table_id">
                          <thead>
                              <tr>
                                  <th>Id</th>
                                  <th scope="col">Benefit</th>
                                  <th scope="col">Subbenefit</th>
                                  <th scope="col">Benefit Name</th>
                                  <th scope="col" style="width: 15%;">Description</th>
                                  <th scope="col" style="width: 15%;">Implementation</th>
                                  <th scope="col">Avail Code</th>
                                  <th style="width: 10%;" scope="col">Business Unit</th>
                                  <th scope="col">Qty Year 1</th>
                                  <th scope="col">Qty Year 2</th>
                                  <th scope="col">Qty Year 3</th>
                                  <th scope="col">Value</th>
                                  <!-- <th scope="col">Optional</th> -->
                                  <th scope="col" style="width: 8%;">Action</th>
                              </tr>
                          </thead>
                          <tbody>
                             <?php foreach($templates as $template) { ?>
                                <tr>
                                    <td><?= $template['id_template_benefit'] ?></td>
                                    <td><?= $template['benefit'] ?></td>
                                    <td><?= $template['subbenefit'] ?></td>
                                    <td><?= $template['benefit_name'] ?></td>
                                    <td><?= $template['description'] ?></td>
                                    <td><?= $template['pelaksanaan'] ?></td>
                                    <td><?= $template['avail'] ?></td>
                                    <td><?= $template['unit_bisnis'] ?></td>
                                    <td><?= $template['qty1'] ?></td>
                                    <td><?= $template['qty2'] ?></td>
                                    <td><?= $template['qty3'] ?></td>
                                    <td><?= number_format($template['valueMoney'], '0', ',', '.') ?></td>
                                    <!-- <td class="text-center">
                                        <?php if($template['optional'] == 1) : ?>
                                            <span><i class='fa fa-check'></i></span>
                                        <?php endif; ?>
                                    </td> -->
                                    <td>
                                      <span data-id="<?= $template['id_template_benefit'] ?>" data-action='edit' data-bs-toggle='modal' data-bs-target='#templateModal' class='btn btn-outline-primary btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Edit'><i class='fas fa-pen'></i></span>

                                      <?php if($_SESSION['role'] == "admin") { ?>
                                        <span data-id="<?= $template['id_template_benefit'] ?>" class='del-template btn btn-outline-danger btn-sm me-1' style='font-size: .75rem' data-toggle='tooltip' title='Delete'><i class='fas fa-trash'></i></span>
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

      <div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="templateModalBody">
                Loading...
            </div>
            </div>
        </div>
      </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript">

    $(document).ready(function(){

      $('.select2').select2();

      var templateModal = document.getElementById('templateModal');
      templateModal.addEventListener('show.bs.modal', function (event) {
          var rowid = event.relatedTarget.getAttribute('data-id')
          let action = event.relatedTarget.getAttribute('data-action');

          var modalTitle = templateModal.querySelector('.modal-title')
          modalTitle.textContent = action == 'create' ?  "Create Template" : "Edit Template";
         
          $.ajax({
              url: 'input-template.php',
              type: 'POST',
              data: {
                id_template: rowid,
              },
              success: function(data) {
                  $('#templateModalBody').html(data);
                  $('.select2').select2({
                    dropdownParent: $('#templateModal')
                  });
              }
          });
      })

      var addTemplateModal = document.getElementById('add_template');
      addTemplateModal.addEventListener('show.bs.modal', function (event) {
          var rowid = 0;
          let action = event.relatedTarget.getAttribute('data-action');

          var modalTitle = addTemplateModal.querySelector('.modal-title')
          modalTitle.textContent = action == 'create' ?  "Create Template" : "Edit Template";
         
          $.ajax({
              url: 'input-template.php',
              type: 'POST',
              data: {
                id_template: rowid,
              },
              success: function(data) {
                  $('#templateModalBody').html(data);
                  $('.select2').select2({
                    dropdownParent: $('#templateModal')
                  });
              }
          });
      })

    });

    $('.del-template').on('click', function() {
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
            url: 'delete-template-benefit.php',
            type: 'POST',
            data: {
              id_template_benefit: id
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
              console.log((data));
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
        $('#templateModal').modal('hide');
    });

</script>
<?php include 'footer.php'; ?>
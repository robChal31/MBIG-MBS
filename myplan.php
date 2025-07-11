
<?php 
    include 'header.php';

    $role = $_SESSION['role'];
    $id_user = $_SESSION['id_user'];
?>

    <!-- Content Start -->
    <div class="content">
        <?php include 'navbar.php'; ?>
        <!-- Sale & Revenue Start -->
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-12">
                    <div class="bg-whites rounded h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-4">My Plan</h6>
                            <?php
                                if($role == 'admin' || $role == 'ec') { ?>
                                    <div class="d-flex align-items-center">
                                        <a href="myplan-form.php">
                                            <button type="button" class="btn btn-primary m-2 btn-sm"><i class="fas fa-plus me-2"></i>Create Plan</button>    
                                        </a>
                                    </div>
                            <?php } ?>
                        </div>
                        

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="table_draft">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col" style="width: 10%">Nama EC</th>
                                        <th scope="col" style="width: 20%">Nama Sekolah</th>
                                        <th scope="col">Segment</th>
                                        <th scope="col">Start Timeline Penyelesaian</th>
                                        <th scope="col">End Timeline Penyelesaian</th>
                                        <th scope="col">Program</th>
                                        <th scope="col">Proyeksi Omset</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Created at</th>
                                        <th scope="col">Updated at</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                        $sql = "SELECT mp.*, user.*, sc.name as school_name, db.id_draft as id_draft, db.fileUrl, db.status, db.verified, db.confirmed
                                                    FROM myplan AS mp
                                                LEFT JOIN schools AS sc ON sc.id = mp.school_id
                                                LEFT JOIN draft_benefit AS db ON mp.id = db.myplan_id
                                                LEFT JOIN user ON mp.user_id = user.id_user
                                                LEFT JOIN programs AS prog ON prog.name = mp.program
                                                WHERE mp.deleted_at IS NULL";

                                        if($role == 'ec'){
                                            $sql .=" AND (mp.user_id = $id_user OR user.leadId = $id_user OR user.leadId2 = $id_user OR user.leadId3 = $id_user)";
                                        }

                                        $sql .= " ORDER BY mp.created_at ASC";
                                        
                                        $result = mysqli_query($conn, $sql);
                                        setlocale(LC_MONETARY,"id_ID");
                                        if (mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) {
                                                $stat = ($row['status'] == 0 && $row['fileUrl']) ? 'Waiting Approval': ($row['status'] == 1 ? 'Approved' : 'Rejected');
                                                $stat = ($row['status'] == 0 && !$row['fileUrl']) ? 'Draft' : $stat;
                                                $status_class = $row['status'] == 0 ? 'bg-warning' : ($row['status'] == 1 ? 'bg-success' : 'bg-danger');
                                                $status_class = ($row['status'] == 0 && !$row['fileUrl']) ? 'bg-primary' : $status_class;

                                                $stat = ($row['verified'] == 1 && $row['status'] == 1 && $row['confirmed'] == 0) ? 'Waiting Confirmation' : ($row['verified'] == 0 && $row['status'] == 1 ? 'Waiting Verification' : $stat);
                                                $is_ec_the_creator = $_SESSION['id_user'] == $row['user_id'] || $_SESSION['id_user'] == 70 || $_SESSION['id_user'] == 15;
                                    ?>
                                                <tr>
                                                    <td><?= $row['id'] ?></td>
                                                    <td><?= $row['generalname'] ?></td>
                                                    <td><?= $row['school_name'] ?></td>
                                                    <td><?= ucfirst($row['segment']) ?></td>
                                                    <td><?= ($row['start_timeline']) ?></td>
                                                    <td><?= ($row['end_timeline']) ?></td>
                                                    <td><?= strtoupper($row['program']) ?></td>
                                                    <td><?= number_format($row['omset_projection']) ?></td>
                                                    <td>
                                                        <span style="cursor: pointer;" data-id="<?= $row['id_draft'] ?>" <?= $stat == 'Draft' ? '' : "data-bs-toggle='modal'" ?>  data-bs-target='#approvalModal' class="<?= $status_class ?> fw-bold py-1 px-2 text-white rounded"><?= $stat ?></span>
                                                    </td>
                                                    <td><?= $row['created_at'] ?></td>
                                                    <td><?= $row['updated_at'] ?></td>
                                                    <td>
                                                        <?php
                                                            if($is_ec_the_creator || $_SESSION['role'] == 'admin'){ ?>
                                                                <a href="myplan-updates.php?plan_id=<?=$row['id']?>" class="text-primary me-1"><i class="fa fa-calendar" data-bs-toggle="tooltip" data-bs-placement="top" title="Add Update Plan"></i></a>
                                                                <a href="myplan-form.php?plan_id=<?=$row['id']?>" class="text-success me-1"><i class="fas fa-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"></i></a>
                                                                <a href='#' data-id="<?= $row['id'] ?>" class='delete-btn text-danger me-1' data-toggle='tooltip' title='Delete'><i class='fas fa-trash'></i></a>
                                                        <?php }else { ?>
                                                            <a href="myplan-updates.php?plan_id=<?=$row['id']?>" class="text-primary me-1"><i class="fa fa-search" data-bs-toggle="tooltip" data-bs-placement="top" title="View Update Plan"></i></a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            
                                        <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="approvalModalBody">
                    Loading...
                </div>
                </div>
            </div>
        </div>

<?php include 'footer.php';?>

<script>
    var approvalModal = document.getElementById('approvalModal')
    approvalModal.addEventListener('show.bs.modal', function (event) {
        var rowid = event.relatedTarget.getAttribute('data-id')
        var modalTitle = approvalModal.querySelector('.modal-title')
        modalTitle.textContent = 'Approval History ' + rowid;
        $.ajax({
            url: 'get_benefits_approver.php',
            type: 'POST',
            data: {
                id_draft: rowid,
            },
            success: function(data) {
                $('#approvalModalBody').html(data)
            }
        });
    })

    $('.close').click(function() {
        $('#approvalModal').modal('hide');
    });

    $('.delete-btn').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "You will delete this plan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: 'delete-plan.php',
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
                    success: function(data) {
                        let resData = JSON.parse(data)
                        Swal.close();
                        if(resData.status == 'Success') {
                            Swal.fire({
                                title: "Deleted!",
                                text: resData.message,
                                icon: "success"
                            });
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        }else {
                            Swal.fire({
                                title: "Error!",
                                text: resData.message,
                                icon: "error"
                            });
                        }
                        // location.reload();
                    },
                    error: function(data) {
                        Swal.close();
                        let resData = JSON.parse(data)
                        Swal.fire({
                            title: "Error!",
                            text: resData.message,
                            icon: "error"
                        });
                            // location.reload();
                    }
                });
            }
        });
    })

    $(document).ready(function() {
        $('#table_draft').DataTable({
            dom: 'Bfrtip',
            pageLength: 20,
            order: [
                [4, 'desc'] 
            ],
            buttons: [
                { 
                    extend: 'copyHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: blue; color: white;'
                    }
                },
                { 
                    extend: 'excelHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: green; color: white;' 
                    }
                },
                { 
                    extend: 'csvHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: orange; color: white;'
                    }
                },
                { 
                    extend: 'pdfHtml5',
                    className: 'btn-custom',
                    attr: {
                        style: 'font-size: .7rem; border: none; font-weight: bold; border-radius: 5px; background-color: red; color: white;'
                    }
                }
            ]
        })
    })
</script>
       
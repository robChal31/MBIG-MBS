
<?php 
    include 'header.php'; 
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
                            <h6 class="mb-4">Draft Benefit</h6>
                            <a href="new-benefit-ec-input.php">
                                <button type="button" class="btn btn-primary m-2 btn-sm"><i class="fas fa-plus me-2"></i>Create Draft</button>    
                            </a>
                        </div>
                        

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="table_draft">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 10%">Nama EC</th>
                                        <th scope="col" style="width: 20%">Nama Sekolah</th>
                                        <th scope="col">Segment</th>
                                        <th scope="col">Program</th>
                                        <th scope="col">Created at</th>
                                        <th scope="col">Updated at</th>
                                        <!-- <th scope="col">Deleted at</th> -->
                                        <th scope="col" style="width: 13%">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                        $programs = [];
                                        $query_program = "SELECT * FROM programs WHERE is_active = 1 AND is_pk = 1";
                                        $program_names = false;
                                        $exec_program = mysqli_query($conn, $query_program);
                                        if (mysqli_num_rows($exec_program) > 0) {
                                            $programs = mysqli_fetch_all($exec_program, MYSQLI_ASSOC);
                                            $program_names = array_map(function($item) {
                                                return strtoupper($item['name']);
                                            }, $programs);
                                        }

                                        $program_names = $program_names ? ("'" . implode("', '", $program_names) . "'") : false;

                                        $query_filter_pk = $program_names ? " AND a.program NOT IN ($program_names)" : '';
                                        $id_user = $_SESSION['id_user'];

                                        $order_by = ' ORDER BY a.date ASC';
                                        $sql = "SELECT a.*, b.*, IFNULL(sc.name, a.school_name) as school_name2, a.verified, a.deleted_at
                                                FROM draft_benefit a
                                                LEFT JOIN schools as sc on sc.id = a.school_name
                                                LEFT JOIN user b on a.id_ec = b.id_user
                                                WHERE a.deleted_at is null
                                                $query_filter_pk"; 
                                        if($_SESSION['role'] == 'ec'){
                                            $sql.=" AND (a.id_ec = $id_user or b.leadId = $id_user or b.leadId2 = $id_user or b.leadId3 = $id_user)";
                                        }
                                        $sql .= $order_by;
                                        
                                        $result = mysqli_query($conn, $sql);
                                        setlocale(LC_MONETARY,"id_ID");
                                        if (mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) {
                                                $stat = ($row['status'] == 0 && $row['fileUrl']) ? 'Waiting Approval': ($row['status'] == 1 ? 'Approved' : 'Rejected');
                                                $stat = ($row['status'] == 0 && !$row['fileUrl']) ? 'Draft' : $stat;
                                                $status_class = $row['status'] == 0 ? 'bg-warning' : ($row['status'] == 1 ? 'bg-success' : 'bg-danger');
                                                $status_class = ($row['status'] == 0 && !$row['fileUrl']) ? 'bg-primary' : $status_class;
                                                $stat = $row['verified'] == 1 && $stat == 'Approved' ? 'Verified' : ($row['verified'] == 0 && $stat == 'Approved' ? 'Waiting Verification' : $stat);
                                                $is_ec_the_creator = $_SESSION['id_user'] == $row['id_ec'] || $_SESSION['id_user'] == 70;
                                    ?>
                                                <tr>
                                                    <td><?= $row['generalname'] ?></td>
                                                    <td><?= $row['school_name2'] ?></td>
                                                    <td><?= ucfirst($row['segment']) ?></td>
                                                    <td><?= $row['program'] ?></td>
                                                    <td><?= $row['date'] ?></td>
                                                    <td><?= $row['updated_at'] ?></td>
                                                    <!-- <td><?= $row['deleted_at'] ?></td> -->
                                                    <td>
                                                        <span style="cursor: pointer;" data-id="<?= $row['id_draft'] ?>" <?= $stat == 'Draft' ? '' : "data-bs-toggle='modal'" ?>  data-bs-target='#approvalModal' class="<?= $status_class ?> fw-bold py-1 px-2 text-white rounded"><?= $stat ?></span>
                                                    </td>
                                                    <td scope="col">
                                                        <?php if($row['fileUrl']) { ?>
                                                            <a href='draft-benefit/<?= $row['fileUrl'].".xlsx" ?>' data-toggle='tooltip' title='View Doc'><i class="bi bi-paperclip me-1"></i></a>
                                                        <?php } ?>
                                                        <?php 
                                                            if((($is_ec_the_creator && $row['status'] == 0 && !$row['fileUrl']) || ($row['status'] == 2 && ($is_ec_the_creator || $_SESSION['role'] == 'admin')) || ($_SESSION['role'] == 'admin' && $row['status'] == 0 && !$row['fileUrl'])) && (!$row['deleted_at'])){ ?>
                                                                <a href="new-benefit-ec-input2.php?edit=edit&id_draft=<?=$row['id_draft']?>" class="text-success me-1"><i class="fas fa-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"></i></a>
                                                        <?php } ?>

                                                        <?php if(($is_ec_the_creator && $row['status'] == 0 && !$row['fileUrl'])){ ?>
                                                            <a href='#' data-id="<?= $row['id_draft'] ?>" class='delete-btn text-danger me-1' data-toggle='tooltip' title='Delete'><i class='fas fa-trash'></i></a>
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
        <!-- Sale & Revenue End -->

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
        console.log(rowid)
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
        // Cari modal yang sedang terbuka dan tutup modal tersebut
        $('#approvalModal').modal('hide');
    });

    $('.delete-btn').click(function() {
        let idDraft = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "You will delete this draft!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                
                $.ajax({
                    url: 'delete-benefit.php',
                    type: 'POST',
                    data: {
                        id_draft: idDraft
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
                        console.log(resData)
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
                        console.log(data)
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
       
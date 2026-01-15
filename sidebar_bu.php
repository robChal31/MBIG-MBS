<?php
$current_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$url_components = parse_url($current_url);

$path = basename($url_components['path']);

$query_string = isset($url_components['query']) ? $url_components['query'] : '';

$last_path_with_query = $path;
if (!empty($query_string)) {
    $last_path_with_query .= '?' . $query_string;
}

$last_path_with_query;
?>


<div class="sidebar pe-4 pb-3">
    <nav class="navbar card">
        <a href="main.php" class="navbar-brand mx-4 mt-md-0 pt-md-0 pt-4 mt-4">
            <h3 class="text-primary"><img src='img/logoMISs.png' width="50%"></h3>
        </a>
        <!-- <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
            </div>
            <div class="ms-3">
                <h6 class="mb-0" style="font-size:0.7rem"><?=$_SESSION['username'];?></h6>
                <span><?=$_SESSION['role'];?></span>
            </div>
        </div> -->
        <div class="navbar-nav w-100" style="font-size: .8rem;">
            <a href="main.php" class="nav-item nav-link <?= $last_path_with_query == 'main.php' ? "active" : '' ?>"><i class="fa fa-tachometer-alt"></i>Dashboard</a>
            <a href="draft-benefit.php" class="nav-item nav-link <?= $last_path_with_query == 'draft-benefit.php' ? "active" : '' ?>"><i class="fas fa-ruler"></i>Draft Benefit</a>
            <a href="draft-approval-list.php" class="nav-item nav-link <?= $last_path_with_query == 'draft-approval-list.php' ? "active" : '' ?>"><i class="fas fa-fingerprint"></i>Draft Approval List</a>
            <a href="list-pk.php" class="nav-item nav-link <?= $last_path_with_query == 'list-pk.php' ? "active" : '' ?>" style="font-size: .7rem">
                <i class="fas fa-file-contract"></i> Partnership Agreement List
            </a>

            <a href="benefits.php" class="nav-item nav-link <?= $last_path_with_query == 'benefits.php' ? "active" : '' ?>"><i class="fa fa-gift"></i>Benefits</a>
            <?php if($_SESSION['role'] == 'admin' || $_SESSION['role']=='bani' || $_SESSION['role'] == 'sa' ): ?>
                <a href="approved_list.php" class="nav-item nav-link <?= $last_path_with_query == 'approved_list.php' ? "active" : '' ?>"><i class="fas fa-signature"></i>Approved Benefit</a>
                <a href="masters.php" class="nav-item nav-link <?= $last_path_with_query == 'masters.php' ? "active" : '' ?>"><i class="fas fa-school"></i>Master School</a>
                <a href="customer_data.php" class="nav-item nav-link <?= $last_path_with_query == 'customer_data.php' ? "active" : '' ?>"><i class="fas fa-users"></i>Customer Data</a>
                <a href="history.php" class="nav-item nav-link <?= $last_path_with_query == 'history.php' ? "active" : '' ?>"><i class="fas fa-history"></i>School History</a>
                <a href="cbls-prestasi.php" class="nav-item nav-link <?= $last_path_with_query == 'cbls-prestasi.php' ? "active" : '' ?>"><i class="fas fa-trophy"></i>Nilai CBLS dan Prestasi</a>
                <a href="new-masterb.php" class="nav-item nav-link <?= $last_path_with_query == 'new-masterb.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Master Benefit</a>
                <a href="omset-data-input.php" class="nav-item nav-link <?= $last_path_with_query == 'omset-data-input.php' ? "active" : '' ?>"><i class="fas fa-coins"></i>School Omset</a>
                
                <a href="new-benefit.php?type=materials" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=materials' ? "active" : '' ?>"><i class="fas fa-cubes"></i>Materials</a>
                <a href="new-benefit.php?type=assessment" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=assessment' ? "active" : '' ?>"><i class="fas fa-tasks"></i>Assessment</a>
                <a href="new-benefit.php?type=sarana prasarana" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=sarana prasarana' ? "active" : '' ?>"><i class="fas fa-building"></i>Sarana Prasarana</a>
                <a href="new-benefit.php?type=supervisi dan cchd" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=supervisi dan cchd' ? "active" : '' ?>"><i class="fas fa-exclamation-triangle"></i>Supervisi & CCHD</a>
                <a href="new-benefit.php?type=mentari teachers academy" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=mentari teachers academy' ? "active" : '' ?>"><i class="fas fa-chalkboard-teacher"></i><span style="font-size: .7rem">Mentari Teachers Academy</span></a>
                <a href="new-benefit.php?type=branding" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=branding' ? "active" : '' ?>"><i class="fas fa-address-card"></i>Branding</a>
                <a href="new-benefit.php?type=networking" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=networking' ? "active" : '' ?>"><i class="fas fa-network-wired"></i>Networking</a>
                <a href="new-benefit.php?type=students" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=students' ? "active" : '' ?>"><i class="fas fa-user-graduate"></i>Students</a>
                <a href="new-benefit.php?type=training" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=training' ? "active" : '' ?>"><i class="fas fa-user-tie"></i>Training</a>
                <a href="benefit-inhouse.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-inhouse.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Inhouse</a>
                <a href="benefit-rpp.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-rpp.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>RPP</a>
                <a href="benefit-cchd.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-cchd.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>CCHD</a>
                <a href="benefit-supervisi.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-supervisi.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Supervisi</a>
                <a href="new-benefit.php?type=lainnya" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=lainnya' ? "active" : '' ?>"><i class="fas fa-cogs"></i>Lainnya</a>
            
            <?php elseif($_SESSION['role']=='miranda' || $_SESSION['role']=='bani'): ?>
                <a href="masters.php" class="nav-item nav-link <?= $last_path_with_query == 'masters.php' ? "active" : '' ?>"><i class="fas fa-school"></i>Master School</a>
                <a href="customer_data.php" class="nav-item nav-link <?= $last_path_with_query == 'customer_data.php' ? "active" : '' ?>"><i class="fas fa-users"></i>Customer Data</a>
                <a href="history.php" class="nav-item nav-link <?= $last_path_with_query == 'history.php' ? "active" : '' ?>"><i class="fas fa-history"></i>School History</a>
                <a href="new-masterb.php" class="nav-item nav-link <?= $last_path_with_query == 'new-masterb.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Master Benefit</a>
                <a href="omset-data-input.php" class="nav-item nav-link <?= $last_path_with_query == 'omset-data-input.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>School Omset</a>
                <a href="new-benefit.php?type=supervisi dan cchd" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=supervisi dan cchd' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Supervisi & CCHD</a>
                <a href="new-benefit.php?type=training" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=training' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Training</a>
            <?php elseif($_SESSION['role']=='hescin' || $_SESSION['role']=='bani'): ?>
                <a href="masters.php" class="nav-item nav-link <?= $last_path_with_query == 'masters.php' ? "active" : '' ?>"><i class="fas fa-school"></i>Master School</a>
                <a href="customer_data.php" class="nav-item nav-link <?= $last_path_with_query == 'customer_data.php' ? "active" : '' ?>"><i class="fas fa-users"></i>Customer Data</a>
                <a href="history.php" class="nav-item nav-link <?= $last_path_with_query == 'history.php' ? "active" : '' ?>"><i class="fas fa-history"></i>School History</a>
                <a href="new-masterb.php" class="nav-item nav-link <?= $last_path_with_query == 'new-masterb.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Master Benefit</a>
                <a href="omset-data-input.php" class="nav-item nav-link <?= $last_path_with_query == 'omset-data-input.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>School Omset</a>
                <a href="new-benefit.php?type=training" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=training' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Training</a>
                <a href="new-benefit.php?type=mentari teachers academy" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=mentari teachers academy' ? "active" : '' ?>"><i class="fa fa-th me-2"></i><span style="font-size:16px">Mentari Teachers Academy</span></a>
            <?php elseif($_SESSION['role']=='amalia' || $_SESSION['role']=='bani'): ?>
                <a href="masters.php" class="nav-item nav-link <?= $last_path_with_query == 'masters.php' ? "active" : '' ?>"><i class="fas fa-school"></i>Master School</a>
                <a href="customer_data.php" class="nav-item nav-link <?= $last_path_with_query == 'customer_data.php' ? "active" : '' ?>"><i class="fas fa-users"></i>Customer Data</a>
                <a href="history.php" class="nav-item nav-link <?= $last_path_with_query == 'history.php' ? "active" : '' ?>"><i class="fas fa-history"></i>School History</a>
                <a href="new-masterb.php" class="nav-item nav-link <?= $last_path_with_query == 'new-masterb.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Master Benefit</a>
                <a href="omset-data-input.php" class="nav-item nav-link <?= $last_path_with_query == 'omset-data-input.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>School Omset</a>
                <a href="new-benefit.php?type=branding" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=branding' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Branding</a>
                <a href="new-benefit.php?type=assessment" class="nav-item nav-link <?= $last_path_with_query == 'new-benefit.php?type=assessment' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Assessment</a>
            <?php elseif($_SESSION['role']=='tdmta' || $_SESSION['role']=='bani'): ?>
                <a href="masters.php" class="nav-item nav-link <?= $last_path_with_query == 'masters.php' ? "active" : '' ?>"><i class="fas fa-school"></i>Master School</a>
                <a href="customer_data.php" class="nav-item nav-link <?= $last_path_with_query == 'customer_data.php' ? "active" : '' ?>"><i class="fas fa-users"></i>Customer Data</a>
                <a href="history.php" class="nav-item nav-link <?= $last_path_with_query == 'history.php' ? "active" : '' ?>"><i class="fas fa-history"></i>School History</a>
                <a href="benefit-tdmta.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-tdmta.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>TDMTA</a>
            <?php elseif($_SESSION['role']=='assessment' || $_SESSION['role']=='bani'): ?>
                <a href="masters.php" class="nav-item nav-link <?= $last_path_with_query == 'masters.php' ? "active" : '' ?>"><i class="fas fa-school"></i>Master School</a>
                <a href="customer_data.php" class="nav-item nav-link <?= $last_path_with_query == 'customer_data.php' ? "active" : '' ?>"><i class="fas fa-users"></i>Customer Data</a>
                <a href="history.php" class="nav-item nav-link <?= $last_path_with_query == 'history.php' ? "active" : '' ?>"><i class="fas fa-history"></i>School History</a>
                <a href="benefit-assessment.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-assessment.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Assessment</a>
            <?php elseif($_SESSION['role']=='imel' || $_SESSION['role']=='bani'): ?>
                <a href="masters.php" class="nav-item nav-link <?= $last_path_with_query == 'masters.php' ? "active" : '' ?>"><i class="fas fa-school"></i>Master School</a>
                <a href="customer_data.php" class="nav-item nav-link <?= $last_path_with_query == 'customer_data.php' ? "active" : '' ?>"><i class="fas fa-users"></i>Customer Data</a>
                <a href="history.php" class="nav-item nav-link <?= $last_path_with_query == 'history.php' ? "active" : '' ?>"><i class="fas fa-history"></i>School History</a>
                <a href="benefit-inhouse.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-inhouse.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Inhouse</a>
                <a href="benefit-rpp.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-rpp.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>RPP</a>
                <a href="benefit-cchd.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-cchd.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>CCHD</a>
                <a href="benefit-supervisi.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-supervisi.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Supervisi</a>
            <?php endif; ?>
            
        </div>
    </nav>
</div>
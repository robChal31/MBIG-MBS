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


<div class="sidebar pe-4 pb-3 bg-whites">
    <nav class="navbar bg-whites">
        <a href="main.php" class="navbar-brand mx-4 mt-md-0 pt-md-0 pt-4 mt-4 mb-2">
            <h3 class="text-primary mt-2 pt-2"><img src='img/lgmbs2.png' style="width: 70%; object-fit: contain;"></h3>
        </a>

        <div class="navbar-nav w-100" style="font-size: .8rem;">
            <a href="main.php" class="nav-item nav-link <?= $last_path_with_query == 'main.php' ? "active" : '' ?>"><i class="fa fa-tachometer-alt"></i>Dashboard</a>
            <a href="myplan.php" class="nav-item nav-link <?= $last_path_with_query == 'myplan.php' ? "active" : '' ?>"><i class="fas fa-tasks"></i>My Plan</a>
            <a href="draft-benefit.php" class="nav-item nav-link <?= $last_path_with_query == 'draft-benefit.php' ? "active" : '' ?>"><i class="fas fa-ruler"></i>Draft Benefit</a>
            <a href="draft-pk.php" class="nav-item nav-link <?= $last_path_with_query == 'draft-pk.php' ? "active" : '' ?>"><i class="fas fa-file-contract"></i>Draft PK</a>
            <a href="draft-approval-list.php" class="nav-item nav-link <?= $last_path_with_query == 'draft-approval-list.php' ? "active" : '' ?>"><i class="fas fa-fingerprint"></i>Draft Approval List</a>
            <a href="list-pk.php" class="nav-item nav-link <?= $last_path_with_query == 'list-pk.php' ? "active" : '' ?>"><i class="fas fa-handshake"></i> Agreement List</a>
            <a href="benefits.php" class="nav-item nav-link <?= $last_path_with_query == 'benefits.php' ? "active" : '' ?>"><i class="fa fa-gift"></i>Benefits</a>
            
            <?php if($_SESSION['role'] != 'ec'): ?>
                <a href="approved_list.php" class="nav-item nav-link <?= $last_path_with_query == 'approved_list.php' ? "active" : '' ?>"><i class="fas fa-signature"></i>Approved Benefit</a>
            <?php endif; ?>
                <a href="school_pic.php" class="nav-item nav-link <?= $last_path_with_query == 'school_pic.php' ? "active" : '' ?>"><i class="fas fa-users"></i>School Program</a>
            <?php if($_SESSION['role'] == 'admin') { ?>

                <a class="nav-item nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#collapsibleNav2" role="button" aria-expanded="false" aria-controls="collapsibleNav2">
                    <div><i class="fas fa-chart-bar"></i> Report</div>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </a>
                <div class="collapse" id="collapsibleNav2">
                    <ul class="list-unstyled ms-3">
                        <li>
                            <a href="report.php" class="nav-link <?= $last_path_with_query == 'report.php' ? "active" : '' ?>"><i class="fas fa-chart-line"></i> Report Programs</a>
                        </li>
                        <li>
                            <a href="report_adoption.php" class="nav-link <?= $last_path_with_query == 'report_adoption.php' ? "active" : '' ?>"><i class="fas fa-chart-line"></i> Report Adopsi</a>
                        </li>
                      
                    </ul>
                </div>

                <a class="nav-item nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#collapsibleNav" role="button" aria-expanded="false" aria-controls="collapsibleNav">
                    <div><i class="fas fa-database"></i> Master Data</div>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </a>
                <div class="collapse" id="collapsibleNav">
                    <ul class="list-unstyled ms-3">
                        <li>
                            <a class="nav-link <?= $last_path_with_query == 'books.php' ? "active" : '' ?>" href="books.php"><i class="fas fa-book"></i> Books</a>
                        </li>
                        <li>
                            <a class="nav-link <?= $last_path_with_query == 'program_categories.php' ? "active" : '' ?>" href="program_categories.php"><i class="fas fa-file-alt"></i> Program Categories</a>
                        </li>
                        <li>
                            <a class="nav-link <?= $last_path_with_query == 'programs.php' ? "active" : '' ?>" href="programs.php"><i class="fas fa-graduation-cap"></i> Programs</a>
                        </li>
                        <li>
                            <a class="nav-link <?= $last_path_with_query == 'benefit_templates.php' ? "active" : '' ?>" href="benefit_templates.php"><i class="fas fa-file-alt"></i> Benefit Templates</a>
                        </li>
                        
                    </ul>
                </div>

                <a href="masters.php" class="nav-item nav-link <?= $last_path_with_query == 'masters.php' ? "active" : '' ?>"><i class="fas fa-school"></i>Master School</a>
                <a href="customer_data.php" class="nav-item nav-link <?= $last_path_with_query == 'customer_data.php' ? "active" : '' ?>"><i class="fas fa-users"></i>Customer Data</a>
                <a href="history.php" class="nav-item nav-link <?= $last_path_with_query == 'history.php' ? "active" : '' ?>"><i class="fas fa-history"></i>School History</a>
                <a href="cbls-prestasi.php" class="nav-item nav-link <?= $last_path_with_query == 'cbls-prestasi.php' ? "active" : '' ?>"><i class="fas fa-trophy"></i>Nilai CBLS dan Prestasi</a>
                <a href="new-masterb.php" class="nav-item nav-link <?= $last_path_with_query == 'new-masterb.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Master Benefit</a>
                <a href="omset-data-input.php" class="nav-item nav-link <?= $last_path_with_query == 'omset-data-input.php' ? "active" : '' ?>"><i class="fas fa-coins"></i>School Omset</a>
            <?php } ?>

            <?php if($_SESSION['role']): ?>
                
                

                <!-- <a href="benefit-inhouse.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-inhouse.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Inhouse</a>
                <a href="benefit-rpp.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-rpp.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>RPP</a>
                <a href="benefit-cchd.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-cchd.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>CCHD</a>
                <a href="benefit-supervisi.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-supervisi.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Supervisi</a>

                <a href="benefit-tdmta.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-tdmta.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>TDMTA</a>
                <a href="benefit-assessment.php" class="nav-item nav-link <?= $last_path_with_query == 'benefit-assessment.php' ? "active" : '' ?>"><i class="fa fa-th me-2"></i>Assessment</a> -->
                

            <?php endif; ?>
            
        </div>
    </nav>
</div>
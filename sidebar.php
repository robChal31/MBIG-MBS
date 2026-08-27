<?php
    $current_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $url_components = parse_url($current_url);

    $path = basename($url_components['path']);

    $query_string = isset($url_components['query']) ? $url_components['query'] : '';

    $last_path_with_query = $path;
    if (!empty($query_string)) {
        $last_path_with_query .= '?' . $query_string;
    }

    // 🔥 Deteksi halaman Master Data
    $master_data_pages = ['book_series.php', 'program_categories.php', 'programs.php', 'benefit_templates.php', 'benefit_setting.php'];
    $master_data_active = in_array($path, $master_data_pages) ? 'show' : '';
    $master_data_expanded = in_array($path, $master_data_pages) ? 'true' : 'false';

    // 🔥 Deteksi halaman Report
    $report_pages = ['report.php', 'report_adoption.php', 'report_adoption2.php', 'report_usage.php', 'report_partnership.php'];
    $report_active = in_array($path, $report_pages) ? 'show' : '';
    $report_expanded = in_array($path, $report_pages) ? 'true' : 'false';
?>


<div class="sidebar pe-4 pb-3">
    <nav class="navbar">
        <a href="main.php" class="navbar-brand mx-4 mt-md-0 pt-md-0 pt-4 mt-4 mb-0 pb-4 border-bottom border-1 border-black">
            <img src='img/lgmbs2.png' style="width: 90%; object-fit: contain;" class="mt-4 pt-2">
        </a>

        <div class="navbar-nav w-100">
            <a href="main.php" class="nav-item nav-link <?= $last_path_with_query == 'main.php' ? "active" : '' ?>"><i class="fa fa-tachometer-alt"></i>Dashboard</a>
            <a href="myplan.php" class="nav-item nav-link <?= $last_path_with_query == 'myplan.php' || $path == 'myplan-form.php' ? "active" : '' ?>"><i class="fas fa-calendar-check"></i>My Plan</a>
            <a href="draft-benefit.php" class="nav-item nav-link <?= $last_path_with_query == 'draft-benefit.php' || $path == 'new-benefit-ec-input.php' || $path == 'new-benefit-ec-input2.php' ? "active" : '' ?>"><i class="fas fa-clipboard"></i>PP & MAP</a>
            <a href="draft-pk.php" class="nav-item nav-link <?= $last_path_with_query == 'draft-pk.php' || $path == 'create_draft_pk.php' ? "active" : '' ?>"><i class="fas fa-file-contract"></i>MPP</a>
            <a href="draft-approval-list.php" class="nav-item nav-link <?= $last_path_with_query == 'draft-approval-list.php' ? "active" : '' ?>"><i class="fas fa-fingerprint"></i>Draft Approval List</a>
            <a href="list-pk.php" class="nav-item nav-link <?= $last_path_with_query == 'list-pk.php' ? "active" : '' ?>"><i class="fa-solid fa-handshake-angle"></i> Agreement List</a>
            <a href="benefits.php" class="nav-item nav-link <?= $last_path_with_query == 'benefits.php' ? "active" : '' ?>"><i class="fa fa-gift"></i>Benefits</a>
            
            <?php if($_SESSION['role'] != 'ec'): ?>
                <a href="approved_list.php" class="nav-item nav-link <?= $last_path_with_query == 'approved_list.php' ? "active" : '' ?>"><i class="fas fa-signature"></i>Approved Program</a>
            <?php endif; ?>
                <a href="school_pic.php" class="nav-item nav-link <?= $last_path_with_query == 'school_pic.php' ? "active" : '' ?>"><i class="fas fa-users"></i>School Program</a>
            <?php if($_SESSION['role'] == 'admin') { ?>
                
                <!-- 🔥 MASTER DATA - Collapse Otomatis -->
                <a class="nav-item nav-link d-flex align-items-center <?= $master_data_active ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" 
                   href="#collapsibleNav" 
                   aria-expanded="<?= $master_data_expanded ?>">
                    <i class="fas fa-database"></i>
                    <span class="flex-grow-1">Master Data</span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </a>

                <div class="collapse <?= $master_data_active ? 'show' : '' ?>" id="collapsibleNav">
                    <ul class="list-unstyled ms-3">
                        <li>
                            <a style="font-size: 11px" class="nav-link <?= ($path == 'book_series.php' || str_contains($path, 'books.php')) ? "active" : '' ?>" href="book_series.php"><i class="fas fa-book"></i> Books</a>
                        </li>
                        <li>
                            <a style="font-size: 11px" class="nav-link <?= $path == 'program_categories.php' ? "active" : '' ?>" href="program_categories.php"><i class="fas fa-file-alt"></i> Program Categories</a>
                        </li>
                        <li>
                            <a style="font-size: 11px" class="nav-link <?= $path == 'programs.php' ? "active" : '' ?>" href="programs.php"><i class="fas fa-graduation-cap"></i> Programs</a>
                        </li>
                        <li>
                            <a style="font-size: 11px" class="nav-link <?= $path == 'benefit_templates.php' ? "active" : '' ?>" href="benefit_templates.php"><i class="fas fa-file-alt"></i> Benefit Templates</a>
                        </li>
                        <li>
                            <a style="font-size: 11px" class="nav-link <?= $path == 'benefit_setting.php' ? "active" : '' ?>" href="benefit_setting.php"><i class="fas fa-percentage"></i> Benefit Settings</a>
                        </li>
                    </ul>
                </div>

                <!-- 🔥 REPORT - Collapse Otomatis -->
                <a class="nav-item nav-link d-flex align-items-center <?= $report_active ? 'active' : '' ?>" 
                   data-bs-toggle="collapse" 
                   href="#collapsibleNav2" 
                   aria-expanded="<?= $report_expanded ?>">
                    <i class="fas fa-chart-line"></i>
                    <span class="flex-grow-1">Report</span>
                    <i class="fas fa-chevron-down chevron-icon"></i>
                </a>

                <div class="collapse <?= $report_active ? 'show' : '' ?>" id="collapsibleNav2">
                    <ul class="list-unstyled ms-3">
                        <li>
                            <a style="font-size: 11px" href="report.php" class="nav-link <?= $path == 'report.php' ? "active" : '' ?>"><i class="fas fa-chart-line"></i> Programs</a>
                        </li>
                        <li>
                            <a style="font-size: 11px" href="report_adoption2.php" class="nav-link <?= ($path == 'report_adoption.php' || $path == 'report_adoption2.php') ? "active" : '' ?>"><i class="fas fa-chart-line"></i> Adopsi</a>
                        </li>
                        <li>
                            <a style="font-size: 11px" href="report_usage.php" class="nav-link <?= $path == 'report_usage.php' ? "active" : '' ?>"><i class="fas fa-chart-line"></i> Usage</a>
                        </li>
                        <li>
                            <a style="font-size: 11px" href="report_partnership.php" class="nav-link <?= $path == 'report_partnership.php' ? "active" : '' ?>"><i class="fas fa-chart-line"></i> Partnership</a>
                        </li>
                    </ul>
                </div>

                <a href="mentari_partner.php" class="nav-item nav-link <?= $last_path_with_query == 'mentari_partner.php' ? "active" : '' ?>"><i class="fas fa-handshake"></i>Mentari Partner</a>
            <?php } ?>
        </div>
    </nav>
</div>
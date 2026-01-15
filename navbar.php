<!-- Navbar Start -->
<div class="" style="position: relative;">
    <nav class="navbar navbar-expand navbar-light sticky-top px-4 py-0">
        <a href="#" class="sidebar-toggler flex-shrink-0">
            <i class="fa fa-bars"></i>
        </a>
        <div class="navbar-nav align-items-center ms-auto">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img class="rounded-circle me-lg-2" src="img/user2.png" alt="" style="width: 40px; height: 40px;">
                    <span class="d-none d-lg-inline-flex" style="font-size: .9rem;"><?=$_SESSION['username'];?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-end border-0 rounded-0 rounded-bottom m-0">
                    <a href="logout.php" class="dropdown-item fw-bold"> <i class="fas fa-sign-out-alt logout-icon"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <?php include 'toast_msg.php'; ?>
</div>
<!-- Navbar End -->
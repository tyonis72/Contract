<!-- SIDEBAR -->
<div id="sidebar" class="bg-white border-end">
    <div class="text-center py-4 border-bottom">
        <div class="sidebar-logo mb-2"></div>
        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
        <small class="text-success">● Online</small>
    </div>
    <div class="p-3">
        <p class="text-muted small mb-2">MAIN NAVIGATION</p>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="<?php echo $base_url; ?>index.php" class="nav-link text-dark">Dashboard</a></li>
            <li class="nav-item"><a href="<?php echo $base_url; ?>city/city-list.php" class="nav-link text-dark">City List</a></li>
            <li class="nav-item"><a href="<?php echo $base_url; ?>region/region-list.php" class="nav-link text-dark">Region List</a></li>
            <li class="nav-item"><a href="<?php echo $base_url; ?>pkwt/pkwt-list.php" class="nav-link text-dark">Pkwt List</a></li>
            <li class="nav-item"><a href="<?php echo $base_url; ?>company/company-list.php" class="nav-link text-dark">Company List</a></li>
            <li class="nav-item"><a href="<?php echo $base_url; ?>contract/contract-list.php" class="nav-link text-dark">Contract List</a></li>
            <li class="nav-item"><a href="<?php echo $base_url; ?>admin/index.php" class="nav-link text-dark">Admin</a></li>
        </ul>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="<?php echo $base_url; ?>auth/logout.php" class="nav-link text-dark">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>dashboard.php">
            <i class="fas fa-shield-alt me-2"></i><?php echo SITE_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/dashboard/index.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Management
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/users/index.php"><i class="fas fa-users me-2"></i>Users</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/roads/index.php"><i class="fas fa-road me-2"></i>Roads</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/speed-limits/index.php"><i class="fas fa-tachometer-alt me-2"></i>Speed Limits</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/checkpoints/index.php"><i class="fas fa-map-pin me-2"></i>Checkpoints</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/alerts/index.php"><i class="fas fa-exclamation-triangle me-2"></i>Alerts</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/accidents/index.php"><i class="fas fa-car-crash me-2"></i>Accidents</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/construction/index.php"><i class="fas fa-hard-hat me-2"></i>Construction</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/live/index.php"><i class="fas fa-wifi me-2"></i>Live Drivers</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>admin/reports/index.php"><i class="fas fa-file-alt me-1"></i>Reports</a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>driver/maps/index.php"><i class="fas fa-map me-1"></i>Maps</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>driver/alerts/index.php"><i class="fas fa-bell me-1"></i>Alerts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>driver/routes/index.php"><i class="fas fa-route me-1"></i>Routes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>driver/history/index.php"><i class="fas fa-history me-1"></i>History</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown" style="width: 320px;" id="notifDropdown">
                        <div class="dropdown-header fw-bold">Notifications</div>
                        <div id="notifList"><p class="text-muted small px-3">No notifications</p></div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>driver/profile/index.php"><i class="fas fa-id-card me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#" id="toggleDarkMode"><i class="fas fa-moon me-2"></i>Dark Mode</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div style="padding-top: 70px;"></div>

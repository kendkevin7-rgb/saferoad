<?php
define('PAGE_TITLE', 'Admin Dashboard');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();
$stats = getAdminStats();
$trafficStats = getTrafficStats(7);

$stmt = $db->query("SELECT u.id, u.username, u.full_name, dl.latitude, dl.longitude, dl.speed, dl.recorded_at
    FROM driver_locations dl JOIN users u ON dl.user_id = u.id
    WHERE dl.recorded_at >= NOW() - INTERVAL 5 MINUTE
    AND dl.id IN (SELECT MAX(id) FROM driver_locations GROUP BY user_id)");
$onlineDrivers = $stmt->fetchAll();

$stmt = $db->query("SELECT wl.*, u.username FROM warning_logs wl JOIN users u ON wl.user_id = u.id ORDER BY wl.created_at DESC LIMIT 10");
$recentWarnings = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard</h2>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Drivers</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['total_drivers']; ?></h3>
                        </div>
                        <div class="stat-icon bg-primary-subtle"><i class="fas fa-users text-primary fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Online Now</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['online_drivers']; ?></h3>
                        </div>
                        <div class="stat-icon bg-success-subtle"><i class="fas fa-wifi text-success fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Active Alerts</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['active_alerts']; ?></h3>
                        </div>
                        <div class="stat-icon bg-warning-subtle"><i class="fas fa-exclamation-triangle text-warning fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Checkpoints</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['active_checkpoints']; ?></h3>
                        </div>
                        <div class="stat-icon bg-danger-subtle"><i class="fas fa-map-pin text-danger fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-map me-2 text-primary"></i>Live Driver Locations</h5>
                </div>
                <div class="card-body p-0">
                    <div id="adminMap" style="height: 450px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Speed Violations (7 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="trafficChart" height="200"></canvas>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2 text-primary"></i>Recent Violations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                        <?php foreach ($recentWarnings as $w): ?>
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between">
                                <small class="fw-bold"><?php echo htmlspecialchars($w['username']); ?></small>
                                <small class="text-danger"><?php echo $w['speed']; ?> km/h</small>
                            </div>
                            <small class="text-muted"><?php echo timeAgo($w['created_at']); ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3"><a href="<?php echo SITE_URL; ?>admin/alerts/index.php" class="btn btn-outline-danger w-100"><i class="fas fa-plus-circle me-2"></i>New Alert</a></div>
                        <div class="col-md-3"><a href="<?php echo SITE_URL; ?>admin/checkpoints/index.php" class="btn btn-outline-primary w-100"><i class="fas fa-map-pin me-2"></i>Add Checkpoint</a></div>
                        <div class="col-md-3"><a href="<?php echo SITE_URL; ?>admin/roads/index.php" class="btn btn-outline-success w-100"><i class="fas fa-road me-2"></i>Manage Roads</a></div>
                        <div class="col-md-3"><a href="<?php echo SITE_URL; ?>admin/reports/index.php" class="btn btn-outline-info w-100"><i class="fas fa-file-export me-2"></i>Export Reports</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = '
<script src="https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY . '&callback=initAdminMap&libraries=places,geometry" async defer></script>
<script>
var SITE_URL = "' . SITE_URL . '";
var onlineDrivers = ' . json_encode($onlineDrivers) . ';
var trafficLabels = ' . json_encode(array_column($trafficStats, 'date')) . ';
var trafficData = ' . json_encode(array_column($trafficStats, 'total')) . ';
</script>
<script src="' . SITE_URL . 'assets/js/admin-map.js"></script>
<script src="' . SITE_URL . 'assets/js/admin-charts.js"></script>
';
include __DIR__ . '/../../includes/footer.php';
?>

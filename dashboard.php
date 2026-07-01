<?php
define('PAGE_TITLE', 'Dashboard');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if (isAdmin()) {
    header('Location: admin/dashboard/index.php');
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$stats = getDriverStats($userId);

$stmt = $db->prepare("SELECT * FROM driver_locations WHERE user_id = ? ORDER BY recorded_at DESC LIMIT 1");
$stmt->execute([$userId]);
$lastLocation = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM warning_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$recentWarnings = $stmt->fetchAll();

$alerts = getActiveAlerts();

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <h2 class="fw-bold"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Driver Dashboard</h2>
            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Trips</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['trips']; ?></h3>
                        </div>
                        <div class="stat-icon bg-primary-subtle">
                            <i class="fas fa-route text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Distance</p>
                            <h3 class="fw-bold mb-0"><?php echo formatDistance($stats['distance']); ?></h3>
                        </div>
                        <div class="stat-icon bg-success-subtle">
                            <i class="fas fa-road text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Warnings</p>
                            <h3 class="fw-bold mb-0"><?php echo $stats['warnings']; ?></h3>
                        </div>
                        <div class="stat-icon bg-warning-subtle">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Top Speed</p>
                            <h3 class="fw-bold mb-0"><?php echo formatSpeed($stats['top_speed']); ?></h3>
                        </div>
                        <div class="stat-icon bg-danger-subtle">
                            <i class="fas fa-tachometer-alt text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Your Location</h5>
                </div>
                <div class="card-body p-0">
                    <div id="dashboardMap" style="height: 400px; border-radius: 0 0 8px 8px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Speed Monitor</h5>
                </div>
                <div class="card-body text-center">
                    <div class="speed-display mb-3" id="speedDisplay">
                        <span id="currentSpeed" class="speed-value">0.0</span>
                        <span class="speed-unit">km/h</span>
                    </div>
                    <div class="progress speed-bar mb-2" style="height: 10px;">
                        <div id="speedBar" class="progress-bar bg-success" style="width: 0%;" role="progressbar"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small" id="speedLimitDisplay">Limit: --</span>
                        <span class="text-muted small" id="roadNameDisplay">Road: --</span>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-bell me-2 text-primary"></i>Recent Warnings</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($recentWarnings)): ?>
                        <li class="list-group-item text-muted">No warnings yet</li>
                        <?php else: ?>
                        <?php foreach ($recentWarnings as $w): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <small class="fw-bold text-danger"><?php echo htmlspecialchars($w['warning_type']); ?></small>
                                <small class="text-muted"><?php echo timeAgo($w['created_at']); ?></small>
                            </div>
                            <small><?php echo htmlspecialchars($w['message']); ?></small>
                        </li>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-primary"></i>Nearby Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2" id="alertsContainer">
                        <?php foreach ($alerts as $alert): ?>
                        <div class="col-md-4 col-lg-3">
                            <div class="alert-card p-3 border rounded <?php echo $alert['severity']; ?>">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-<?php echo alertIcon($alert['alert_type']); ?> me-2 fa-lg"></i>
                                    <small class="fw-bold"><?php echo htmlspecialchars($alert['title']); ?></small>
                                </div>
                                <small class="text-muted d-block"><?php echo htmlspecialchars(substr($alert['description'], 0, 60)); ?>...</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function alertIcon($type) {
    $icons = [
        'accident' => 'car-crash',
        'road_closure' => 'road',
        'construction' => 'hard-hat',
        'flood' => 'water',
        'hazard' => 'skull-crossbones',
        'weather' => 'cloud-rain',
        'traffic_jam' => 'traffic-light',
        'school_zone' => 'school',
        'hospital_zone' => 'hospital',
        'dangerous_curve' => 'road',
        'checkpoint' => 'shield-alt',
    ];
    return $icons[$type] ?? 'exclamation-triangle';
}

$extraScripts = '
<script src="https://maps.googleapis.com/maps/api/js?key=' . GOOGLE_MAPS_API_KEY . '&callback=initDashboardMap&libraries=places,geometry" async defer></script>
<script src="' . SITE_URL . 'assets/js/maps.js"></script>
<script src="' . SITE_URL . 'assets/js/gps.js"></script>
<script src="' . SITE_URL . 'assets/js/alerts.js"></script>
';
include __DIR__ . '/includes/footer.php';
?>

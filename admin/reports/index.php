<?php
define('PAGE_TITLE', 'Reports');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();
$reportType = $_GET['type'] ?? 'warnings';

$data = [];
$filename = '';

switch ($reportType) {
    case 'warnings':
        $data = $db->query("SELECT wl.*, u.username, u.email FROM warning_logs wl JOIN users u ON wl.user_id = u.id ORDER BY wl.created_at DESC LIMIT 1000")->fetchAll();
        $filename = 'speed_warnings_' . date('Y-m-d') . '.csv';
        break;
    case 'drivers':
        $data = $db->query("SELECT id, username, email, full_name, phone, is_active, last_login, created_at FROM users WHERE role_id = 2 ORDER BY created_at DESC")->fetchAll();
        $filename = 'driver_report_' . date('Y-m-d') . '.csv';
        break;
    case 'alerts':
        $data = $db->query("SELECT a.*, r.road_name FROM alerts a LEFT JOIN roads r ON a.road_id = r.id ORDER BY a.created_at DESC")->fetchAll();
        $filename = 'alerts_report_' . date('Y-m-d') . '.csv';
        break;
    case 'locations':
        $data = $db->query("SELECT dl.*, u.username FROM driver_locations dl JOIN users u ON dl.user_id = u.id ORDER BY dl.recorded_at DESC LIMIT 1000")->fetchAll();
        $filename = 'location_history_' . date('Y-m-d') . '.csv';
        break;
}

if (isset($_GET['export']) && $_GET['export'] === 'csv' && !empty($data)) {
    exportToCSV($data, $filename);
}

$stats = getAdminStats();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-file-alt me-2 text-primary"></i>Reports & Export</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-exclamation-triangle text-danger fa-3x mb-2"></i>
                    <h5>Speed Violations</h5>
                    <h3 class="fw-bold"><?php echo $stats['speed_violations_today']; ?></h3>
                    <small class="text-muted">Today</small>
                    <a href="?type=warnings" class="btn btn-sm btn-outline-primary mt-2 w-100">View</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-users text-primary fa-3x mb-2"></i>
                    <h5>Driver Report</h5>
                    <h3 class="fw-bold"><?php echo $stats['total_drivers']; ?></h3>
                    <small class="text-muted">Registered</small>
                    <a href="?type=drivers" class="btn btn-sm btn-outline-primary mt-2 w-100">View</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-bell text-warning fa-3x mb-2"></i>
                    <h5>Alerts Report</h5>
                    <h3 class="fw-bold"><?php echo $stats['active_alerts']; ?></h3>
                    <small class="text-muted">Active</small>
                    <a href="?type=alerts" class="btn btn-sm btn-outline-primary mt-2 w-100">View</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-map-marked-alt text-info fa-3x mb-2"></i>
                    <h5>Location History</h5>
                    <h3 class="fw-bold">-</h3>
                    <small class="text-muted">Last 1000</small>
                    <a href="?type=locations" class="btn btn-sm btn-outline-primary mt-2 w-100">View</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($data)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <?php echo ucfirst($reportType); ?> Report
                <small class="text-muted">(<?php echo count($data); ?> records)</small>
            </h5>
            <a href="?type=<?php echo $reportType; ?>&export=csv" class="btn btn-success">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-striped table-sm mb-0">
                    <thead class="sticky-top bg-white">
                        <tr>
                            <?php if (!empty($data[0])): ?>
                            <?php foreach (array_keys($data[0]) as $col): ?>
                            <th><?php echo ucwords(str_replace('_', ' ', $col)); ?></th>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($row as $val): ?>
                            <td><small><?php echo htmlspecialchars(substr((string)$val, 0, 50)); ?></small></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

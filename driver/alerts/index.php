<?php
define('PAGE_TITLE', 'Alerts');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT wl.*, u.full_name FROM warning_logs wl JOIN users u ON wl.user_id = u.id WHERE wl.user_id = ? ORDER BY wl.created_at DESC LIMIT 50");
$stmt->execute([$userId]);
$warnings = $stmt->fetchAll();

$alerts = getActiveAlerts();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <h4 class="fw-bold mb-4"><i class="fas fa-bell me-2 text-primary"></i>Alerts & Warnings</h4>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Active Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2" id="alertsList">
                        <?php foreach ($alerts as $a): ?>
                        <div class="col-md-6">
                            <div class="card alert-card <?php echo $a['severity']; ?>">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                                        <span class="badge bg-<?php echo $a['severity'] === 'critical' ? 'danger' : $a['severity']; ?>"><?php echo $a['severity']; ?></span>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($a['description'] ?? '', 0, 100)); ?></small><br>
                                    <small class="text-muted"><?php echo $a['alert_type']; ?> &middot; <?php echo timeAgo($a['created_at']); ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Warning History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        <?php if (empty($warnings)): ?>
                        <div class="list-group-item text-muted">No warnings recorded</div>
                        <?php else: ?>
                        <?php foreach ($warnings as $w): ?>
                        <div class="list-group-item py-2">
                            <div class="d-flex justify-content-between">
                                <small class="fw-bold text-danger"><?php echo htmlspecialchars($w['warning_type']); ?></small>
                                <small class="text-muted"><?php echo timeAgo($w['created_at']); ?></small>
                            </div>
                            <small><?php echo htmlspecialchars($w['message']); ?></small>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

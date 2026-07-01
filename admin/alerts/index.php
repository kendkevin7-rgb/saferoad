<?php
define('PAGE_TITLE', 'Manage Alerts');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid token');
    $stmt = $db->prepare("INSERT INTO alerts (alert_type, title, description, latitude, longitude, severity, radius_meters, road_id, expires_at, reported_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['alert_type'], sanitize($_POST['title']), sanitize($_POST['description']),
        $_POST['latitude'] ?: null, $_POST['longitude'] ?: null,
        $_POST['severity'], $_POST['radius_meters'] ?: 500,
        $_POST['road_id'] ?: null, $_POST['expires_at'] ?: null,
        $_SESSION['user_id']
    ]);
    logActivity($_SESSION['user_id'], 'add_alert', 'Added alert: ' . $_POST['title']);
    header('Location: index.php?msg=added');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle'])) {
    $stmt = $db->prepare("UPDATE alerts SET is_active = !is_active WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    header('Location: index.php?msg=toggled');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $db->prepare("DELETE FROM alerts WHERE id = ?")->execute([$_POST['id']]);
    header('Location: index.php?msg=deleted');
    exit;
}

$alerts = $db->query("SELECT a.*, r.road_name, u.username as reported_by_name FROM alerts a LEFT JOIN roads r ON a.road_id = r.id LEFT JOIN users u ON a.reported_by = u.id ORDER BY a.created_at DESC")->fetchAll();
$roads = $db->query("SELECT id, road_name FROM roads ORDER BY road_name")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-exclamation-triangle me-2 text-primary"></i>Traffic Alerts</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>New Alert</button>
    </div>
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">Alert <?php echo $_GET['msg']; ?>.</div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead><tr><th>Title</th><th>Type</th><th>Severity</th><th>Road</th><th>Active</th><th>Expires</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($alerts as $a): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
                        <td><span class="badge bg-info"><?php echo str_replace('_', ' ', $a['alert_type']); ?></span></td>
                        <td><span class="badge bg-<?php echo $a['severity'] === 'critical' ? 'danger' : $a['severity']; ?>"><?php echo $a['severity']; ?></span></td>
                        <td><small><?php echo htmlspecialchars($a['road_name'] ?? 'N/A'); ?></small></td>
                        <td><span class="badge bg-<?php echo $a['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $a['is_active'] ? 'Yes' : 'No'; ?></span></td>
                        <td><small><?php echo $a['expires_at'] ? date('M d, H:i', strtotime($a['expires_at'])) : 'Never'; ?></small></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button type="submit" name="toggle" class="btn btn-sm btn-<?php echo $a['is_active'] ? 'warning' : 'success'; ?>"><i class="fas fa-<?php echo $a['is_active'] ? 'pause' : 'play'; ?>"></i></button>
                            </form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5>Create Alert</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST">
        <div class="modal-body">
            <?php echo csrfField(); ?>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Alert Type</label><select name="alert_type" class="form-select" required>
                    <option value="accident">Accident</option><option value="road_closure">Road Closure</option><option value="construction">Construction</option>
                    <option value="flood">Flood</option><option value="hazard">Hazard</option><option value="weather">Weather</option>
                    <option value="traffic_jam">Traffic Jam</option><option value="school_zone">School Zone</option><option value="hospital_zone">Hospital Zone</option>
                    <option value="dangerous_curve">Dangerous Curve</option><option value="checkpoint">Checkpoint</option>
                </select></div>
                <div class="col-md-6 mb-3"><label>Severity</label><select name="severity" class="form-select"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="critical">Critical</option></select></div>
            </div>
            <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3"><label>Latitude</label><input type="text" name="latitude" class="form-control" step="any"></div>
                <div class="col-md-4 mb-3"><label>Longitude</label><input type="text" name="longitude" class="form-control" step="any"></div>
                <div class="col-md-4 mb-3"><label>Radius (meters)</label><input type="number" name="radius_meters" class="form-control" value="500"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Road</label><select name="road_id" class="form-select"><option value="">None</option><?php foreach ($roads as $r): ?><option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['road_name']); ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6 mb-3"><label>Expires At</label><input type="datetime-local" name="expires_at" class="form-control"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" name="save" class="btn btn-primary">Create Alert</button></div>
    </form>
</div></div></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

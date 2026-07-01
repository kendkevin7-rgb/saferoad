<?php
define('PAGE_TITLE', 'Construction Zones');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid token');
    $stmt = $db->prepare("INSERT INTO alerts (alert_type, title, description, latitude, longitude, severity, radius_meters, road_id, reported_by) VALUES ('construction', ?, ?, ?, ?, 'medium', ?, ?, ?)");
    $stmt->execute([
        sanitize($_POST['title']), sanitize($_POST['description']),
        $_POST['latitude'] ?: null, $_POST['longitude'] ?: null,
        $_POST['radius_meters'] ?: 300, $_POST['road_id'] ?: null,
        $_SESSION['user_id']
    ]);
    if ($_POST['road_id']) {
        $stmt = $db->prepare("UPDATE roads SET status = 'under_construction' WHERE id = ?");
        $stmt->execute([$_POST['road_id']]);
    }
    header('Location: index.php?msg=added');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $db->prepare("DELETE FROM alerts WHERE id = ? AND alert_type = 'construction'")->execute([$_POST['id']]);
    header('Location: index.php?msg=deleted');
    exit;
}

$constructions = $db->query("SELECT a.*, r.road_name FROM alerts a LEFT JOIN roads r ON a.road_id = r.id WHERE a.alert_type = 'construction' ORDER BY a.created_at DESC")->fetchAll();
$roads = $db->query("SELECT id, road_name FROM roads ORDER BY road_name")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-hard-hat me-2 text-primary"></i>Construction Zones</h4>
        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add Construction</button>
    </div>
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">Construction <?php echo $_GET['msg']; ?>.</div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead><tr><th>Title</th><th>Road</th><th>Location</th><th>Radius</th><th>Active</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($constructions as $c): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($c['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($c['road_name'] ?? 'N/A'); ?></td>
                        <td><small><?php echo $c['latitude'] ? $c['latitude'] . ', ' . $c['longitude'] : 'N/A'; ?></small></td>
                        <td><?php echo $c['radius_meters']; ?>m</td>
                        <td><span class="badge bg-<?php echo $c['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $c['is_active'] ? 'Yes' : 'No'; ?></span></td>
                        <td><small><?php echo timeAgo($c['created_at']); ?></small></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
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

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5>Add Construction Zone</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST">
        <div class="modal-body">
            <?php echo csrfField(); ?>
            <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" required placeholder="e.g., Road widening work on MG Road"></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Latitude</label><input type="text" name="latitude" class="form-control" step="any"></div>
                <div class="col-md-6 mb-3"><label>Longitude</label><input type="text" name="longitude" class="form-control" step="any"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Radius (meters)</label><input type="number" name="radius_meters" class="form-control" value="300"></div>
                <div class="col-md-6 mb-3"><label>Affected Road</label><select name="road_id" class="form-select"><option value="">None</option><?php foreach ($roads as $r): ?><option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['road_name']); ?></option><?php endforeach; ?></select></div>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" name="save" class="btn btn-warning">Add Construction Alert</button></div>
    </form>
</div></div></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

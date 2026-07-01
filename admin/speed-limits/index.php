<?php
define('PAGE_TITLE', 'Speed Limits');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf)) die('Invalid token');
    if (isset($_POST['save'])) {
        $stmt = $db->prepare("INSERT INTO speed_limits (road_id, max_speed, min_speed, vehicle_type, effective_from, effective_to, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['road_id'], $_POST['max_speed'], $_POST['min_speed'] ?: 0, $_POST['vehicle_type'], $_POST['effective_from'] ?: '00:00:00', $_POST['effective_to'] ?: '23:59:59', $_SESSION['user_id']]);
        header('Location: index.php?msg=added');
        exit;
    }
    if (isset($_POST['delete'])) {
        $db->prepare("DELETE FROM speed_limits WHERE id = ?")->execute([$_POST['id']]);
        header('Location: index.php?msg=deleted');
        exit;
    }
}

$limits = $db->query("SELECT sl.*, r.road_name FROM speed_limits sl JOIN roads r ON sl.road_id = r.id ORDER BY sl.created_at DESC")->fetchAll();
$roads = $db->query("SELECT id, road_name FROM roads WHERE status = 'open' ORDER BY road_name")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Speed Limits</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add Limit</button>
    </div>
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">Speed limit <?php echo $_GET['msg']; ?>.</div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead><tr><th>Road</th><th>Max Speed</th><th>Min Speed</th><th>Vehicle</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($limits as $l): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($l['road_name']); ?></td>
                        <td><span class="badge bg-danger fs-6"><?php echo $l['max_speed']; ?> km/h</span></td>
                        <td><?php echo $l['min_speed'] ? $l['min_speed'] . ' km/h' : 'N/A'; ?></td>
                        <td><?php echo $l['vehicle_type']; ?></td>
                        <td><span class="badge bg-<?php echo $l['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $l['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $l['id']; ?>">
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

<div class="modal fade" id="addModal">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5>Add Speed Limit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST">
            <div class="modal-body">
                <?php echo csrfField(); ?>
                <div class="mb-3"><label class="form-label">Road</label><select name="road_id" class="form-select" required><?php foreach ($roads as $r): ?><option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['road_name']); ?></option><?php endforeach; ?></select></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Max Speed (km/h)</label><input type="number" name="max_speed" class="form-control" required min="1"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Min Speed</label><input type="number" name="min_speed" class="form-control" min="0"></div>
                </div>
                <div class="mb-3"><label class="form-label">Vehicle Type</label><select name="vehicle_type" class="form-select"><option value="all">All Vehicles</option><option value="car">Car</option><option value="truck">Truck</option><option value="bus">Bus</option><option value="motorcycle">Motorcycle</option></select></div>
            </div>
            <div class="modal-footer"><button type="submit" name="save" class="btn btn-primary">Save</button></div>
        </form>
    </div></div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

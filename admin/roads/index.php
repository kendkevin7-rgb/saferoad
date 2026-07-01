<?php
define('PAGE_TITLE', 'Manage Roads');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf)) die('Invalid CSRF token');

    if (isset($_POST['save'])) {
        $stmt = $db->prepare("INSERT INTO roads (road_name, road_type, start_lat, start_lng, end_lat, end_lng, distance_km, lanes, surface_type, status, added_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            sanitize($_POST['road_name']),
            sanitize($_POST['road_type']),
            $_POST['start_lat'], $_POST['start_lng'],
            $_POST['end_lat'], $_POST['end_lng'],
            $_POST['distance_km'], $_POST['lanes'],
            sanitize($_POST['surface_type']),
            sanitize($_POST['status']),
            $_SESSION['user_id']
        ]);
        logActivity($_SESSION['user_id'], 'add_road', 'Added road: ' . $_POST['road_name']);
        header('Location: index.php?msg=added');
        exit;
    }
    if (isset($_POST['delete'])) {
        $stmt = $db->prepare("DELETE FROM roads WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header('Location: index.php?msg=deleted');
        exit;
    }
}

$roads = $db->query("SELECT r.*, u.username as added_by_name FROM roads r LEFT JOIN users u ON r.added_by = u.id ORDER BY r.created_at DESC")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-road me-2 text-primary"></i>Manage Roads</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoadModal"><i class="fas fa-plus me-2"></i>Add Road</button>
    </div>
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">Road <?php echo $_GET['msg']; ?> successfully.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>ID</th><th>Road Name</th><th>Type</th><th>Distance</th><th>Lanes</th><th>Status</th><th>Added By</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($roads as $r): ?>
                        <tr>
                            <td>#<?php echo $r['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($r['road_name']); ?></strong></td>
                            <td><span class="badge bg-info"><?php echo $r['road_type']; ?></span></td>
                            <td><?php echo formatDistance($r['distance_km']); ?></td>
                            <td><?php echo $r['lanes']; ?></td>
                            <td><span class="badge bg-<?php echo $r['status'] === 'open' ? 'success' : ($r['status'] === 'under_construction' ? 'warning' : 'danger'); ?>"><?php echo $r['status']; ?></span></td>
                            <td><small><?php echo htmlspecialchars($r['added_by_name'] ?? 'N/A'); ?></small></td>
                            <td>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this road?')">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
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
</div>

<div class="modal fade" id="addRoadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-road me-2"></i>Add New Road</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body">
                    <?php echo csrfField(); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Road Name</label><input type="text" name="road_name" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Road Type</label><select name="road_type" class="form-select"><option>Highway</option><option>Expressway</option><option>City Road</option><option>Rural Road</option></select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">Start Lat</label><input type="text" name="start_lat" class="form-control" step="any"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Start Lng</label><input type="text" name="start_lng" class="form-control" step="any"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">End Lat</label><input type="text" name="end_lat" class="form-control" step="any"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">End Lng</label><input type="text" name="end_lng" class="form-control" step="any"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Distance (km)</label><input type="number" name="distance_km" class="form-control" step="0.01"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Lanes</label><input type="number" name="lanes" class="form-control" value="2" min="1"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Surface Type</label><select name="surface_type" class="form-select"><option>Asphalt</option><option>Concrete</option><option>Gravel</option></select></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="open">Open</option><option value="closed">Closed</option><option value="under_construction">Under Construction</option></select></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Road</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

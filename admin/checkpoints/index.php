<?php
define('PAGE_TITLE', 'Checkpoints');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf)) die('Invalid token');
    $stmt = $db->prepare("INSERT INTO checkpoints (checkpoint_name, latitude, longitude, type, status, description, added_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([sanitize($_POST['checkpoint_name']), $_POST['latitude'], $_POST['longitude'], $_POST['type'], $_POST['status'], sanitize($_POST['description']), $_SESSION['user_id']]);
    logActivity($_SESSION['user_id'], 'add_checkpoint', 'Added checkpoint: ' . $_POST['checkpoint_name']);
    header('Location: index.php?msg=added');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $db->prepare("DELETE FROM checkpoints WHERE id = ?")->execute([$_POST['id']]);
    header('Location: index.php?msg=deleted');
    exit;
}

$checkpoints = $db->query("SELECT c.*, u.username as added_by_name FROM checkpoints c LEFT JOIN users u ON c.added_by = u.id ORDER BY c.created_at DESC")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-map-pin me-2 text-primary"></i>Official Checkpoints</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Add Checkpoint</button>
    </div>
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">Checkpoint <?php echo $_GET['msg']; ?>.</div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead><tr><th>Name</th><th>Type</th><th>Coordinates</th><th>Status</th><th>Added By</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($checkpoints as $c): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($c['checkpoint_name']); ?></strong></td>
                        <td><span class="badge bg-info"><?php echo str_replace('_', ' ', $c['type']); ?></span></td>
                        <td><small><?php echo $c['latitude']; ?>, <?php echo $c['longitude']; ?></small></td>
                        <td><span class="badge bg-<?php echo $c['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo $c['status']; ?></span></td>
                        <td><small><?php echo htmlspecialchars($c['added_by_name'] ?? 'N/A'); ?></small></td>
                        <td>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this checkpoint?')">
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
    <div class="modal-header"><h5>Add Checkpoint</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST">
        <div class="modal-body">
            <?php echo csrfField(); ?>
            <div class="mb-3"><label>Checkpoint Name</label><input type="text" name="checkpoint_name" class="form-control" required></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Latitude</label><input type="text" name="latitude" class="form-control" step="any" required></div>
                <div class="col-md-6 mb-3"><label>Longitude</label><input type="text" name="longitude" class="form-control" step="any" required></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Type</label><select name="type" class="form-select"><option value="traffic_police">Traffic Police</option><option value="speed_camera">Speed Camera</option><option value="red_light_camera">Red Light Camera</option><option value="toll_booth">Toll Booth</option><option value="weigh_station">Weigh Station</option><option value="border">Border</option></select></div>
                <div class="col-md-6 mb-3"><label>Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            </div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="submit" name="save" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

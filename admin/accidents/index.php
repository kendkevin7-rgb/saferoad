<?php
define('PAGE_TITLE', 'Accident Reports');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid token');
    if (isset($_POST['save'])) {
        $stmt = $db->prepare("INSERT INTO reports (title, report_type, description, latitude, longitude, reported_by, status) VALUES (?, 'accident', ?, ?, ?, ?, 'pending')");
        $stmt->execute([sanitize($_POST['title']), sanitize($_POST['description']), $_POST['latitude'] ?: null, $_POST['longitude'] ?: null, $_SESSION['user_id']]);
        $alertStmt = $db->prepare("INSERT INTO alerts (alert_type, title, description, latitude, longitude, severity, radius_meters, reported_by) VALUES ('accident', ?, ?, ?, ?, 'high', 500, ?)");
        $alertStmt->execute([sanitize($_POST['title']), sanitize($_POST['description']), $_POST['latitude'] ?: null, $_POST['longitude'] ?: null, $_SESSION['user_id']]);
        header('Location: index.php?msg=added');
        exit;
    }
    if (isset($_POST['resolve'])) {
        $stmt = $db->prepare("UPDATE reports SET status = 'resolved' WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $stmt = $db->prepare("UPDATE alerts SET is_active = 0 WHERE title = (SELECT title FROM reports WHERE id = ?)");
        $stmt->execute([$_POST['id']]);
        header('Location: index.php?msg=resolved');
        exit;
    }
}

$accidents = $db->query("SELECT r.*, u.username FROM reports r JOIN users u ON r.reported_by = u.id WHERE r.report_type = 'accident' ORDER BY r.created_at DESC")->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-car-crash me-2 text-primary"></i>Accident Reports</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fas fa-plus me-2"></i>Report Accident</button>
    </div>
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">Accident report <?php echo $_GET['msg']; ?>.</div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead><tr><th>Title</th><th>Reported By</th><th>Location</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach ($accidents as $a): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($a['username']); ?></td>
                        <td><small><?php echo $a['latitude'] ? $a['latitude'] . ', ' . $a['longitude'] : 'N/A'; ?></small></td>
                        <td><span class="badge bg-<?php echo $a['status'] === 'pending' ? 'warning' : ($a['status'] === 'resolved' ? 'success' : 'info'); ?>"><?php echo $a['status']; ?></span></td>
                        <td><small><?php echo timeAgo($a['created_at']); ?></small></td>
                        <td>
                            <?php if ($a['status'] !== 'resolved'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Mark as resolved?')">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                <button type="submit" name="resolve" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5>Report Accident</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST">
        <div class="modal-body">
            <?php echo csrfField(); ?>
            <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label>Latitude</label><input type="text" name="latitude" class="form-control" step="any"></div>
                <div class="col-md-6 mb-3"><label>Longitude</label><input type="text" name="longitude" class="form-control" step="any"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="submit" name="save" class="btn btn-danger">Report Accident</button></div>
    </form>
</div></div></div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

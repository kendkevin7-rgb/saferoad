<?php
define('PAGE_TITLE', 'Manage Users');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    $userId = (int)$_POST['user_id'];
    $stmt = $db->prepare("UPDATE users SET is_active = !is_active WHERE id = ?");
    $stmt->execute([$userId]);
    logActivity($_SESSION['user_id'], 'toggle_user_status', "Toggled user #$userId");
    header('Location: index.php?msg=updated');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int)$_POST['user_id'];
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role_id = 2");
    $stmt->execute([$userId]);
    logActivity($_SESSION['user_id'], 'delete_user', "Deleted user #$userId");
    header('Location: index.php?msg=deleted');
    exit;
}

$stmt = $db->query("SELECT u.*, r.role_name, (SELECT COUNT(*) FROM driver_locations WHERE user_id = u.id) as location_count,
    (SELECT MAX(recorded_at) FROM driver_locations WHERE user_id = u.id) as last_seen
    FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC");
$users = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-users me-2 text-primary"></i>Manage Users</h4>
    </div>
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show">User updated successfully.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><span class="badge bg-<?php echo $user['role_name'] === 'admin' ? 'danger' : 'primary'; ?>"><?php echo $user['role_name']; ?></span></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?php echo $user['last_seen'] ? timeAgo($user['last_seen']) : 'Never'; ?></small></td>
                            <td>
                                <?php if ($user['role_name'] !== 'admin'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="toggle_active" class="btn btn-sm btn-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>"
                                        onclick="return confirm('Toggle status for <?php echo addslashes($user['username']); ?>?')">
                                        <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete user <?php echo addslashes($user['username']); ?>?')">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
</div>
<script>
$(document).ready(function() {
    $('#usersTable').DataTable ? $('#usersTable').DataTable({ pageLength: 25 }) : null;
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

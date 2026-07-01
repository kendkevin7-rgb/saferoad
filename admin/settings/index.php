<?php
define('PAGE_TITLE', 'Settings');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid token');
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $db->prepare("UPDATE settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?");
        $stmt->execute([sanitize($value), $_SESSION['user_id'], $key]);
    }
    logActivity($_SESSION['user_id'], 'update_settings', 'System settings updated');
    $msg = 'Settings saved successfully.';
}

$settings = $db->query("SELECT * FROM settings ORDER BY setting_group, setting_key")->fetchAll();
$grouped = [];
foreach ($settings as $s) {
    $grouped[$s['setting_group']][] = $s;
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <h4 class="fw-bold mb-4"><i class="fas fa-cogs me-2 text-primary"></i>System Settings</h4>
    <?php if (isset($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <form method="POST">
        <?php echo csrfField(); ?>
        <?php foreach ($grouped as $group => $items): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-capitalize"><?php echo htmlspecialchars($group); ?> Settings</h5>
            </div>
            <div class="card-body">
                <?php foreach ($items as $item): ?>
                <div class="mb-3">
                    <label class="form-label"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $item['setting_key']))); ?></label>
                    <?php if (in_array($item['setting_key'], ['voice_warning_enabled', 'dark_mode', 'notifications_enabled'])): ?>
                    <select name="settings[<?php echo $item['setting_key']; ?>]" class="form-select">
                        <option value="1" <?php echo $item['setting_value'] === '1' ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?php echo $item['setting_value'] === '0' ? 'selected' : ''; ?>>Disabled</option>
                    </select>
                    <?php elseif ($item['setting_key'] === 'google_maps_api_key'): ?>
                    <input type="password" name="settings[<?php echo $item['setting_key']; ?>]" class="form-control" value="<?php echo htmlspecialchars($item['setting_value']); ?>">
                    <?php else: ?>
                    <input type="text" name="settings[<?php echo $item['setting_key']; ?>]" class="form-control" value="<?php echo htmlspecialchars($item['setting_value']); ?>">
                    <?php endif; ?>
                    <small class="text-muted">Group: <?php echo $group; ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <button type="submit" name="save_settings" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Save All Settings</button>
    </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

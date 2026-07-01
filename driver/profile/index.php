<?php
define('PAGE_TITLE', 'My Profile');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid token');
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $msg = 'Only JPG, PNG, GIF, WEBP files are allowed.';
        } else {
            $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
            $dest = UPLOAD_PATH . $filename;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
                $stmt = $db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->execute([$filename, $userId]);
                $user = getUserById($userId);
                $msg = 'Profile photo updated successfully.';
                logActivity($userId, 'upload_photo', 'Updated profile picture');
            } else {
                $msg = 'Failed to upload file.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid token');
    $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->execute([sanitize($_POST['full_name']), sanitize($_POST['phone']), sanitize($_POST['address']), $userId]);
    logActivity($userId, 'update_profile', 'Updated profile information');
    $user = getUserById($userId);
    $_SESSION['full_name'] = $user['full_name'];
    $msg = 'Profile updated successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid token');
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $pwError = 'Current password is incorrect.';
    } elseif ($new !== $confirm) {
        $pwError = 'Passwords do not match.';
    } else {
        $pwErrors = validatePasswordStrength($new);
        if (!empty($pwErrors)) {
            $pwError = 'Password must contain ' . implode(', ', $pwErrors) . '.';
        } else {
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
            $pwMsg = 'Password changed successfully.';
            logActivity($userId, 'change_password', 'Password changed');
        }
    }
}

include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="mb-3">
                        <?php
                        $pic = $user['profile_pic'] ?: 'default.svg';
                        $picUrl = ($user['profile_pic'] && file_exists(UPLOAD_PATH . $user['profile_pic'])) ? UPLOAD_URL . $user['profile_pic'] : UPLOAD_URL . 'default.svg';
                        ?>
                        <img src="<?php echo $picUrl; ?>" alt="Profile" class="rounded-circle mb-2" style="width:100px;height:100px;object-fit:cover;border:3px solid #667eea;">
                        <h4 class="fw-bold mt-2"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <span class="badge bg-<?php echo $user['role_name'] === 'admin' ? 'danger' : 'primary'; ?>"><?php echo $user['role_name']; ?></span>
                        <form method="POST" enctype="multipart/form-data" class="mt-3">
                            <?php echo csrfField(); ?>
                            <input type="file" name="profile_pic" class="form-control form-control-sm mb-2" accept="image/jpeg,image/png,image/gif,image/webp" required>
                            <button type="submit" name="upload_photo" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-upload me-1"></i>Upload Photo</button>
                        </form>
                    </div>
                    <hr>
                    <div class="text-start">
                        <p><i class="fas fa-envelope me-2 text-muted"></i><?php echo htmlspecialchars($user['email']); ?></p>
                        <p><i class="fas fa-phone me-2 text-muted"></i><?php echo htmlspecialchars($user['phone'] ?: 'Not set'); ?></p>
                        <p><i class="fas fa-calendar me-2 text-muted"></i>Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                        <p><i class="fas fa-sign-in-alt me-2 text-muted"></i>Last login: <?php echo $user['last_login'] ? timeAgo($user['last_login']) : 'Never'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <?php if (isset($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $msg; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Edit Profile</h5></div>
                <div class="card-body">
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled><small class="text-muted">Email cannot be changed</small></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="1"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea></div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Profile</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-lock me-2 text-primary"></i>Change Password</h5></div>
                <div class="card-body">
                    <?php if (isset($pwError)): ?>
                    <div class="alert alert-danger"><?php echo $pwError; ?></div>
                    <?php endif; ?>
                    <?php if (isset($pwMsg)): ?>
                    <div class="alert alert-success"><?php echo $pwMsg; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <div class="mb-3"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required minlength="6"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning"><i class="fas fa-key me-2"></i>Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>

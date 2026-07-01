<?php
define('PAGE_TITLE', 'Reset Password');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $token = $_POST['token'] ?? '';
    if (!verifyCSRFToken($csrf)) {
        $error = 'Invalid security token.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $pwErrors = validatePasswordStrength($password);
        if (!empty($pwErrors)) {
            $error = 'Password must contain ' . implode(', ', $pwErrors) . '.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()");
            $stmt->execute([$token]);
            $row = $stmt->fetch();

            if ($row) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $row['user_id']]);

                $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                $stmt->execute([$row['user_id']]);

                logActivity($row['user_id'], 'password_reset', 'Password reset via email');
                $success = 'Password has been reset successfully! <a href="login.php" class="alert-link">Login now</a>.';
            } else {
                $error = 'Invalid or expired reset token. Please request a new one.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center">
                <div class="auth-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Reset Password</h2>
                <p class="text-muted">Choose a new password for your account</p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php else: ?>
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-lock me-2"></i>New Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" required minlength="6" placeholder="Min 6 characters">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-check-circle me-2"></i>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control form-control-lg" required placeholder="Repeat password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-save me-2"></i>Reset Password
                    </button>
                    <div class="text-center">
                        <p class="mb-0"><a href="login.php" class="fw-bold">Back to Login</a></p>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
define('PAGE_TITLE', 'Forgot Password');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf)) {
        $error = 'Invalid security token.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        if (!validateEmail($email)) {
            $error = 'Please enter a valid email address.';
        } else {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id, full_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);

                $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                $stmt->execute([$user['id']]);

                $stmt = $db->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires]);

                $resetLink = SITE_URL . "reset-password.php?token=$token";
                $name = htmlspecialchars($user['full_name'] ?: 'User');
                $body = "
                <html><body style='font-family: Arial, sans-serif; padding: 20px;'>
                    <h2>Password Reset</h2>
                    <p>Hello $name,</p>
                    <p>Click the link below to reset your password. This link expires in 1 hour.</p>
                    <p><a href='$resetLink' style='display:inline-block;padding:12px 24px;background:#667eea;color:#fff;text-decoration:none;border-radius:6px;'>Reset Password</a></p>
                    <p>If you didn't request this, ignore this email.</p>
                </body></html>";

                sendEmail($email, 'Password Reset - ' . SITE_NAME, $body);
            }
            $success = 'If that email is registered, we have sent a password reset link. Please check your inbox.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center">
                <div class="auth-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h2>Forgot Password</h2>
                <p class="text-muted">Enter your email to receive a reset link</p>
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
                <?php endif; ?>
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg" required placeholder="your@email.com">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                    <div class="text-center">
                        <p class="mb-0"><a href="login.php" class="fw-bold">Back to Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
define('PAGE_TITLE', 'Login');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf)) {
        $error = 'Invalid security token.';
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (isRateLimited('login_attempt', $ip, 5, 300)) {
            $error = 'Too many login attempts. Please try again in 5 minutes.';
        } else {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Please enter username and password.';
            } else {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role_name'];
                    $_SESSION['full_name'] = $user['full_name'];

                    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);

                    logActivity($user['id'], 'login', 'User logged in successfully');
                    createNotification($user['id'], 'Welcome Back', 'You have successfully logged in.', 'success');

                    clearRateLimit('login_attempt', $ip);

                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid username/email or password.';
                    logActivity(0, 'login_failed', "Failed login attempt for: $username");
                }
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
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center">
                <div class="auth-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2><?php echo SITE_NAME; ?></h2>
                <p class="text-muted">Smart Road Safety & Traffic Alert System</p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user me-2"></i>Username or Email</label>
                        <input type="text" name="username" class="form-control form-control-lg" required autofocus placeholder="Enter username or email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control form-control-lg" required placeholder="Enter password" id="loginPassword">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-end mb-3">
                        <a href="forgot-password.php" class="text-decoration-none small">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="fw-bold">Register</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const pwd = document.getElementById('loginPassword');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>

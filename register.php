<?php
define('PAGE_TITLE', 'Register');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$captchaQuestion = generateCaptcha();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf)) {
        $error = 'Invalid security token.';
    } elseif (!verifyCaptcha($_POST['captcha'] ?? '')) {
        $error = 'Incorrect CAPTCHA answer. Please try again.';
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (isRateLimited('register_attempt', $ip, 3, 3600)) {
            $error = 'Too many registration attempts from this IP. Please try again later.';
        } else {
            $username = sanitize($_POST['username'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $fullName = sanitize($_POST['full_name'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');

            if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
                $error = 'Please fill all required fields.';
            } elseif (!validateEmail($email)) {
                $error = 'Invalid email address.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $pwErrors = validatePasswordStrength($password);
                if (!empty($pwErrors)) {
                    $error = 'Password must contain ' . implode(', ', $pwErrors) . '.';
                } else {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    $error = 'Username or email already exists.';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (role_id, username, email, password, full_name, phone) VALUES (2, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$username, $email, $hashed, $fullName, $phone])) {
                        $success = 'Registration successful! <a href="login.php" class="alert-link">Login now</a>.';
                        logActivity(0, 'user_registered', "New user registered: $username");
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
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
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header text-center">
                <div class="auth-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Create Account</h2>
                <p class="text-muted">Join <?php echo SITE_NAME; ?> for safer driving</p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="John Doe">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="9876543210">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user me-2"></i>Username *</label>
                        <input type="text" name="username" class="form-control" required placeholder="Choose a username">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope me-2"></i>Email *</label>
                        <input type="email" name="email" class="form-control" required placeholder="your@email.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-lock me-2"></i>Password *</label>
                            <input type="password" name="password" class="form-control" required placeholder="Min 6 characters">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-check-circle me-2"></i>Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-calculator me-2"></i>What is <?php echo $captchaQuestion; ?>?</label>
                        <input type="text" name="captcha" class="form-control" required placeholder="Enter answer">
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                    <div class="text-center">
                        <p class="mb-0">Already have an account? <a href="login.php" class="fw-bold">Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

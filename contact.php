<?php
define('PAGE_TITLE', 'Contact Us');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$msg = '';
$error = '';
$captchaQuestion = generateCaptcha();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } elseif (!verifyCaptcha($_POST['captcha'] ?? '')) {
        $error = 'Incorrect CAPTCHA answer. Please try again.';
    } else {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $subject = sanitize($_POST['subject'] ?? 'No Subject');
        $message = sanitize($_POST['message']);
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $subject, $message])) {
            $body = "
            <html><body style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2>New Contact Message</h2>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p><strong>Message:</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            </body></html>";
            sendEmail(ADMIN_EMAIL, "Contact Form: $subject", $body);
            $msg = 'Thank you for your message. We will get back to you soon.';
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
if (isLoggedIn()) include __DIR__ . '/includes/navbar.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-envelope text-primary fa-4x mb-3"></i>
                    <h2 class="fw-bold">Contact Us</h2>
                    <p class="text-muted">Have questions or suggestions? We'd love to hear from you.</p>
                    <?php if ($msg): ?><div class="alert alert-success"><?php echo $msg; ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body p-4">
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                        </div>
                        <div class="mb-3"><label>Subject</label><input type="text" name="subject" class="form-control"></div>
                        <div class="mb-3"><label>Message</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
                        <div class="mb-3">
                            <label><i class="fas fa-calculator me-2"></i>What is <?php echo $captchaQuestion; ?>?</label>
                            <input type="text" name="captcha" class="form-control" required placeholder="Enter answer">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
define('PAGE_TITLE', 'Page Not Found');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
if (isLoggedIn()) include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5 text-center">
    <div class="py-5">
        <i class="fas fa-exclamation-triangle text-warning fa-5x mb-4"></i>
        <h1 class="fw-bold">404</h1>
        <h3 class="text-muted">Page Not Found</h3>
        <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-home me-2"></i>Go Home
        </a>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

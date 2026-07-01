<?php
define('PAGE_TITLE', 'About');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
include __DIR__ . '/includes/header.php';
if (isLoggedIn()) include __DIR__ . '/includes/navbar.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <i class="fas fa-shield-alt text-primary fa-5x mb-3"></i>
                <h1 class="fw-bold"><?php echo SITE_NAME; ?></h1>
                <p class="lead text-muted">Smart Road Safety & Traffic Alert System v1.0</p>
            </div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4><i class="fas fa-info-circle me-2 text-primary"></i>About the System</h4>
                    <p>SafeRoad AI is a comprehensive road safety platform designed to help drivers travel safely by providing real-time GPS tracking, speed monitoring, traffic alerts, and smart route planning. The system prioritizes road safety, privacy, and legal compliance.</p>
                    <p>It uses official traffic control locations, verified checkpoints, accident reports, construction zones, and speed limit information from authorized sources &mdash; not individual tracking of traffic police officers.</p>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-map-marked-alt text-primary fa-3x mb-3"></i>
                            <h5>Real-Time Tracking</h5>
                            <p class="text-muted">GPS location updates every few seconds with Google Maps visualization.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-tachometer-alt text-danger fa-3x mb-3"></i>
                            <h5>Speed Monitoring</h5>
                            <p class="text-muted">Voice warnings via Web Speech API when exceeding speed limits.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <h5>Smart Alerts</h5>
                            <p class="text-muted">Accidents, road closures, construction, floods, school zones, and more.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-route text-success fa-3x mb-3"></i>
                            <h5>Smart Routing</h5>
                            <p class="text-muted">Alternative routes that avoid blocked roads, traffic, and hazards.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4><i class="fas fa-shield-alt me-2 text-primary"></i>Privacy & Compliance</h4>
                    <p>SafeRoad AI is committed to protecting your privacy. All location data is encrypted and stored securely. You have full control over your data and can delete your history at any time.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="text-center py-4 bg-dark text-white">
    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/env.php';

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'saferoad_db'));

define('SITE_NAME', 'SafeRoad AI');
define('SITE_URL', env('SITE_URL', 'http://localhost/SafeRoadAI/'));
define('ADMIN_EMAIL', env('ADMIN_EMAIL', 'admin@saferoad.ai'));
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . 'uploads/');

define('SPEED_WARNING_THRESHOLD', 10);
define('LOCATION_UPDATE_INTERVAL', 5);
define('SESSION_TIMEOUT', 3600);

define('GOOGLE_MAPS_API_KEY', env('GOOGLE_MAPS_API_KEY', ''));
define('TIMEZONE', env('TIMEZONE', 'Asia/Kolkata'));

define('SMTP_HOST', env('SMTP_HOST', ''));
define('SMTP_PORT', env('SMTP_PORT', '587'));
define('SMTP_USER', env('SMTP_USER', ''));
define('SMTP_PASS', env('SMTP_PASS', ''));
define('SMTP_FROM', env('SMTP_FROM', 'noreply@saferoad.ai'));

date_default_timezone_set(TIMEZONE);
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

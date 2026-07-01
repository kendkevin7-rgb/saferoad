<?php
require_once __DIR__ . '/../config/database.php';

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sanitizeArray($array) {
    return array_map('sanitize', $array);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePasswordStrength($password) {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'at least 8 characters';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'an uppercase letter';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'a lowercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'a number';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'a special character (!@#$%^&*)';
    }
    return $errors;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . 'login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . 'dashboard.php');
        exit;
    }
}

function getUserById($id) {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function logActivity($userId, $action, $details = null) {
    $db = Database::getInstance();
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId ?: null,
        $action,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
}

function createNotification($userId, $title, $message, $type = 'info') {
    $db = Database::getInstance();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $title, $message, $type]);
}

function getUnreadNotifications($userId) {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getActiveAlerts($lat = null, $lng = null, $radius = 50000) {
    $db = Database::getInstance();
    $sql = "SELECT a.*, r.road_name FROM alerts a LEFT JOIN roads r ON a.road_id = r.id WHERE a.is_active = 1 AND (a.expires_at IS NULL OR a.expires_at > NOW())";
    if ($lat && $lng) {
        $sql .= " AND (6371 * acos(cos(radians(?)) * cos(radians(a.latitude)) * cos(radians(a.longitude) - radians(?)) + sin(radians(?)) * sin(radians(a.latitude)))) <= ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$lat, $lng, $lat, $radius / 1000]);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function getSpeedLimit($lat, $lng) {
    $db = Database::getInstance();
    $stmt = $db->prepare("
        SELECT sl.max_speed, r.road_name, r.id as road_id,
            (6371 * acos(cos(radians(?)) * cos(radians(r.start_lat)) * cos(radians(r.start_lng) - radians(?)) + sin(radians(?)) * sin(radians(r.start_lat)))) AS dist_start,
            (6371 * acos(cos(radians(?)) * cos(radians(r.end_lat)) * cos(radians(r.end_lng) - radians(?)) + sin(radians(?)) * sin(radians(r.end_lat)))) AS dist_end
        FROM speed_limits sl 
        JOIN roads r ON sl.road_id = r.id 
        WHERE sl.is_active = 1 
        AND r.status = 'open'
        HAVING LEAST(dist_start, dist_end) < 1
        ORDER BY LEAST(dist_start, dist_end) ASC
        LIMIT 1
    ");
    $stmt->execute([$lat, $lng, $lat, $lat, $lng, $lat]);
    return $stmt->fetch();
}

function formatSpeed($speed) {
    return number_format($speed, 1) . ' km/h';
}

function formatDistance($km) {
    if ($km < 1) {
        return number_format($km * 1000, 0) . ' m';
    }
    return number_format($km, 2) . ' km';
}

function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    if ($hours > 0) {
        return "{$hours}h {$mins}m";
    }
    return "{$mins} min";
}

function getCheckpoints($lat = null, $lng = null, $radius = 5000) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM checkpoints WHERE status = 'active'";
    if ($lat && $lng) {
        $sql .= " AND (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$lat, $lng, $lat, $radius / 1000]);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function getDriverStats($userId) {
    $db = Database::getInstance();
    $stats = [];

    $stmt = $db->prepare("SELECT COUNT(*) as total_warnings FROM warning_logs WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['warnings'] = $stmt->fetch()['total_warnings'];

    $stmt = $db->prepare("SELECT COUNT(*) as total_trips FROM driving_history WHERE user_id = ? AND is_completed = 1");
    $stmt->execute([$userId]);
    $stats['trips'] = $stmt->fetch()['total_trips'];

    $stmt = $db->prepare("SELECT COALESCE(SUM(distance_km), 0) as total_distance FROM driving_history WHERE user_id = ? AND is_completed = 1");
    $stmt->execute([$userId]);
    $stats['distance'] = $stmt->fetch()['total_distance'];

    $stmt = $db->prepare("SELECT COALESCE(MAX(speed), 0) as top_speed FROM driver_locations WHERE user_id = ?");
    $stmt->execute([$userId]);
    $stats['top_speed'] = $stmt->fetch()['top_speed'];

    return $stats;
}

function getAdminStats() {
    $db = Database::getInstance();
    $stats = [];

    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role_id = 2 AND is_active = 1");
    $stats['total_drivers'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM driver_locations WHERE recorded_at >= NOW() - INTERVAL 5 MINUTE");
    $stats['online_drivers'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM alerts WHERE is_active = 1");
    $stats['active_alerts'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM checkpoints WHERE status = 'active'");
    $stats['active_checkpoints'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM warning_logs WHERE DATE(created_at) = CURDATE()");
    $stats['speed_violations_today'] = $stmt->fetch()['total'];

    $stmt = $db->query("SELECT COUNT(*) as total FROM reports WHERE status = 'pending'");
    $stats['pending_reports'] = $stmt->fetch()['total'];

    return $stats;
}

function getTrafficStats($days = 7) {
    $db = Database::getInstance();
    $stats = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as total 
            FROM warning_logs 
            WHERE DATE(created_at) = ? 
            GROUP BY DATE(created_at)
        ");
        $stmt->execute([$date]);
        $row = $stmt->fetch();
        $stats[] = [
            'date' => $date,
            'total' => $row ? (int)$row['total'] : 0
        ];
    }
    return $stats;
}

function exportToCSV($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    fclose($output);
    exit;
}

function getSetting($key, $default = null) {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function isRateLimited($action, $identifier, $maxAttempts = 5, $windowSeconds = 300) {
    $file = sys_get_temp_dir() . "/ratelimit_" . md5("$action:$identifier");
    $data = @file_get_contents($file);
    $attempts = $data ? json_decode($data, true) : [];
    $now = time();
    $attempts = array_filter($attempts, fn($t) => $t > $now - $windowSeconds);
    if (count($attempts) >= $maxAttempts) {
        return true;
    }
    $attempts[] = $now;
    file_put_contents($file, json_encode($attempts), LOCK_EX);
    return false;
}

function clearRateLimit($action, $identifier) {
    $file = sys_get_temp_dir() . "/ratelimit_" . md5("$action:$identifier");
    @unlink($file);
}

function generateCaptcha() {
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    $ops = ['+', '*'];
    $op = $ops[array_rand($ops)];
    $result = $op === '+' ? $num1 + $num2 : $num1 * $num2;
    $_SESSION['captcha_result'] = $result;
    return "$num1 $op $num2";
}

function verifyCaptcha($answer) {
    if (!isset($_SESSION['captcha_result'])) return false;
    $correct = (int)$_SESSION['captcha_result'] === (int)$answer;
    unset($_SESSION['captcha_result']);
    return $correct;
}

function sendEmail($to, $subject, $body) {
    if (empty(SMTP_HOST)) {
        return mail($to, $subject, $body, "From: " . SMTP_FROM . "\r\nContent-Type: text/html; charset=UTF-8\r\n");
    }
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: " . SMTP_FROM . "\r\n";
    return mail($to, $subject, $body, $headers);
}

function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return date('M d, H:i', $time);
}

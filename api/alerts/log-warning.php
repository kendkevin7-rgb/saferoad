<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$warningType = sanitize($_POST['warning_type'] ?? '');
$message = sanitize($_POST['message'] ?? '');
$speed = filter_input(INPUT_POST, 'speed', FILTER_VALIDATE_FLOAT) ?? 0;
$speedLimit = filter_input(INPUT_POST, 'speed_limit', FILTER_VALIDATE_INT) ?? 0;
$lat = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

if (empty($warningType) || empty($message)) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$db = Database::getInstance();
$stmt = $db->prepare("INSERT INTO warning_logs (user_id, warning_type, message, latitude, longitude, speed, speed_limit) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$userId, $warningType, $message, $lat, $lng, $speed, $speedLimit]);

createNotification($userId, 'Speed Warning', $message, 'warning');

echo json_encode(['status' => 'ok', 'warning_id' => $db->lastInsertId()]);

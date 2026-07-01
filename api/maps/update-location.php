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
$lat = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);
$speed = filter_input(INPUT_POST, 'speed', FILTER_VALIDATE_FLOAT) ?? 0;
$heading = filter_input(INPUT_POST, 'heading', FILTER_VALIDATE_FLOAT) ?? 0;
$accuracy = filter_input(INPUT_POST, 'accuracy', FILTER_VALIDATE_FLOAT) ?? 0;

if (!$lat || !$lng) {
    echo json_encode(['error' => 'Invalid coordinates']);
    exit;
}

$db = Database::getInstance();
$stmt = $db->prepare("INSERT INTO driver_locations (user_id, latitude, longitude, speed, heading, accuracy, is_moving) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$userId, $lat, $lng, $speed, $heading, $accuracy, $speed > 2 ? 1 : 0]);

$response = ['status' => 'ok'];

$speedData = getSpeedLimit($lat, $lng);
if ($speedData) {
    $response['speed_limit'] = (int)$speedData['max_speed'];
    $response['road_name'] = $speedData['road_name'];
}

$alerts = getActiveAlerts($lat, $lng, 5000);
if ($alerts) {
    $response['nearby_alerts'] = $alerts;
}

$response['online_drivers'] = getAdminStats()['online_drivers'] ?? 0;

echo json_encode($response);

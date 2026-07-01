<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$originLat = filter_input(INPUT_GET, 'origin_lat', FILTER_VALIDATE_FLOAT);
$originLng = filter_input(INPUT_GET, 'origin_lng', FILTER_VALIDATE_FLOAT);
$destLat = filter_input(INPUT_GET, 'dest_lat', FILTER_VALIDATE_FLOAT);
$destLng = filter_input(INPUT_GET, 'dest_lng', FILTER_VALIDATE_FLOAT);

if (!$originLat || !$originLng || !$destLat || !$destLng) {
    echo json_encode(['error' => 'Invalid coordinates']);
    exit;
}

$blockedRoads = [];
$db = Database::getInstance();
$stmt = $db->query("SELECT id, road_name FROM roads WHERE status != 'open'");
while ($row = $stmt->fetch()) {
    $blockedRoads[] = $row['road_name'];
}

$alerts = getActiveAlerts();
$alertPoints = [];
foreach ($alerts as $alert) {
    if ($alert['latitude'] && $alert['longitude']) {
        $alertPoints[] = [
            'lat' => $alert['latitude'],
            'lng' => $alert['longitude'],
            'type' => $alert['alert_type'],
            'severity' => $alert['severity']
        ];
    }
}

echo json_encode([
    'status' => 'ok',
    'origin' => ['lat' => $originLat, 'lng' => $originLng],
    'destination' => ['lat' => $destLat, 'lng' => $destLng],
    'blocked_roads' => $blockedRoads,
    'alerts' => $alertPoints,
    'avoid_zones' => $alertPoints
]);

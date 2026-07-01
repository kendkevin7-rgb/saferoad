<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

header('Content-Type: application/json');

$db = Database::getInstance();
$stmt = $db->query("SELECT u.id, u.username, u.full_name, dl.latitude, dl.longitude, dl.speed, dl.heading, dl.recorded_at
    FROM driver_locations dl JOIN users u ON dl.user_id = u.id
    WHERE dl.id IN (SELECT MAX(id) FROM driver_locations GROUP BY user_id)
    ORDER BY dl.recorded_at DESC");
echo json_encode($stmt->fetchAll());

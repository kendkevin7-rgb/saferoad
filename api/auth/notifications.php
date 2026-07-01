<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

$notifications = getUnreadNotifications($_SESSION['user_id']);
echo json_encode($notifications);

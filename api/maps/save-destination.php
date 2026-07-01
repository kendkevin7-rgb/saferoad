<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    $stmt = $db->prepare("INSERT INTO destinations (user_id, name, latitude, longitude, address, is_favorite) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        sanitize($_POST['name']),
        $_POST['latitude'],
        $_POST['longitude'],
        sanitize($_POST['address'] ?? ''),
        isset($_POST['is_favorite']) ? 1 : 0
    ]);
    logActivity($_SESSION['user_id'], 'add_destination', 'Added destination: ' . $_POST['name']);
}

header('Location: ' . SITE_URL . 'driver/routes/index.php?msg=saved');
exit;

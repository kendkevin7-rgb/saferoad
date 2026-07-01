<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$stmt = $db->query("SELECT * FROM checkpoints WHERE status = 'active' ORDER BY checkpoint_name");
echo json_encode($stmt->fetchAll());

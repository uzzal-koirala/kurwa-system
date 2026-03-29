<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if ($_SESSION['role'] !== 'admin') {
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$location_id = (int)($_GET['location_id'] ?? 0);

if ($location_id <= 0) {
    echo json_encode([]);
    exit;
}

$hospitals = $conn->query("SELECT id, name FROM hospitals WHERE location_id = $location_id ORDER BY name ASC");
$results = [];

while ($h = $hospitals->fetch_assoc()) {
    $results[] = $h;
}

echo json_encode($results);
?>

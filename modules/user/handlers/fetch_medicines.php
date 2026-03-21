<?php
require_once '../../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

header('Content-Type: application/json');

if (!isset($_GET['pharmacy_id'])) {
    echo json_encode([]);
    exit;
}

$pharmacy_id = intval($_GET['pharmacy_id']);
$medicines = [];

$res = $conn->query("SELECT * FROM medicines WHERE pharmacy_id = $pharmacy_id AND stock_status = 'in_stock'");

if ($res) {
    while($row = $res->fetch_assoc()) {
        $medicines[] = $row;
    }
}

echo json_encode($medicines);
?>

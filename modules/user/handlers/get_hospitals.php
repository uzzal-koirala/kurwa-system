<?php
include '../../../includes/core/config.php';

header('Content-Type: application/json');

if (isset($_GET['location_id'])) {
    $location_id = intval($_GET['location_id']);
    
    $stmt = $conn->prepare("SELECT id, name FROM hospitals WHERE location_id = ?");
    $stmt->bind_param("i", $location_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hospitals = [];
    while ($row = $result->fetch_assoc()) {
        $hospitals[] = $row;
    }
    
    echo json_encode($hospitals);
} else {
    echo json_encode([]);
}
?>

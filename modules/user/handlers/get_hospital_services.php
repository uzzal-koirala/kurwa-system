<?php
/**
 * AJAX Handler to get available services at a specific hospital
 */
header('Content-Type: application/json');
require_once '../../../includes/core/config.php';

$hospital_id = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : 0;

if ($hospital_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid hospital ID.']);
    exit;
}

// 1. Fetch Caretakers count
$caretaker_res = $conn->query("SELECT COUNT(*) as count FROM caretakers WHERE hospital_id = $hospital_id");
$caretaker_count = $caretaker_res ? $caretaker_res->fetch_assoc()['count'] : 0;

// 2. Fetch Pharmacies count
$pharmacy_res = $conn->query("SELECT COUNT(*) as count FROM pharmacies WHERE hospital_id = $hospital_id");
$pharmacy_count = $pharmacy_res ? $pharmacy_res->fetch_assoc()['count'] : 0;

// 3. Fetch Restaurants/Canteens count
// Check canteens table for hospital_id and also restaurants table
$canteen_res = $conn->query("SELECT COUNT(*) as count FROM canteens WHERE hospital_id = $hospital_id");
$canteen_count = $canteen_res ? $canteen_res->fetch_assoc()['count'] : 0;

$restaurant_res = $conn->query("SELECT COUNT(*) as count FROM restaurants WHERE hospital_id = $hospital_id");
$restaurant_count = $restaurant_res ? $restaurant_res->fetch_assoc()['count'] : 0;

$combined_food_count = $canteen_count + $restaurant_count;

echo json_encode([
    'success' => true,
    'counts' => [
        'caretakers' => $caretaker_count,
        'pharmacies' => $pharmacy_count,
        'food' => $combined_food_count
    ]
]);
?>

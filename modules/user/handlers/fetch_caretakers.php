<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../../includes/core/config.php';

header('Content-Type: application/json');

// Get actual session user_id
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$category = isset($_GET['category']) ? $_GET['category'] : 'All';

// Base Query with favorite + booking status check
$sql = "SELECT id, full_name, category, specialization, rating, experience_years, price_per_day, patients_helped, about_text, availability, working_hours, image_url,
        (SELECT COUNT(*) FROM caretaker_favorites cf WHERE cf.caretaker_id = caretakers.id AND cf.user_id = $user_id) as is_favorite,
        (SELECT COUNT(*) FROM caretaker_bookings cb WHERE cb.caretaker_id = caretakers.id AND cb.user_id = $user_id AND cb.status IN ('pending','confirmed')) as is_booked
        FROM caretakers";

if ($category !== 'All') {
    $sql .= " WHERE category = ?";
}

$stmt = $conn->prepare($sql);

if ($category !== 'All') {
    $stmt->bind_param("s", $category);
}

$stmt->execute();

$id = $full_name = $cat = $spec = $rating = $exp = $price = $patients = $about = $avail = $hours = $img = $is_fav = $is_booked = null;
$stmt->bind_result($id, $full_name, $cat, $spec, $rating, $exp, $price, $patients, $about, $avail, $hours, $img, $is_fav, $is_booked);

$caretakers = [];
while ($stmt->fetch()) {
    $caretakers[] = [
        'id'               => $id,
        'full_name'        => $full_name,
        'category'         => $cat,
        'specialization'   => $spec,
        'rating'           => $rating,
        'experience_years' => $exp,
        'price_per_day'    => $price,
        'patients_helped'  => $patients,
        'about_text'       => $about,
        'availability'     => $avail,
        'working_hours'    => $hours,
        'image_url'        => $img,
        'is_favorite'      => $is_fav > 0,
        'is_booked'        => $is_booked > 0,
    ];
}

echo json_encode(['status' => 'success', 'data' => $caretakers]);
$stmt->close();
$conn->close();
?>

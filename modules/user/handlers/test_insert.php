<?php
require_once '../../../includes/core/config.php';
error_reporting(E_ALL); ini_set('display_errors', 1);

try {
    $conn->begin_transaction();
    
    // 1. Insert into restaurant_orders
    $stmt = $conn->prepare("INSERT INTO restaurant_orders (restaurant_id, user_id, total_amount, status, delivery_address) VALUES (?, ?, ?, 'pending', ?)");
    $r_id = 1; $u_id = 3; $amt = 150.00; $add = "Test";
    $stmt->bind_param("iids", $r_id, $u_id, $amt, $add);
    if (!$stmt->execute()) {
        throw new Exception("ORDER INSERT FAILED: " . $stmt->error);
    }
    
    $order_id = $stmt->insert_id;
    echo "Inserted order_id: $order_id\n";
    
    // 2. Insert item
    $stmt_item = $conn->prepare("INSERT INTO restaurant_order_items (order_id, menu_item_id, quantity, price, item_name) VALUES (?, ?, ?, ?, ?)");
    $mi_id = 1; $qty = 1; $price = 150.00; $name = "Food";
    $stmt_item->bind_param("iiids", $order_id, $mi_id, $qty, $price, $name);
    
    if (!$stmt_item->execute()) {
        throw new Exception("ITEM INSERT FAILED: " . $stmt_item->error);
    }
    echo "Item inserted for order_id: $order_id\n";
    
    $conn->commit();
    echo "Transaction committed.";
} catch (Exception $e) {
    $conn->rollback();
    echo "Rolled back! Error: " . $e->getMessage();
}
?>

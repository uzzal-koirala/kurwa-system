<?php
require_once '../../../includes/core/config.php';

if (!isset($_SESSION['restaurant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$restaurant_id = $_SESSION['restaurant_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $name = trim($_POST['category_name'] ?? '');
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO restaurant_categories (restaurant_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $restaurant_id, $name);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add category']);
        }
        $stmt->close();
        exit;
    }

    if ($action === 'delete_category') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM restaurant_categories WHERE id = ? AND restaurant_id = ?");
        $stmt->bind_param("ii", $id, $restaurant_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
        }
        $stmt->close();
        exit;
    }

    if ($action === 'add_item' || $action === 'edit_item') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        if (empty($name) || $price <= 0 || $category_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please fill all required fields correctly.']);
            exit;
        }

        // Handle Image Upload
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../../assets/images/menu/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid('menu_') . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Public path for DB
                $image_url = '../../assets/images/menu/' . $new_filename;
            }
        }

        if ($action === 'add_item') {
            $stmt = $conn->prepare("INSERT INTO restaurant_menu (restaurant_id, name, description, price, category_id, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdisi", $restaurant_id, $name, $description, $price, $category_id, $image_url, $is_available);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Item added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add item']);
            }
            $stmt->close();
        } else {
            // Edit
            $id = intval($_POST['item_id'] ?? 0);
            if ($image_url) {
                // Update with new image
                $stmt = $conn->prepare("UPDATE restaurant_menu SET name=?, description=?, price=?, category_id=?, is_available=?, image_url=? WHERE id=? AND restaurant_id=?");
                $stmt->bind_param("ssdiisii", $name, $description, $price, $category_id, $is_available, $image_url, $id, $restaurant_id);
            } else {
                // Keep old image
                $stmt = $conn->prepare("UPDATE restaurant_menu SET name=?, description=?, price=?, category_id=?, is_available=? WHERE id=? AND restaurant_id=?");
                $stmt->bind_param("ssdiiii", $name, $description, $price, $category_id, $is_available, $id, $restaurant_id);
            }
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update item']);
            }
            $stmt->close();
        }
        exit;
    }

    if ($action === 'delete_item') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM restaurant_menu WHERE id = ? AND restaurant_id = ?");
        $stmt->bind_param("ii", $id, $restaurant_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Item deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete item']);
        }
        $stmt->close();
        exit;
    }

    if ($action === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        $status = intval($_POST['status'] ?? 0);
        $stmt = $conn->prepare("UPDATE restaurant_menu SET is_available = ? WHERE id = ? AND restaurant_id = ?");
        $stmt->bind_param("iii", $status, $id, $restaurant_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        $stmt->close();
        exit;
    }
}

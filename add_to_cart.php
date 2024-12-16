<?php
session_start();
require 'includes/dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize POST data
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if ($product_id > 0 && $quantity > 0) {
        $user_id = $_SESSION['user_id'];

        // Fetch product price
        $stmt = $conn->prepare("SELECT product_price FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($product_price);
        if ($stmt->fetch()) {
            $stmt->close();

            // Calculate subtotal price
            $subtotal_price = $product_price * $quantity;

            // Check if product is already in cart
            $stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Update existing cart item
                $stmt->bind_result($existing_quantity);
                $stmt->fetch();
                $new_quantity = $existing_quantity + $quantity;
                $new_subtotal = $product_price * $new_quantity;

                $stmt->close();

                $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = ?, subtotal_price = ? WHERE user_id = ? AND product_id = ?");
                $update_stmt->bind_param("idii", $new_quantity, $new_subtotal, $user_id, $product_id);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Insert new cart item
                $stmt->close();

                $insert_stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity, subtotal_price) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("iiid", $user_id, $product_id, $quantity, $subtotal_price);
                $insert_stmt->execute();
                $insert_stmt->close();
            }

            echo json_encode(['success' => true, 'message' => 'Product added to cart successfully.']);
            exit();
        } else {
            $stmt->close();
            // Product not found
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            exit();
        }
    } else {
        // Invalid input
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}
?>
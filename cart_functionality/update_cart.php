<?php
session_start();
require '../includes/dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cart_item_id']) && isset($_POST['quantity'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        $quantity = intval($_POST['quantity']);

        if ($quantity > 0) {
            // Update the cart item quantity and subtotal
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ?, subtotal_price = quantity * (SELECT product_price FROM products WHERE product_id = cart_items.product_id) WHERE cart_item_id = ?");
            $stmt->bind_param("ii", $quantity, $cart_item_id);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: ../cart.php");
        exit();
    }
}

header("Location: ../cart.php");
exit();
?>
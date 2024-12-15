<?php
session_start();
require '../includes/dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cart_item_id'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        $user_id = $_SESSION['user_id'];

        // Verify that the cart item belongs to the user
        $stmt = $conn->prepare("SELECT cart_item_id FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_item_id, $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            // Delete the cart item
            $delete_stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?");
            $delete_stmt->bind_param("ii", $cart_item_id, $user_id);
            if ($delete_stmt->execute()) {
                $delete_stmt->close();
                header("Location: ../cart.php");
                exit();
            } else {
                // Handle deletion failure
                $delete_stmt->close();
                header("Location: ../cart.php?error=Unable to remove the item");
                exit();
            }
        } else {
            $stmt->close();
            header("Location: ../cart.php?error=Item not found");
            exit();
        }
    } else {
        header("Location: ../cart.php?error=Invalid request");
        exit();
    }
} else {
    header("Location: ../cart.php");
    exit();
}
?>
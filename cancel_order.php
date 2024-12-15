<?php
session_start();
require 'includes/dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Update the order status to 'Cancelled'
    $sql = "UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Order has been cancelled successfully.";
    } else {
        $_SESSION['message'] = "Failed to cancel the order. Please try again.";
    }

    $stmt->close();
    $conn->close();

    header("Location: orders.php");
    exit();
} else {
    $_SESSION['message'] = "Invalid request.";
    header("Location: orders.php");
    exit();
}
?>
<?php
session_start();
require 'includes/dbconnect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $_POST['shipping_address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    // Fetch cart items again
    $sql = "
        SELECT ci.cart_item_id, ci.quantity, ci.subtotal_price, p.product_id
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.user_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cartResult = $stmt->get_result();

    $cartItems = [];
    $order_total = 0.00;

    while ($row = $cartResult->fetch_assoc()) {
        $cartItems[] = $row;
        $order_total += (float)$row['subtotal_price'];
    }
    $stmt->close();

    if (empty($cartItems)) {
        die("Cart is empty. No order placed.");
    }

    // Insert order
    // Generate a random order number reference
    $order_number = 'ORD-' . strtoupper(uniqid());

    // Insert into orders table
    $order_status = 'Pending';
    $sql = "INSERT INTO orders (user_id, order_status, order_number) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $order_status, $order_number);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    foreach ($cartItems as $item) {
        $quantity = $item['quantity'];
        $subtotal_price = $item['subtotal_price'];
        $total_price = $order_total; // total_price is often the full order price, or can be subtotal if you want per-item total
        $product_id = $item['product_id'];

        $sql = "INSERT INTO order_items (order_id, product_id, quantity, subtotal_price, total_price) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiidd", $order_id, $product_id, $quantity, $subtotal_price, $total_price);
        $stmt->execute();
        $stmt->close();
    }

    // Clear cart after order is placed
    $sql = "DELETE FROM cart_items WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    $conn->close();
} else {
    die("Invalid request method.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - PharmaEase</title>
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #f8f9fa;
        }
        .confirmation-wrapper {
            max-width: 600px;
            margin: 50px auto;
        }
        .order-number {
            font-size: 1.25rem;
            font-weight: 500;
            word-break: break-all;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container my-5 confirmation-wrapper">
    <div class="card p-4">
        <h1 class="mb-4 text-success">Order Confirmed</h1>
        <p>Your order has been successfully placed.</p>
        <p class="mb-4">Your order number is: <span class="order-number"><?php echo htmlspecialchars($order_number); ?></span></p>
        <p>Thank you for shopping with PharmaEase! We will deliver your order soon.</p>
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary w-100">Continue Shopping</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
require 'includes/dbconnect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch cart items for current user
$sql = "
    SELECT ci.cart_item_id, ci.quantity, ci.subtotal_price, p.product_name, p.product_price, p.product_id
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$total = 0.00;

while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $total += (float)$row['subtotal_price'];
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Checkout - PharmaEase</title>
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #f8f9fa;
        }
        .table > :not(:first-child) {
            border-top: 2px solid #dee2e6;
        }
        .product-name {
            font-weight: 500;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4">Pre-Checkout</h1>
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">Your cart is empty.</div>
    <?php else: ?>
        <div class="card p-4">
            <h2 class="mb-3">Order Review</h2>
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>₱<?php echo number_format($item['product_price'], 2); ?></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td>₱<?php echo number_format($item['subtotal_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="fw-bold">
                            <td colspan="3" class="text-end">Total:</td>
                            <td>₱<?php echo number_format($total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <form action="checkout_confirmation.php" method="POST">
                <div class="mb-3">
                    <label for="shippingAddress" class="form-label">Shipping Address</label>
                    <textarea class="form-control" id="shippingAddress" name="shipping_address" rows="3" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="paymentMethod" class="form-label">Payment Method</label>
                    <select class="form-select" id="paymentMethod" name="payment_method" required>
                        <option value="">Select a payment method</option>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="E-Wallet">E-Wallet</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Proceed to Checkout</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

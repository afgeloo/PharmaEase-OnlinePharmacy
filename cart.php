<?php
session_start();
require 'includes/dbconnect.php';

$cart = $_SESSION['cart'] ?? [];

// Sample display of cart items
// if (!empty($cart)) {
//     foreach ($cart as $productId => $quantity) {
//         echo "Product ID: $productId, Quantity: $quantity<br>";
//     }
// } else {
//     echo "Your cart is empty.";
// }

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$sql = "
    SELECT 
        ci.cart_item_id,
        p.product_id,
        p.product_name,
        p.product_description,
        p.product_price,
        pi.image_name_1,
        ci.quantity,
        ci.subtotal_price
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id
    WHERE ci.user_id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total cart value
$total_cart = 0;
$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $total_cart += $row['subtotal_price'];
    $cart_items[] = $row;
}
$stmt->close();

// Define tax rate
$tax_rate = 0.05;
$tax = $total_cart * $tax_rate;
$total = $total_cart + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart - PharmaEase</title>
    <!-- Bootstrap CSS -->
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container my-5">
        <h2 class="mb-4">Your Shopping Cart</h2>
        <?php if (count($cart_items) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Product Image</th>
                            <th scope="col">Product Name</th>
                            <th scope="col">Description</th>
                            <th scope="col" style="width: 120px;">Quantity</th>
                            <th scope="col" style="width: 120px;">Price</th>
                            <th scope="col" style="width: 150px;">Subtotal</th>
                            <th scope="col" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($item['image_name_1'] ?? 'path/to/default-image.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         class="img-thumbnail" 
                                         width="100">
                                </td>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['product_description']); ?></td>
                                <td>
                                    <input type="number" class="quantity-input" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           step="1" 
                                           min="1" 
                                           data-quantity-target>
                                </td>
                                <td>₱<?php echo number_format($item['product_price'], 2); ?></td>
                                <td>₱<?php echo number_format($item['subtotal_price'], 2); ?></td>
                                <td>
                                    <form method="POST" action="cart_functionality/remove_from_cart.php">
                                        <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($item['cart_item_id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Remove item from cart">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <div class="card" style="width: 25rem;">
                    <div class="card-body">
                        <h5 class="card-title">Cart Summary</h5>
                        <p class="card-text">
                            <strong>Grand Total:</strong> ₱<?php echo number_format($total_cart, 2); ?><br>
                            <strong>Tax (<?php echo $tax_rate * 100; ?>%):</strong> ₱<?php echo number_format($tax, 2); ?><br>
                            <strong>Final Total:</strong> ₱<?php echo number_format($total, 2); ?>
                        </p>
                        <a href="checkout.php" class="btn btn-success w-100">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                Your cart is empty. <a href="products.php" class="alert-link">Browse products</a> to add items to your cart.
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/cart.js"></script>
</body>
</html>

<?php
session_start();
require 'includes/dbconnect.php';

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
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total cart value
$total_cart = 0;
$cart_items = [];
while($row = $result->fetch_assoc()) {
    $total_cart += $row['subtotal_price'];
    $cart_items[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Cart - PharmaEase</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <?php foreach($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($item['image_name_1']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-thumbnail" width="100">
                                </td>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['product_description']); ?></td>
                                <td>
                                    <form method="POST" action="cart_functionality/update_cart.php" class="d-flex align-items-center">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                        <button type="button" class="btn btn-secondary btn-sm quantity-decrement">-</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control text-center mx-2" style="width: 50px;">
                                        <button type="button" class="btn btn-secondary btn-sm quantity-increment">+</button>
                                        <button type="submit" class="btn btn-primary btn-sm ms-2">Update</button>
                                    </form>
                                </td>
                                <td>₱<?php echo number_format($item['product_price'], 2); ?></td>
                                <td>₱<?php echo number_format($item['subtotal_price'], 2); ?></td>
                                <td>
                                    <form method="POST" action="cart_functionality/remove_from_cart.php">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
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
                            <strong>Subtotal:</strong> ₱<?php echo number_format($total_cart, 2); ?><br>
                            <strong>Tax (5%):</strong> ₱<?php echo number_format($total_cart * 0.05, 2); ?><br>
                            <strong>Total:</strong> ₱<?php echo number_format($total_cart * 1.05, 2); ?>
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
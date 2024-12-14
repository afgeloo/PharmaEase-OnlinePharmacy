<?php
// manage_orders.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Authentication Check
// if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
//     header("Location: ../login.php");
//     exit();
// }

// Database connection variables
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmaease_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['action'])) {
    $order_id = $_POST['order_id'];
    $action = $_POST['action'];
    $new_status = ($action == 'confirm') ? 'Confirmed' : 'Cancelled';

    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('si', $new_status, $order_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch orders from the database
$sql = "SELECT orders.id, registered_users.first_name, registered_users.last_name, orders.product_name, orders.quantity, orders.status
        FROM orders
        JOIN registered_users ON orders.user_id = registered_users.id
        ORDER BY orders.id DESC";
$orderResult = $conn->query($sql);
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Admin - Manage Orders</title>
    <!-- Link to Homepage CSS for common styles -->
    <link rel="stylesheet" type="text/css" href="/PharmaEase/PharmaEase-Final/components/homepage/homepage.css?v=1.0">
    <!-- Link to Admin-Specific CSS -->
    <link rel="stylesheet" type="text/css" href="/PharmaEase/PharmaEase-Final/components/Admin/admin.css?v=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="/PharmaEase/PharmaEase-Final/assets/PharmaEaseLogo.png">
</head>
<body>
    <div class="container">
        <!-- Main Navbar -->
        <header>
            <img src="/PharmaEase/PharmaEase-Final/assets/PharmaEaseFullLight.png" alt="PharmaEase Logo" class="logo-img">
            <nav>
                <a href="/PharmaEase/PharmaEase-Final/components/homepage/homepage.php">Home</a>
                <a href="/PharmaEase/PharmaEase-Final/components/cart/cart.php">Cart</a>
                <a href="/PharmaEase/PharmaEase-Final/components/checkout/checkout.php">Checkout</a>
                <a href="#">My Account</a>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1): ?>
                    <a href="manage_orders.php">Manage Orders</a>
                    <a href="manage_products.php">Manage Products</a>
                <?php endif; ?>
            </nav>
        </header>
        <div class="navlist">
            <div>
                <a href="allproducts.php">All Products</a>
                <a href="medicines.php">Prescription Medicines</a>
                <a href="overthecounter.php">Over-the-Counter</a>
                <a href="vitsandsupps.php">Vitamins and Supplements</a>
                <a href="personalcare.php">Personal Care</a>
                <a href="medsupps.php">Medicinal Supplies</a>
                <a href="babycare.php">Baby Care</a>
                <a href="sexualwellness.php">Sexual Wellness</a>
            </div>
            <div class="search">
                <form action="#">
                    <input type="text" placeholder="Search for Products & Brands" name="search">
                </form>
            </div>
        </div>
        <!-- Orders Table -->
        <div class="orders-table admin-container">
            <h2 class="admin-header">Manage Orders</h2>
            <?php if ($orderResult->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $orderResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row["id"]; ?></td>
                                <td><?php echo $row["first_name"] . " " . $row["last_name"]; ?></td>
                                <td><?php echo $row["product_name"]; ?></td>
                                <td><?php echo $row["quantity"]; ?></td>
                                <td><?php echo $row["status"]; ?></td>
                                <td>
                                    <?php if ($row["status"] == 'Pending'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $row["id"]; ?>">
                                            <button type="submit" name="action" value="confirm" class="btn confirm-btn">Confirm</button>
                                            <button type="submit" name="action" value="cancel" class="btn cancel-btn">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        <?php echo $row["status"]; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
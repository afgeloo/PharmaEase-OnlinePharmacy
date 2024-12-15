<?php
session_start();
require 'includes/dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user
$sql = "SELECT order_id, order_status, order_date, delivery_date FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();

$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track My Orders - PharmaEase</title>
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #f8f9fa;
        }
        .order-card {
            margin-bottom: 15px;
        }
        .order-header {
            cursor: pointer;
        }
        .order-id {
            font-weight: 600;
        }
        .no-orders {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            padding: 30px;
            text-align: center;
        }
        .no-orders p {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4">Track My Orders</h1>
    <?php if (empty($orders)): ?>
    <div class="no-orders">
        <h2 class="mb-3">No Orders Yet</h2>
        <p>Browse our products and place your first order!</p>
    </div>
    <?php else: ?>
    <div class="accordion" id="ordersAccordion">
        <?php foreach ($orders as $index => $order): ?>
        <?php
            $order_id = $order['order_id'];
            // Fetch order items
            require 'includes/dbconnect.php';
            $sql = "
                SELECT oi.quantity, oi.subtotal_price, oi.total_price, p.product_name, p.product_price
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $itemsResult = $stmt->get_result();
            $orderItems = [];
            $orderTotal = 0.00;
            while ($itemRow = $itemsResult->fetch_assoc()) {
                $orderItems[] = $itemRow;
                $orderTotal += (float)$itemRow['subtotal_price'];
            }
            $stmt->close();
            $conn->close();

            // Calculate tax and total with tax
            $tax_rate = 0.05;
            $tax = $orderTotal * $tax_rate;
            $total_with_tax = $orderTotal + $tax;
        ?>
        <div class="card order-card">
            <div class="card-header" id="heading<?php echo $index; ?>">
                <button class="btn btn-link d-flex justify-content-between align-items-center w-100 text-decoration-none order-header" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse<?php echo $index; ?>">
                    <div class="text-start">
                        <span class="order-id">Order #<?php echo htmlspecialchars($order_id); ?></span> 
                        <span class="badge bg-info text-dark ms-2"><?php echo htmlspecialchars($order['order_status']); ?></span>
                    </div>
                    <div class="text-end">
                        <small>Ordered on: <?php echo date('F j, Y, g:i A', strtotime($order['order_date'])); ?></small>
                    </div>
                </button>
            </div>

            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#ordersAccordion">
                <div class="card-body">
                    <p><strong>Delivery Date:</strong> <?php echo date('F j, Y, g:i A', strtotime($order['delivery_date'])); ?></p>
                    <?php if (!empty($orderItems)): ?>
                    <div class="table-responsive">
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
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td>₱<?php echo number_format($item['product_price'], 2); ?></td>
                                    <td><?php echo (int)$item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($item['subtotal_price'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Grand Total:</td>
                                    <td>₱<?php echo number_format($orderTotal, 2); ?></td>
                                </tr>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Tax (<?php echo $tax_rate * 100; ?>%):</td>
                                    <td>₱<?php echo number_format($tax, 2); ?></td>
                                </tr>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Final Total:</td>
                                    <td>₱<?php echo number_format($total_with_tax, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p>No items found for this order.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

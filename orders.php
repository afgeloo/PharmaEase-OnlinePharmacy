<?php
session_start();
require 'includes/dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user
$sql = "SELECT order_id, order_number, order_status, order_date FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();

$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $order_date = new DateTime($row['order_date']);
    
    // Calculate delivery date excluding weekends
    $delivery_date = new DateTime();
    $days_to_add = 3; // Number of business days to add
    
    while ($days_to_add > 0) {
        $delivery_date->modify('+1 day');
        if ($delivery_date->format('N') < 6) { // 1 (Monday) to 5 (Friday) are business days
            $days_to_add--;
        }
    }
    
    $row['delivery_date'] = $delivery_date->format('F j, Y, g:i A');
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
    <link rel="stylesheet" href="css/home.css?v=<?php echo time(); ?>">
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #FFF9F0;
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
        .delivery-date {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: .375rem 0 0 0;
            margin-bottom: 0;
            margin-top: 0;
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
            $order_number = $order['order_number'];
            // Fetch order items
            require 'includes/dbconnect.php';
            $sql = "
                SELECT oi.quantity, oi.subtotal_price, p.product_name, p.product_price
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

            // Add shipping fee
            $shipping_fee = 50.00;
            $total_with_shipping = $orderTotal + $shipping_fee;
        ?>
        <div class="card order-card">
            <div class="card-header" id="heading<?php echo $index; ?>">
                <button class="btn btn-link d-flex justify-content-between align-items-center w-100 text-decoration-none order-header" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse<?php echo $index; ?>">
                    <div class="text-start">
                        <span class="order-id"><?php echo htmlspecialchars($order_number); ?></span> 
                        <span class="badge bg-info text-dark ms-2"><?php echo htmlspecialchars($order['order_status']); ?></span>
                    </div>
                    <div class="text-end">
                        <small>Ordered on: <?php echo date('F j, Y, g:i A', strtotime($order['order_date'])); ?></small>
                        <?php if ($order['order_status'] === 'Pending'): ?>
                       
                        <?php endif; ?>
                    </div>
                </button>
            </div>

            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#ordersAccordion">
                <div class="card-body">
                    <p class="delivery-date"><strong>Delivery Date:</strong> <?php echo $order['delivery_date']; ?></p>
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
                                    <td colspan="3" class="text-end">Shipping Fee:</td>
                                    <td>₱<?php echo number_format($shipping_fee, 2); ?></td>
                                </tr>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">Total:</td>
                                    <td>₱<?php echo number_format($total_with_shipping, 2); ?></td>
                                </tr>
                
                            </tbody>
                        </table>
                    </div>
                    <form method="post" action="cancel_order.php" class="d-inline">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm ms-2">Cancel Order</button>
                        </form>
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

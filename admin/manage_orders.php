<?php
session_start();
require '../includes/dbconnect.php';

// Ensure admin is logged in (example check; adjust as needed)
// if (!isset($_SESSION['admin_logged_in'])) {
//     die("Admin not logged in.");
// }

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $order_id = (int)($_POST['order_id'] ?? 0);

    if ($action === 'confirm' && $order_id > 0) {
        // Confirm order and set delivery date (3 days after today)
        $confirmation_date = new DateTime(); // Today
        $delivery_date = calculate_delivery_date($confirmation_date, 3); // custom function to calculate delivery
        $new_status = "Confirmed";

        $sql = "UPDATE orders SET order_status=?, delivery_date=? WHERE order_id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $formatted_delivery_date = $delivery_date->format('Y-m-d H:i:s');
            $stmt->bind_param("ssi", $new_status, $formatted_delivery_date, $order_id);
            if ($stmt->execute()) {
                // echo "Order #$order_id confirmed with delivery date $formatted_delivery_date.";
            } else {
                echo "Error executing statement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }

    if ($action === 'reject' && $order_id > 0) {
        // Reject order
        $new_status = "Rejected";
        // Optionally set delivery_date to NULL or leave as is
        $sql = "UPDATE orders SET order_status=?, delivery_date=order_date WHERE order_id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("si", $new_status, $order_id);
            if ($stmt->execute()) {
                // echo "Order #$order_id rejected.";
            } else {
                echo "Error executing statement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}

// Fetch all orders
$sql = "
SELECT o.order_id, o.user_id, o.order_status, o.order_date, o.delivery_date, 
       r.first_name, r.last_name, r.email
FROM orders o
JOIN registered_users r ON o.user_id = r.user_id
ORDER BY o.order_date DESC
";
$result = $conn->query($sql);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$conn->close();

/**
 * Calculate a delivery date by adding $days to $start_date.
 */

function calculate_delivery_date(DateTime $start_date, $days) {
    $date = clone $start_date;
    $added_days = 0;

    while ($added_days < $days) {
        $date->modify('+1 day');
        if ($date->format('N') < 6) { // 1 (Monday) to 5 (Friday) are weekdays
            $added_days++;
        }
    }

    return $date;
}

// Calculate delivery date excluding weekends
$delivery_date = new DateTime();
$days_to_add = 3;
while ($days_to_add > 0) {
    $delivery_date->modify('+2 day');
    if ($delivery_date->format('N') < 6) { // 1 (Monday) to 5 (Friday)
        $days_to_add--;
    }
}
$expected_delivery = $delivery_date->format('F j, Y');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - PharmaEase Admin</title>
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="Admin.css">
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #f8f9fa;
        }
        .order-id {
            font-weight: 500;
        }
        .badge-status {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header_admin.php'; ?>

<div class="container my-5">
    <h1 class="mb-4">Manage Orders</h1>
    <?php if (empty($orders)): ?>
        <div class="alert alert-info">No orders found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Delivery Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <?php
                    $status = htmlspecialchars($order['order_status']);
                    $statusBadgeClass = 'bg-secondary text-light';
                    if ($status === 'Pending') {
                        $statusBadgeClass = 'bg-warning text-dark';
                    } elseif ($status === 'Confirmed') {
                        $statusBadgeClass = 'bg-success';
                    } elseif ($status === 'Rejected') {
                        $statusBadgeClass = 'bg-danger';
                    }
                    ?>
                    <tr>
                        <td class="order-id">#<?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $statusBadgeClass; ?> badge-status">
                                <?php echo $status; ?>
                            </span>
                        </td>
                        <td><?php echo date('F j, Y, g:i A', strtotime($order['order_date'])); ?></td>
                        <td><?php echo date('F j, Y, g:i A', strtotime($order['delivery_date'])); ?></td>
                        <td>
                            <?php if ($status === 'Pending'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <input type="hidden" name="action" value="confirm">
                                    <button type="submit" class="btn btn-sm btn-primary">Confirm</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody> 
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
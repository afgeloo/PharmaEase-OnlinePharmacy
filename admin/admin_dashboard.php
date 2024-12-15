<?php
session_start();
require '../includes/dbconnect.php';

// Ensure admin is logged in (example check; adjust as needed)
if (
    !isset($_SESSION['user']) ||
    $_SESSION['user'] !== 'admin' ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    // Optionally, redirect to login page instead of dying
    header("Location: ../index.php");
    exit("Access denied. Admin not logged in.");
}


// Fetch some summary data for the dashboard
// Total products
$productCountSql = "SELECT COUNT(*) AS total_products FROM products";
$productCountResult = $conn->query($productCountSql);
$totalProducts = ($row = $productCountResult->fetch_assoc()) ? $row['total_products'] : 0;

// Total orders
$orderCountSql = "SELECT COUNT(*) AS total_orders FROM orders";
$orderCountResult = $conn->query($orderCountSql);
$totalOrders = ($row = $orderCountResult->fetch_assoc()) ? $row['total_orders'] : 0;

// Pending orders
$pendingCountSql = "SELECT COUNT(*) AS pending_orders FROM orders WHERE order_status = 'Pending'";
$pendingCountResult = $conn->query($pendingCountSql);
$pendingOrders = ($row = $pendingCountResult->fetch_assoc()) ? $row['pending_orders'] : 0;

// Confirmed orders
$confirmedCountSql = "SELECT COUNT(*) AS confirmed_orders FROM orders WHERE order_status = 'Confirmed'";
$confirmedCountResult = $conn->query($confirmedCountSql);
$confirmedOrders = ($row = $confirmedCountResult->fetch_assoc()) ? $row['confirmed_orders'] : 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PharmaEase</title>
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="Admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #f8f9fa;
        }
        .card-stats {
            border-left: 4px solid #88c273;
            border-radius: 0.375rem;
        }
        .card-stats h5 {
            margin-bottom: 0;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #88c273;
            border: none;
        }
    </style>
</head>
<body>
<?php include '../includes/header_admin.php'; ?>

<div class="container my-5">
    <h1 class="mb-4">Admin Dashboard</h1>
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card p-3 card-stats">
                <h5>Total Products</h5>
                <span class="fs-3 fw-bold"><?php echo $totalProducts; ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 card-stats">
                <h5>Total Orders</h5>
                <span class="fs-3 fw-bold"><?php echo $totalOrders; ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 card-stats">
                <h5>Pending Orders</h5>
                <span class="fs-3 fw-bold text-warning"><?php echo $pendingOrders; ?></span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 card-stats">
                <h5>Confirmed Orders</h5>
                <span class="fs-3 fw-bold text-success"><?php echo $confirmedOrders; ?></span>
            </div>
        </div>
    </div>

    <h2 class="section-title">Quick Actions</h2>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h3 class="card-title mb-3">Manage Products</h3>
                    <p class="card-text flex-grow-1">Add new products, edit existing ones, or remove products from the store inventory.</p>
                    <a href="manage_products.php" class="btn btn-primary mt-3">Go to Manage Products</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h3 class="card-title mb-3">Manage Orders</h3>
                    <p class="card-text flex-grow-1">View all customer orders, confirm pending orders, or reject invalid ones, and keep track of deliveries.</p>
                    <a href="manage_orders.php" class="btn btn-primary mt-3">Go to Manage Orders</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

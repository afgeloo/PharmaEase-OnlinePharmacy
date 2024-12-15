<?php
// filepath: /c:/xampp/htdocs/PharmaEase/productlist.php
session_start();

require 'includes/dbconnect.php';

// Fetch categories for the filter
$categorySql = "SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC";
$categoryResult = $conn->query($categorySql);

// Debugging: Check if the query was successful
if (!$categoryResult) {
    die("Error fetching categories: " . $conn->error);
}

// Get selected category from GET parameters
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Fetch products based on selected category
if ($selected_category > 0) {
    $sql = "SELECT p.product_id, p.product_name, p.product_description, p.product_price, p.store, c.category_name, pi.image_name_1
            FROM products p
            JOIN product_categories c ON p.category_id = c.category_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id
            WHERE p.category_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $selected_category);
} else {
    // If no category selected, fetch all products
    $sql = "SELECT p.product_id, p.product_name, p.product_description, p.product_price, p.store, c.category_name, pi.image_name_1
            FROM products p
            JOIN product_categories c ON p.category_id = c.category_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/productlist.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/home.css">
    <title>Products List - PharmaEase</title>
</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>

        <h2>Products List</h2>

        <div class="product-container">
            <?php if($result->num_rows > 0): ?>
                <?php while($product = $result->fetch_assoc()): 
                    $images = [];
                    if (!empty($product['image_name_1'])) {
                        $images[] = htmlspecialchars($product['image_name_1']);
                    }
                    ?>
                    <div class="product">
                        <img src="<?php echo $images[0] ?? 'https://placehold.co/600x600'; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['product_description']); ?></p>
                        <p>Price: ₱<?php echo number_format($product['product_price'], 2); ?></p>
                        <p>Store: <?php echo htmlspecialchars($product['store']); ?></p>
                        <a href="productview.php?id=<?php echo $product['product_id']; ?>">View Details</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
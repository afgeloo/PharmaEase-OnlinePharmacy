<?php
// filepath: /c:/xampp/htdocs/PharmaEase/admin/manage_products.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/dbconnect.php';

// Define the upload directory
$uploadDir = '../assets/ProductPics/'; // Adjust the path as needed

// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle product addition in this file if needed (no changes necessary)
// Deletion logic remains the same
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $productId = intval($_POST['product_id']);
    // Delete images first
    $imgStmt = $conn->prepare("SELECT image_name_1, image_name_2, image_name_3 FROM product_images WHERE product_id = ?");
    $imgStmt->bind_param("i", $productId);
    $imgStmt->execute();
    $imgResult = $imgStmt->get_result();
    if ($imgResult->num_rows > 0) {
        $imgRow = $imgResult->fetch_assoc();
        foreach ($imgRow as $imgPath) {
            if (!empty($imgPath)) {
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imgPath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }
    }
    $imgStmt->close();

    $stmt = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    if ($stmt->execute()) {
        $successMessage = "Product ID #$productId has been deleted.";
    } else {
        $errorMessage = "Error deleting product: " . $conn->error;
    }
    $stmt->close();
}

// Fetch Products
$productSql = "SELECT 
                p.product_id, 
                p.product_name, 
                p.product_price, 
                c.category_name, 
                pi.image_name_1, 
                pi.image_name_2, 
                pi.image_name_3 
               FROM products p
               JOIN product_categories c ON p.category_id = c.category_id
               LEFT JOIN product_images pi ON p.product_id = pi.product_id
               ORDER BY p.product_id DESC";
$productResult = $conn->query($productSql);

// Fetch Categories for Dropdown
$categorySql = "SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC";
$categoryResult = $conn->query($categorySql);
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Admin - Manage Products</title>
    <link rel="stylesheet" type="text/css" href="../css/home.css">
    <link rel="stylesheet" type="text/css" href="Admin.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="/PharmaEase/PharmaEase-Final/assets/PharmaEaseLogo.png">
</head>
<body>
    <div class="container">
        <?php include '../includes/header_admin.php' ?>
        <div class="admin-container">
            <h2 class="admin-header">Manage Products</h2>
            <?php if (isset($successMessage)): ?>
                <p class="success-message"><?php echo $successMessage; ?></p>
            <?php endif; ?>
            <?php if (isset($errorMessage)): ?>
                <p class="error-message"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            <div class="admin-actions">
                <a href="add_product.php" class="btn confirm-btn">Add Product</a>
            </div>
            <?php if ($productResult->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Main Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($product = $productResult->fetch_assoc()): ?>
                        <?php
                            $productId = htmlspecialchars($product['product_id']);
                            $name = htmlspecialchars($product['product_name']);
                            $category = htmlspecialchars($product['category_name']);
                            $price = htmlspecialchars($product['product_price']);
                            $mainImage = !empty($product['image_name_1']) ? htmlspecialchars($product['image_name_1']) : 'https://placehold.co/100x100';
                        ?>
                        <tr>
                            <td><?php echo $productId; ?></td>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $category; ?></td>
                            <td>₱<?php echo number_format($price, 2); ?></td>
                            <td>
                                <img src="<?php echo $mainImage; ?>" alt="Main Image" width="100" height="100">
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                    <button type="submit" name="delete_product" class="btn cancel-btn">Delete</button>
                                </form>
                                <a href="edit_product.php?id=<?php echo $productId; ?>" class="btn edit-btn">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>

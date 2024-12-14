<?php
// filepath: /c:/xampp/htdocs/PharmaEase/admin/manage_products.php
// manage_products.php
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

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    // Retrieve and sanitize form inputs
    $name = trim($_POST['product_name']);
    $category = intval($_POST['category_id']);
    $label = trim($_POST['product_label']);
    $description = trim($_POST['product_description']);
    $price = floatval($_POST['product_price']);
    $store = trim($_POST['store']);
    
    // Initialize image path variables
    $image1Path = '';
    $image2Path = '';
    $image3Path = '';

    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    // Function to handle file upload
    function handleFileUpload($file, $uploadDir, $allowedTypes) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        // Generate a unique file name
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid() . '.' . $ext;

        // Move the file to the upload directory
        $destination = $uploadDir . $uniqueName;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Return the relative path to store in the database
            return '/PharmaEase/assets/ProductPics/' . $uniqueName;
        }

        return false;
    }

    // Handle Image 1 (Main Image)
    $image1Path = handleFileUpload($_FILES['image_name_1'], $uploadDir, $allowedTypes);
    if ($image1Path === false) {
        $errorMessage = "Error uploading Image 1. Please ensure it's a valid image file.";
    }

    // Handle Image 2 (Optional)
    if (isset($_FILES['image_name_2'])) {
        $image2Path = handleFileUpload($_FILES['image_name_2'], $uploadDir, $allowedTypes);
        if ($image2Path === false && $_FILES['image_name_2']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorMessage = "Error uploading Image 2. Please ensure it's a valid image file.";
        }
    }

    // Handle Image 3 (Optional)
    if (isset($_FILES['image_name_3'])) {
        $image3Path = handleFileUpload($_FILES['image_name_3'], $uploadDir, $allowedTypes);
        if ($image3Path === false && $_FILES['image_name_3']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorMessage = "Error uploading Image 3. Please ensure it's a valid image file.";
        }
    }

    // If there were no upload errors, proceed to insert into the database
    if (!isset($errorMessage)) {
        // Insert into products table
        $stmt = $conn->prepare("INSERT INTO products (product_name, category_id, product_label, product_description, product_price, store) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissds", $name, $category, $label, $description, $price, $store);
        
        if ($stmt->execute()) {
            $newProductId = $stmt->insert_id;
            $stmt->close();
            
            // Insert into product_images table
            $stmt = $conn->prepare("INSERT INTO product_images (product_id, image_name_1, image_name_2, image_name_3) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $newProductId, $image1Path, $image2Path, $image3Path);
            
            if ($stmt->execute()) {
                $successMessage = "Product '{$name}' has been added successfully.";
            } else {
                $errorMessage = "Error adding product images: " . $conn->error;
            }
            $stmt->close();
        } else {
            $errorMessage = "Error adding product: " . $conn->error;
        }
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    $productId = intval($_POST['product_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    if ($stmt->execute()) {
        $successMessage = "Product ID #$productId has been deleted.";
    } else {
        $errorMessage = "Error deleting product: " . $conn->error;
    }
    $stmt->close();
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $productId = intval($_POST['product_id']);
    $name = trim($_POST['product_name']);
    $category = intval($_POST['category']);
    $price = floatval($_POST['price']);

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, category_id = ?, product_price = ? WHERE product_id = ?");
    $stmt->bind_param("sddi", $name, $category, $price, $productId);
    if ($stmt->execute()) {
        $successMessage = "Product ID #$productId has been updated.";
    } else {
        $errorMessage = "Error updating product: " . $conn->error;
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
    <!-- Link to Homepage CSS for common styles -->
    <link rel="stylesheet" type="text/css" href="/PharmaEase/PharmaEase-Final/components/homepage/homepage.css?v=1.0">
    <!-- Link to Admin-Specific CSS -->
    <link rel="stylesheet" type="text/css" href="Admin.css?v=1.0">
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
                <a href="homepage.php">Home</a>
                <a href="../cart/cart.php">Cart</a>
                <a href="../checkout/checkout.php">Checkout</a>
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
        <!-- Products Management Section -->
        <div class="admin-container">
            <h2 class="admin-header">Manage Products</h2>
            <?php if (isset($successMessage)): ?>
                <p class="success-message"><?php echo $successMessage; ?></p>
            <?php endif; ?>
            <?php if (isset($errorMessage)): ?>
                <p class="error-message"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
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
                            
                            // Collect image paths
                            $images = [];
                            if (!empty($product['image_name_1'])) {
                                $images[] = $product['image_name_1'];
                            }
                            if (!empty($product['image_name_2'])) {
                                $images[] = $product['image_name_2'];
                            }
                            if (!empty($product['image_name_3'])) {
                                $images[] = $product['image_name_3'];
                            }
                            
                            // Main Image is image_name_1
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
                                <button class="btn edit-btn" onclick="openEditForm(<?php echo $productId; ?>, '<?php echo addslashes($name); ?>', '<?php echo addslashes($category); ?>', <?php echo $price; ?>)">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
        <!-- Add Product Form -->
        <div class="admin-container">
            <h2 class="admin-header">Add New Product</h2>
            <?php if (isset($successMessage)): ?>
                <p class="success-message"><?php echo $successMessage; ?></p>
            <?php endif; ?>
            <?php if (isset($errorMessage)): ?>
                <p class="error-message"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            <form method="POST" class="admin-form" enctype="multipart/form-data">
                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" required>

                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php if ($categoryResult->num_rows > 0): ?>
                        <?php while($category = $categoryResult->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No Categories Available</option>
                    <?php endif; ?>
                </select>

                <label for="product_label">Product Label:</label>
                <input type="text" id="product_label" name="product_label" required>

                <label for="product_description">Product Description:</label>
                <textarea id="product_description" name="product_description" rows="4" required></textarea>

                <label for="product_price">Product Price:</label>
                <input type="number" step="0.01" id="product_price" name="product_price" required>

                <label for="store">Store:</label>
                <input type="text" id="store" name="store" required>

                <h3>Product Images</h3>
                <label for="image_name_1">Image 1 (Main Image):</label>
                <input type="file" id="image_name_1" name="image_name_1" accept="image/*" required>

                <label for="image_name_2">Image 2 (Optional):</label>
                <input type="file" id="image_name_2" name="image_name_2" accept="image/*">

                <label for="image_name_3">Image 3 (Optional):</label>
                <input type="file" id="image_name_3" name="image_name_3" accept="image/*">

                <button type="submit" name="add_product" class="btn confirm-btn">Add Product</button>
            </form>
        </div>
        <!-- Edit Product Form -->
        <div id="editForm" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditForm()">&times;</span>
                <h2>Edit Product</h2>
                <form method="POST">
                    <input type="hidden" id="edit_product_id" name="product_id">
                    <label for="edit_product_name">Product Name:</label>
                    <input type="text" id="edit_product_name" name="product_name" required>
                    
                    <label for="edit_category">Category:</label>
                    <select id="edit_category" name="category" required>
                        <option value="">Select Category</option>
                        <?php
                        // Fetch categories again for the edit form
                        $editCategoryResult = $conn->query($categorySql);
                        if ($editCategoryResult->num_rows > 0):
                            while($category = $editCategoryResult->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                        <?php endwhile; 
                        else: ?>
                            <option value="">No Categories Available</option>
                        <?php endif; ?>
                    </select>
                    
                    <label for="edit_price">Price:</label>
                    <input type="number" step="0.01" id="edit_price" name="price" required>
                    <!-- Removed stock and sku fields -->
                    <button type="submit" name="edit_product" class="btn confirm-btn">Update Product</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function openEditForm(id, name, category, price) {
            document.getElementById('edit_product_id').value = id;
            document.getElementById('edit_product_name').value = name;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_price').value = price;
            document.getElementById('editForm').style.display = 'block';
        }
    
        function closeEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
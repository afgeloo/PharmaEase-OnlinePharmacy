<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/dbconnect.php';

// Define the upload directory
$uploadDir = '../assets/ProductPics/'; // Adjust the path as needed

// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Fetch Categories for Dropdown with Error Handling
$categorySql = "SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC";
$categoryResult = $conn->query($categorySql);

if (!$categoryResult) {
    $errorMessage = "Error fetching categories: " . $conn->error;
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
    $imagePaths = [
        'image_name_1' => '',
        'image_name_2' => '',
        'image_name_3' => ''
    ];

    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    // Maximum file size (e.g., 2MB)
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    // Function to handle single file upload
    function handleFileUpload($file, $uploadDir, $allowedTypes, $maxFileSize) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > $maxFileSize) {
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

    // Check if files were uploaded
    if (isset($_FILES['product_images'])) {
        $files = $_FILES['product_images'];
        $fileCount = count($files['name']);
        $uploadedCount = 0;

        for ($i = 0; $i < $fileCount && $uploadedCount < 3; $i++) {
            // Skip if no file selected in this slot
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $uploadResult = handleFileUpload(
                [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ],
                $uploadDir,
                $allowedTypes,
                $maxFileSize
            );

            if ($uploadResult === false) {
                $errorMessage = "Error uploading Image " . ($uploadedCount + 1) . ". Please ensure it's a valid image file and does not exceed 2MB.";
                break;
            }

            // Assign the uploaded image path
            if (!empty($uploadResult)) {
                $imageKey = 'image_name_' . ($uploadedCount + 1);
                $imagePaths[$imageKey] = $uploadResult;
                $uploadedCount++;
            }
        }

        // Check if at least the main image was uploaded
        if ($uploadedCount < 1 && !isset($errorMessage)) {
            $errorMessage = "Please upload at least one image for the product.";
        }
    } else {
        $errorMessage = "No images were uploaded.";
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
            $stmt->bind_param("isss", $newProductId, $imagePaths['image_name_1'], $imagePaths['image_name_2'], $imagePaths['image_name_3']);
            
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

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Add Product</title>
    <!-- Link to Homepage CSS for common styles -->
    <link rel="stylesheet" type="text/css" href="../css/home.css">
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
        <?php include '../includes/header_admin.php' ?>
        <!-- Add Product Section -->
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
                    <?php if (isset($categoryResult) && $categoryResult->num_rows > 0): ?>
                        <?php while($category = $categoryResult->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?php if (isset($errorMessage) && strpos($errorMessage, 'fetching categories') !== false): ?>
                            <option value=""><?php echo htmlspecialchars($errorMessage); ?></option>
                        <?php else: ?>
                            <option value="">No Categories Available</option>
                        <?php endif; ?>
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
                <label for="product_images">Upload Images (First image will be the main image):</label>
                <input type="file" id="product_images" name="product_images[]" accept="image/*" multiple required>

                <button type="submit" name="add_product" class="btn confirm-btn">Add Product</button>
            </form>
        </div>
    </div>
</body>
</html>
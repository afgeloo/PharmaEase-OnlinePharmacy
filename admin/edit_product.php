<?php
// filepath: /c:/xampp/htdocs/PharmaEase/admin/edit_product.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/dbconnect.php';

// Define the upload directory
$uploadDir = '../assets/ProductPics/'; 

// Ensure the upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Fetch the product info
if (!isset($_GET['id'])) {
    echo "Product ID not provided.";
    exit;
}
$productId = intval($_GET['id']);

// Fetch product details
$stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.category_id, p.product_label, p.product_description, p.product_price, p.store, pi.image_name_1, pi.image_name_2, pi.image_name_3
                        FROM products p
                        LEFT JOIN product_images pi ON p.product_id = pi.product_id
                        WHERE p.product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$productResult = $stmt->get_result();
if ($productResult->num_rows < 1) {
    echo "Product not found.";
    exit;
}
$product = $productResult->fetch_assoc();
$stmt->close();

// Fetch categories for dropdown
$categorySql = "SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC";
$categoryResult = $conn->query($categorySql);

// Allowed file types
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxFileSize = 2 * 1024 * 1024; // 2MB

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

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = uniqid() . '.' . $ext;
    $destination = $uploadDir . $uniqueName;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return '/PharmaEase/assets/ProductPics/' . $uniqueName;
    }

    return false;
}

// Handle product edit submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $name = trim($_POST['product_name']);
    $categoryId = intval($_POST['category_id']);
    $label = trim($_POST['product_label']);
    $description = trim($_POST['product_description']);
    $price = floatval($_POST['product_price']);
    $store = trim($_POST['store']);

    // Retrieve current images
    $currentImages = [
        'image_name_1' => $product['image_name_1'],
        'image_name_2' => $product['image_name_2'],
        'image_name_3' => $product['image_name_3']
    ];

    // Handle image uploads
    $imagePaths = [
        'image_name_1' => $currentImages['image_name_1'],
        'image_name_2' => $currentImages['image_name_2'],
        'image_name_3' => $currentImages['image_name_3']
    ];

    if (isset($_FILES['product_images'])) {
        $files = $_FILES['product_images'];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount && $i < 3; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_NO_FILE) {
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
                    $errorMessage = "Error uploading an image. Ensure it's valid and under 2MB.";
                    break;
                }
                if (!empty($uploadResult)) {
                    // If a new image is uploaded, optionally remove the old image from the server
                    if (!empty($imagePaths['image_name_'.($i+1)])) {
                        $oldImageFullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePaths['image_name_'.($i+1)];
                        if (file_exists($oldImageFullPath)) {
                            unlink($oldImageFullPath);
                        }
                    }
                    $imagePaths['image_name_'.($i+1)] = $uploadResult;
                }
            }
        }
    }

    // If no errors, update the product info
    if (!isset($errorMessage)) {
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, category_id = ?, product_label = ?, product_description = ?, product_price = ?, store = ? WHERE product_id = ?");
        $stmt->bind_param("sissdsi", $name, $categoryId, $label, $description, $price, $store, $productId);
        if ($stmt->execute()) {
            $stmt->close();
            // Update images
            // Check if product_images row exists
            $checkStmt = $conn->prepare("SELECT product_id FROM product_images WHERE product_id = ?");
            $checkStmt->bind_param("i", $productId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkStmt->close();

            if ($checkResult->num_rows > 0) {
                $imgStmt = $conn->prepare("UPDATE product_images SET image_name_1 = ?, image_name_2 = ?, image_name_3 = ? WHERE product_id = ?");
                $imgStmt->bind_param("sssi", $imagePaths['image_name_1'], $imagePaths['image_name_2'], $imagePaths['image_name_3'], $productId);
                $imgStmt->execute();
                $imgStmt->close();
            } else {
                $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_name_1, image_name_2, image_name_3) VALUES (?, ?, ?, ?)");
                $imgStmt->bind_param("isss", $productId, $imagePaths['image_name_1'], $imagePaths['image_name_2'], $imagePaths['image_name_3']);
                $imgStmt->execute();
                $imgStmt->close();
            }

            $successMessage = "Product '{$name}' has been updated successfully.";
            // Refresh product data
            $stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.category_id, p.product_label, p.product_description, p.product_price, p.store, pi.image_name_1, pi.image_name_2, pi.image_name_3
                                    FROM products p
                                    LEFT JOIN product_images pi ON p.product_id = pi.product_id
                                    WHERE p.product_id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $productResult = $stmt->get_result();
            $product = $productResult->fetch_assoc();
            $stmt->close();
        } else {
            $errorMessage = "Error updating product: " . $conn->error;
        }
    }
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" type="text/css" href="../css/home.css">
    <link rel="stylesheet" type="text/css" href="Admin.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="/PharmaEase/PharmaEase-Final/assets/PharmaEaseLogo.png">
</head>
<body>
    <div class="container">
        <?php include '../includes/header_admin.php' ?>
        <div class="admin-container">
            <h2 class="admin-header">Edit Product: <?php echo htmlspecialchars($product['product_name']); ?></h2>
            <?php if (isset($successMessage)): ?>
                <p class="success-message"><?php echo $successMessage; ?></p>
            <?php endif; ?>
            <?php if (isset($errorMessage)): ?>
                <p class="error-message"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            <form method="POST" class="admin-form" enctype="multipart/form-data">
                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" required value="<?php echo htmlspecialchars($product['product_name']); ?>">

                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php if ($categoryResult->num_rows > 0): ?>
                        <?php while($cat = $categoryResult->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($cat['category_id']); ?>" <?php if ($cat['category_id'] == $product['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No Categories Available</option>
                    <?php endif; ?>
                </select>

                <label for="product_label">Product Label:</label>
                <input type="text" id="product_label" name="product_label" required value="<?php echo htmlspecialchars($product['product_label']); ?>">

                <label for="product_description">Product Description:</label>
                <textarea id="product_description" name="product_description" rows="4" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>

                <label for="product_price">Product Price:</label>
                <input type="number" step="0.01" id="product_price" name="product_price" required value="<?php echo htmlspecialchars($product['product_price']); ?>">

                <label for="store">Store:</label>
                <input type="text" id="store" name="store" required value="<?php echo htmlspecialchars($product['store']); ?>">

                <h3>Product Images</h3>
                <p>Current main image:</p>
                <?php if (!empty($product['image_name_1'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image_name_1']); ?>" width="100" height="100" alt="Main Image">
                <?php else: ?>
                    <img src="https://placehold.co/100x100" width="100" height="100" alt="No Main Image">
                <?php endif; ?>

                <p>Upload new images to replace existing ones (First image uploaded will be main image):</p>
                <input type="file" name="product_images[]" accept="image/*" multiple>

                <button type="submit" name="edit_product" class="btn confirm-btn">Update Product</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>

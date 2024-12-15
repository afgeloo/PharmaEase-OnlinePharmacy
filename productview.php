<?php
session_start();
require 'includes/dbconnect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product details
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = intval($_GET['id']);
    $sql = "
        SELECT 
            p.product_id, 
            p.product_name, 
            p.product_price, 
            p.product_description, 
            p.store, 
            c.category_name, 
            pi.image_name_1, 
            pi.image_name_2, 
            pi.image_name_3 
        FROM products p
        JOIN product_categories c ON p.category_id = c.category_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        WHERE p.product_id = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found.");
    }
    $stmt->close();
} else {
    die("Product ID is missing or invalid.");
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product View - PharmaEase</title>
    <link rel="shortcut icon" href="assets/PharmaEaseLogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/productview.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #FFF9F0;
        }
        .product-title {
            font-size: 1.75rem;
            font-weight: bold;
        }
        .main-product-image {
            width: 100%;
            height: auto;
            max-height: 450px;
            object-fit: cover;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .thumbnail-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            cursor: pointer;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row g-4">
        <div class="col-md-5">
            <?php
            $images = [];
            if (!empty($product['image_name_1'])) { $images[] = $product['image_name_1']; }
            if (!empty($product['image_name_2'])) { $images[] = $product['image_name_2']; }
            if (!empty($product['image_name_3'])) { $images[] = $product['image_name_3']; }

            if (empty($images)) {
                $images[] = "assets/default-image.jpg";
            }

            $mainImage = $images[0];
            ?>
            <div class="text-center mb-3">
                <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="Main Product Image" class="main-product-image" id="main-product-image">
            </div>
            <?php if (count($images) > 1): ?>
            <div class="row g-2">
                <?php foreach ($images as $img): ?>
                <div class="col-auto">
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Product Thumbnail" class="thumbnail-image" data-thumb-img="<?php echo htmlspecialchars($img); ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-7">
            <div class="card p-4">
                <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <p>Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                <p>Price: ₱<?php echo number_format($product['product_price'], 2); ?></p>
                <p>Store: <?php echo htmlspecialchars($product['store']); ?></p>
                <p>Description: <?php echo htmlspecialchars($product['product_description']); ?></p>

                <form id="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                    <div class="d-flex align-items-center mb-3">
                        <button type="button" data-quantity-minus class="btn btn-outline-secondary">-</button>
                        <input type="number" name="quantity" data-quantity-target class="form-control quantity-input mx-2" value="1" min="1">
                        <button type="button" data-quantity-plus class="btn btn-outline-secondary">+</button>
                    </div>
                    <button type="submit" class="btn btn-success">Add to Cart</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const quantityInput = document.querySelector('[data-quantity-target]');
        const minusBtn = document.querySelector('[data-quantity-minus]');
        const plusBtn = document.querySelector('[data-quantity-plus]');

        if (quantityInput && minusBtn && plusBtn) {
            minusBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value, 10) || 1;
                if (currentValue > 1) {
                    quantityInput.value = --currentValue;
                }
            });

            plusBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value, 10) || 1;
                quantityInput.value = ++currentValue;
            });

            quantityInput.addEventListener('input', () => {
                let val = parseInt(quantityInput.value, 10);
                if (isNaN(val) || val <= 0) {
                    quantityInput.value = 1;
                }
            });

            quantityInput.addEventListener('blur', () => {
                if (!quantityInput.value || parseInt(quantityInput.value, 10) <= 0) {
                    quantityInput.value = 1;
                }
            });
        }

        // AJAX form submission
        document.getElementById('add-to-cart-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Product added to cart!');
                        window.location.href = 'cart.php';
                    } else {
                        alert(data.message || 'Failed to add product to cart.');
                    }
                });
        });
    });
</script>
</body>
</html>
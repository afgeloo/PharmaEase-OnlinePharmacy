<?php
session_start();
require 'includes/dbconnect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Product View - PharmaEase</title>
    <link rel="shortcut icon" type="image/png" href="assets/PharmaEaseLogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/productview.css">
    <style>
        body {
            font-family: 'Varela Round', sans-serif;
            background-color: #f8f9fa;
        }
        .product-title {
            font-size: 1.75rem;
            font-weight: 600;
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
                $images[] = "/PharmaEase/PharmaEase-Final/assets/product/default-image.jpg";
            }

            // Main image
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
                <div class="mb-3">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                    <div class="text-muted">Category: <?php echo htmlspecialchars($product['category_name']); ?></div>
                    <div class="text-muted">Product No.: <strong><?php echo htmlspecialchars($product['product_id']); ?></strong></div>
                </div>
                <h3 class="text-primary mb-4">₱<?php echo number_format($product['product_price'], 2); ?></h3>
                <div class="mb-3">
                    <strong>Store:</strong> <?php echo htmlspecialchars($product['store']); ?>
                </div>
                <div class="mb-4 product-description">
                    <strong>Description:</strong>
                    <p><?php echo htmlspecialchars($product['product_description']); ?></p>
                </div>

                <form id="add-to-cart-form" method="POST" action="add_to_cart.php" class="mb-4">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="input-group" style="width:120px;">
                            <button class="btn btn-outline-secondary" type="button" data-quantity-minus>-</button>
                            <input type="number" name="quantity" value="1" step="1" min="1" class="form-control text-center mx-2 quantity-input" style="width: 50px;">
                            <button class="btn btn-outline-secondary" type="button" data-quantity-plus>+</button>
                        </div>
                        <button type="submit" class="btn btn-success w-100 ms-3">Add to Cart</button>
                    </div>
                </form>

                <div class="text-secondary">
                    PharmaEase ensures that individuals can access essential medications conveniently, especially during emergencies.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Thumbnail click logic
    document.querySelectorAll('.thumbnail-image').forEach(function(thumb) {
        thumb.addEventListener('click', function() {
            var newSrc = this.getAttribute('data-thumb-img');
            var mainImage = document.getElementById('main-product-image');
            mainImage.src = newSrc;
        });
    });

    // Quantity control logic
    (function() {
        var container = document.querySelector('[data-quantity-minus]')?.closest('.input-group');
        if (container) {
            var minusBtn = container.querySelector('[data-quantity-minus]');
            var plusBtn = container.querySelector('[data-quantity-plus]');
            var target = container.querySelector('[data-quantity-target]');
            var quantity = parseInt(target.value, 10);

            minusBtn.addEventListener('click', function() {
                if (quantity > 1) {
                    quantity--;
                    target.value = quantity;
                }
            });

            plusBtn.addEventListener('click', function() {
                quantity++;
                target.value = quantity;
            });

            target.addEventListener('input', function() {
                var val = parseInt(target.value, 10);
                if (!isNaN(val) && val > 0) {
                    quantity = val;
                }
            });

            target.addEventListener('blur', function() {
                if (target.value === '' || parseInt(target.value, 10) <= 0) {
                    quantity = 1;
                    target.value = quantity;
                }
            });
        }
    })();
</script>
</body>
</html>

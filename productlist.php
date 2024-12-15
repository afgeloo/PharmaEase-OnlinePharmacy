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
    <link rel="stylesheet" href="css/home.css">
    <title>Products List - PharmaEase</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/products.js"></script>

</head>
<body>
    <div class="container">
        <?php include 'includes/header.php'; ?>

        <h2>Products List</h2>
        <div class="product-container">
    <div id="grid-selector">
               <div id="grid-menu">
                      View:
                   <ul>           	   
                       <li class="largeGrid"><a href=""></a></li>
                       <li class="smallGrid"><a class="active" href=""></a></li>
                   </ul>
               </div>
               
               <ion-icon name="cart-outline"></ion-icon>
        </div>
        
        <div id="grid">
        <?php
        // Check if products are available
        if ($result->num_rows > 0) {
            // Loop through each product and display it
            while($row = $result->fetch_assoc()) {
              $images = []; 
              // Updated SQL query to fetch all image names
              $image_sql = "SELECT image_name_1, image_name_2, image_name_3 FROM product_images WHERE product_id = " . $row['product_id'];
              $image_result = $conn->query($image_sql);
              if($img = $image_result->fetch_assoc()) {
                  if (!empty($img['image_name_1'])) {
                      $images[] = $img['image_name_1']; // Main Image
                  }
                  if (!empty($img['image_name_2'])) {
                      $images[] = $img['image_name_2'];
                  }
                  if (!empty($img['image_name_3'])) {
                      $images[] = $img['image_name_3'];
                  }
              }
              ?>
                
                <div class="product">
                    <!-- Product Info -->
                    <div class="info-large">
                    <h4><?php echo htmlspecialchars($row['product_name']); ?></h4>

                        <?php if (!empty($row['category_name'])): ?>
                        <span><?php echo htmlspecialchars($row['category_name']); ?></span>
                        <?php endif; ?>                
                
                        <div class="price-big">
                            ₱<?php echo number_format($row['product_price'], 2); ?>
                        </div>
                
                        <h3>DESCRIPTION</h3>
                        <div class="colors-large">
                            <span><?php echo htmlspecialchars($row['product_description']); ?></span>
                        </div>
                
                        <h3>STORE</h3>
                        <div class="sizes-large">
                            <span><?php echo htmlspecialchars($row['store']); ?></span>
                        </div>
                
                        <h3>QUANTITY</h3> <!-- Added quantity heading -->
                        <div class="quantity-control" data-quantity="">
                            <button class="quantity-btn" data-quantity-minus="">
                                <!-- SVG Icon -->
                            </button>
                            <input type="number" class="quantity-input" data-quantity-target="" value="1" step="1" min="1" name="quantity">
                            <button class="quantity-btn" data-quantity-plus="">
                                <!-- SVG Icon -->
                            </button>
                        </div>
                
                        <form action="../cart/cart2.php" method="post">
                            <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($images[0]); ?>">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_id']); ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['product_name']); ?>">
                            <input type="hidden" name="category_name" value="<?php echo htmlspecialchars($row['category_name']); ?>">
                            <input type="hidden" name="product_description" value="<?php echo htmlspecialchars($row['product_description']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo htmlspecialchars($row['product_price']); ?>">
                            <input type="hidden" name="product_quantity" value="1"> <!-- Default quantity -->
                            <button type="submit" name="add_to_cart" class="add-cart-large">Add To Cart</button>
                        </form>
                
                        <a href="productview.php?id=<?php echo $row['product_id']; ?>" class="view_product_link">View Product Details</a>
                    </div>
                
                    <!-- 3D Product Display -->
                    <div class="make3D">
                        <div class="product-front">
                            <div class="shadow"></div>
                            <?php
                            if (!empty($images)) {
                                echo '<img src="' . htmlspecialchars($images[0]) . '" alt="Product Front Image" />';
                            } else {
                                echo '<img src="https://placehold.co/600x600" alt="Default Front Image" />';
                            }
                            ?>
                            <div class="image_overlay"></div>
                            <div class="view_gallery">View gallery</div>
                            <a href="productview.php?id=<?php echo $row['product_id']; ?>" class="view_details">View details</a>
                            <div class="stats">
                                <div class="stats-container">
                                    <span class="product_price">₱<?php echo number_format($row['product_price'], 2); ?></span>
                                    <span class="product_name"><?php echo htmlspecialchars($row['product_name']); ?></span>
                                    <br>
                                    <span class="category_name"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                    <div class="product-options">
                                    <br /><strong>DESCRIPTION</strong>
                                        <span><?php echo htmlspecialchars($row['product_description']); ?></span>
                                        <strong>STORE</strong>
                                        <div class="colors">
                                            <span><?php echo htmlspecialchars($row['store']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                
                        <!-- Product Back (Carousel) -->
                        <div class="product-back">
                            <div class="shadow"></div>
                            <div class="carousel">
                                <ul class="carousel-container">
                                    <?php
                                    if (!empty($images)) {
                                        $carousel_images = array_slice($images, 0, 3); // Show up to 3 images in carousel
                                        foreach ($carousel_images as $image) {
                                            echo '<li><img src="' . htmlspecialchars($image) . '" alt="Product Image" /></li>';
                                        }
                                    } else {
                                        echo '<li><img src="/path/to/default-image.jpg" alt="Default Image" /></li>';
                                    }
                                    ?>
                                </ul>
                                <div class="arrows-perspective">
                                    <div class="carouselPrev">
                                        <div class="y"></div>
                                        <div class="x"></div>
                                    </div>
                                    <div class="carouselNext">
                                        <div class="y"></div>
                                        <div class="x"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flip-back">
                                <div class="cy"></div>
                                <div class="cx"></div>
                            </div>
                        </div>
                    </div>  
                </div>
                <?php
            }
        } else {
            echo "No products found.";
        }
        ?>
        
    </div>

        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
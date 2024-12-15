<?php
include 'includes/dbconnect.php';

// Fetch categories from the database
$categorySql = "SELECT category_id, category_name FROM product_categories ORDER BY category_name ASC";
$categoryResult = $conn->query($categorySql);
?>
<header>
    <a href="home.php">
        <img src="assets/PharmaEaseFullLight.png" alt="PharmaEase Logo" class="logo-img">
    </a>
    <nav>
        <a href="home.php">Home</a>
        <a href="cart.php">Cart</a>
        <a href="checkout.php">Checkout</a>
        <a href="orders.php">Track Order</a>
        <a href="account.php">My Account</a>
        <a href="authentication/logout.php"><ion-icon name="log-out-outline" class="sign-out-img"></ion-icon></a>
    </nav>
</header>
<div class="navlist">
    <div>
        <a href="productlist.php">All Products</a>
        <?php if ($categoryResult->num_rows > 0): ?>
            <?php while($category = $categoryResult->fetch_assoc()): ?>
                <a href="productlist.php?category=<?php echo $category['category_id']; ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    <div class="row"></div>
</div>

<style>
    /* General styles for better layout handling */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #FFF9F0;
    }

    /* header */
    header {
        background-color: #88C273;
        position: sticky;
        top: 0; /* Sticky starts at the top */
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px;
        gap: 40px;
        font-size: 18px;
    }

    header p {
        color: #FFF9F0;
    }

    header .logo-img {
        height: 60px;
        margin-left: 8px;
    }

    nav a {
        color: #FFF9F0;
        text-decoration: none;
        margin-right: 20px;
        font-size: 18px;
        margin-top: 10px;
    }

    nav a:hover {
        text-decoration: underline;
    }

    /* navlist */
    .navlist {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        position: sticky;
        top: 80px; /* Adjust based on header height */
        background-color: #FFF9F0;
        z-index: 999; /* Lower than header but above content */
        padding: 20px 20px;
        font-size: 18px;
    }

    .navlist a {
        color: #333333;
        font-size: 18px;
        text-decoration: none;
        margin-right: 20px;
    }

    .navlist a:hover {
        text-decoration: underline;
    }

    input {
        font-size: 18px;
    }

    .sign-out-img {
        font-size: 26px;
        color: #FFF9F0;
        vertical-align: middle;
    }
</style>

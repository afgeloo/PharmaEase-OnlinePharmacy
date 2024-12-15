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
        <a href="authentication/logout.php"><ion-icon name="log-out-outline"></ion-icon> Sign Out</a>
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
    <div class="row">
    </div>
</div>

<style>
    /* header */
    header {
        background-color: #88C273;
        position: sticky;
        top: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px; /* Add padding */
        gap: 80px;
        padding-bottom: 20px; /* Add bottom padding */
    }
    
    header p {
        color: #FFF9F0;
        font-size: 48px;
        margin-left: 54px;
    }
    
    header .logo-img {
        height: 50px;
        margin-left: 54px;
    }
    
    a {
        margin-right: 39px;
        font-size: 14px;
        color: #FFF9F0;
        text-decoration: none;
    }
    
    .navlist a:link, .navlist a:visited, .navlist a:active {
    color: #333333;
    font-size: 18px;
    margin-right: 20px;
}

.navlist a:first-child {
    margin-left: 30px;
}

.navlist {
    display: flex;
    align-items: center;
    justify-content: center; /* Center the items horizontally */
    margin-top: 10px;
    margin-bottom: 10px;
    gap: 20px;
    position: sticky;
    top: 60px; /* Adjust based on header height */
    background-color: #FFF9F0; /* Match body background color */
    z-index: 1001; /* Ensure it stays above the header */
    padding: 10px 20px; /* Add padding */
}

nav a {
    color: #FFF9F0 !important;
}


    input {
        font-size: 18px;
    }
</style>
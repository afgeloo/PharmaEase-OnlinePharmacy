<header>
    <img src="../assets/PharmaEaseFullLight.png" alt="PharmaEase Logo" class="logo-img">
    <nav>
    <!-- <a href="homepage.php">Home</a> -->
    <!-- <a href="cart.php">Cart</a>
    <a href="checkout.php">Checkout</a>
    <a href="orders.php">Track Order</a> -->
    <a href="admin_dashboard.php">Dashboard</a> 
    <a href="manage_orders.php">Orders</a> 
    <a href="manage_products.php">Products</a> 
    <a href="../authentication/logout.php"><ion-icon name="log-out-outline"></ion-icon> Sign Out</a>
    </nav>
</header>
<!-- <div class="navlist">
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
    <div class="row">
    </div>
</div> -->


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
  
  /* header flexbox */
  /* Navlist 2nd nav bar with search icon */
  .navlist a:link, .navlist a:visited, .navlist a:active {
	color: #333333;
	font-size: 14px;
	margin-right: 20px;
  }
  
  .navlist a:first-child {
	margin-left: 30px;
  }
  
  .search {
	margin-right: 62px;
  }
  
  .navlist {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-top: 10px;
	margin-bottom: 10px;
	gap: 20px;
	position: sticky;
	top: 60px; /* Adjust based on header height */
	background-color: #FFF9F0; /* Match body background color */
	z-index: 1001; /* Ensure it stays above the header */
	padding: 10px 20px; /* Add padding */
  }
  
  input {
	font-size: 18px;
  }
</style>
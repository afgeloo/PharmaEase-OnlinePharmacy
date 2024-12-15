
<?php
require 'includes/dbconnect.php';

if (isset($_POST['productName'])) {
    $productName = $conn->real_escape_string($_POST['productName']);
    $sql = "SELECT product_name FROM products WHERE product_name LIKE '%$productName%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row['product_name'];
        }
        echo "Products found: " . implode(', ', $products);
    } else {
        echo "Product not found.";
    }
} else {
    echo "Invalid request.";
}
?>
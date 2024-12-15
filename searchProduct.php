<?php
require 'includes/dbconnect.php';

if (isset($_POST['productName'])) {
    // Use prepared statements to prevent SQL injection
    $productName = $_POST['productName'];
    $stmt = $conn->prepare(
        "SELECT p.product_name, c.category_name 
         FROM products p 
         JOIN product_categories c ON p.category_id = c.category_id 
         WHERE p.product_name LIKE ?"
    );
    
    $likeProductName = '%' . $productName . '%';
    $stmt->bind_param("s", $likeProductName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $output = [];
        while ($row = $result->fetch_assoc()) {
            // Add both product and category to the output array
            $output[] = "Product: " . $row['product_name'] . " | Category: " . $row['category_name'];
        }
        // Display the results with proper formatting
        echo "Products found:<br>" . implode('<br>', $output);
    } else {
        echo "No matching products found.";
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>

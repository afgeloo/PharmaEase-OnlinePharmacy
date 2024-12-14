<?php
$host = 'localhost';
$db   = 'pharmaease_db';
$user = 'root'; 
$pass = '';    
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Enable exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options); // Create PDO instance
} catch (\PDOException $e) {
     die('Database connection failed: ' . $e->getMessage()); // Handle connection errors
}
?>

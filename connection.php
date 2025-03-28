<?php
$host = 'localhost';
$dbname = 'ecommerce_db';
$username = 'root'; // Replace with actual username if not 'root'
$password = ''; // Replace with actual password if not empty

// Correct the PDO connection usage
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage()); // Log the error instead of displaying it
    echo "Connection failed: Please check the server settings."; // Show a general error message to the user
}
?>

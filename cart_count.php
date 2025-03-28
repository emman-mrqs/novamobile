<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['cartCount' => 0]);
    exit();
}

// Ensure the user is logged in
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['cartCount' => 0]);
    exit();
}

// Get logged-in user's email
$userEmail = $_SESSION['user_email'];

// Query the database for the count of unique products in the cart
$stmt = $conn->prepare("SELECT COUNT(*) as totalProducts FROM cart_items WHERE user_email = :email");
$stmt->execute([':email' => $userEmail]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$cartCount = $result['totalProducts'] ?? 0;

// Return the cart count as JSON
echo json_encode(['cartCount' => $cartCount]);
?>

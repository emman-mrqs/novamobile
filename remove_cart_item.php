<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "Connection failed: " . $e->getMessage()]);
    exit();
}

// Check if item ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $itemId = $_GET['id'];
    $userEmail = $_SESSION['user_email']; // Get logged-in user's email

    // Delete the item from the database
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = :id AND user_email = :email");
    $stmt->execute([':id' => $itemId, ':email' => $userEmail]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove item or item does not exist.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid item ID.']);
}
?>

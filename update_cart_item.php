<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Include database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

try {
    // Ensure POST request and validate input
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $itemId = $_POST['id'] ?? null;
        $quantity = $_POST['quantity'] ?? null;

        // Validate input
        if (!is_numeric($itemId) || !is_numeric($quantity) || $quantity < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
            exit();
        }

        // Fetch the available stock for the product
        $stockQuery = $conn->prepare("
            SELECT p.quantity AS available_stock
            FROM cart_items ci
            JOIN products p ON ci.product_name = p.name
            WHERE ci.id = :id
        ");
        $stockQuery->execute([':id' => $itemId]);
        $product = $stockQuery->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit();
        }

        $availableStock = $product['available_stock'];

        // Check if the requested quantity exceeds the available stock
        if ($quantity > $availableStock) {
            echo json_encode(['status' => 'error', 'message' => "Only {$availableStock} items are available in stock."]);
            exit();
        }

        // Update the cart item with the new quantity
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = :quantity WHERE id = :id");
        if ($stmt->execute([':quantity' => $quantity, ':id' => $itemId])) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No changes made']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update the database']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again later.']);
}


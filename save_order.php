<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

// Ensure the user is logged in and their email is available
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
    exit();
}

$userEmail = $_SESSION['user_email']; // Get the logged-in user's email

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    echo json_encode(['status' => 'error', 'message' => 'No data received.']);
    exit();
}

try {
    // Save order to `orders` table
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_email, first_name, last_name, address, country, state, zip, 
            subtotal, shipping_fee, total_amount, voucher_code, voucher_discount, 
            payment, change_amount
        ) 
        VALUES (
            :user_email, :first_name, :last_name, :address, :country, :state, :zip, 
            :subtotal, :shipping_fee, :total_amount, :voucher_code, :voucher_discount, 
            :payment, :change_amount
        )
    ");
    $stmt->execute([
        ':user_email' => $userEmail,
        ':first_name' => $data['firstName'],
        ':last_name' => $data['lastName'],
        ':address' => $data['address'],
        ':country' => $data['country'],
        ':state' => $data['state'],
        ':zip' => $data['zip'],
        ':subtotal' => $data['subtotal'],
        ':shipping_fee' => $data['shippingFee'],
        ':total_amount' => $data['totalAmount'],
        ':voucher_code' => !empty($data['voucherCode']) ? $data['voucherCode'] : null,
        ':voucher_discount' => !empty($data['voucherDiscount']) ? $data['voucherDiscount'] : 0,
        ':payment' => $data['payment'],
        ':change_amount' => $data['change'],
    ]);

    // Get the last inserted order ID
    $orderId = $conn->lastInsertId();

    // Save order details to `order_details` table
    $stmt = $conn->prepare("
        INSERT INTO order_details (order_id, user_email, product_name, quantity, price) 
        VALUES (:order_id, :user_email, :product_name, :quantity, :price)
    ");

    $processedProducts = [];

    foreach ($data['orderDetails'] as $item) {
        if (in_array($item['name'], ['Subtotal', 'Shipping', 'Coupon', 'Total Amount'])) {
            continue;
        }

        // Extract quantity and clean product name
        preg_match('/\(x(\d+)\)/', $item['name'], $matches);
        $actualQuantity = isset($matches[1]) ? (int)$matches[1] : $item['quantity'];
        $cleanProductName = preg_replace('/\(x\d+\)/', '', $item['name']);

        // Skip duplicates
        if (in_array($cleanProductName, $processedProducts)) {
            continue;
        }

        $processedProducts[] = $cleanProductName;

        $stmt->execute([
            ':order_id' => $orderId,
            ':user_email' => $userEmail,
            ':product_name' => $cleanProductName,
            ':quantity' => $actualQuantity,
            ':price' => $item['price'],
        ]);

        // Decrement the quantity in the products table
        $updateStmt = $conn->prepare("
            UPDATE products 
            SET quantity = quantity - :quantity 
            WHERE name = :name AND quantity >= :quantity
        ");
        $updateStmt->execute([
            ':quantity' => $actualQuantity,
            ':name' => $cleanProductName,
        ]);

        if ($updateStmt->rowCount() === 0) {
            throw new Exception("Not enough stock for product: $cleanProductName");
        }
    }

    // Decrement the quantity of the voucher if used
    if (!empty($data['voucherCode'])) {
        $voucherStmt = $conn->prepare("
            UPDATE vouchers 
            SET quantity = quantity - 1 
            WHERE promo_code = :promo_code AND quantity > 0
        ");
        $voucherStmt->execute([':promo_code' => $data['voucherCode']]);
    
        if ($voucherStmt->rowCount() === 0) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Voucher code \'' . htmlspecialchars($data['voucherCode']) . '\' is no longer valid or out of stock.'
            ]);
            exit();
        }
    }
    

    // Clear the user's cart
    $deleteStmt = $conn->prepare("DELETE FROM cart_items WHERE user_email = :user_email");
    $deleteStmt->execute([':user_email' => $userEmail]);

    echo json_encode(['status' => 'success', 'message' => 'Order saved successfully.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save order: ' . $e->getMessage()]);
}



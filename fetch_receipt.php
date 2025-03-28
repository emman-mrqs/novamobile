<?php
include('connection.php'); // Include database connection

if (!isset($_GET['order_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID is missing.']);
    exit();
}

$orderId = intval($_GET['order_id']);

try {
    // Fetch order info
    $orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = :order_id");
    $orderStmt->execute([':order_id' => $orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    // Fetch order items
    $orderDetailsStmt = $conn->prepare("SELECT * FROM order_details WHERE order_id = :order_id");
    $orderDetailsStmt->execute([':order_id' => $orderId]);
    $orderDetails = $orderDetailsStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($order && $orderDetails) {
        echo json_encode([
            'status' => 'success',
            'order' => $order,
            'details' => $orderDetails
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching order: ' . $e->getMessage()]);
}
?>

<?php
include('connection.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['order_id']);
    $status = $_POST['status'];

    try {
        // Update the status in the order_details table
        $stmt = $conn->prepare("UPDATE order_details SET status = :status WHERE order_id = :order_id");
        $stmt->execute([':status' => $status, ':order_id' => $orderId]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>

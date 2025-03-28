<?php
session_start();
header('Content-Type: application/json');

// Read the raw input data
$data = json_decode(file_get_contents('php://input'), true);

// Check if items are provided
if (!isset($data['items']) || empty($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'No items selected.']);
    exit();
}

// Save the selected items in the session
$_SESSION['checkout_items'] = $data['items'];

// Respond with success
echo json_encode(['status' => 'success']);
exit();
?>
<?php
include('connection.php'); // Include your database connection file

// Start session to access user data
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and feedback data is submitted
if (isset($_SESSION['user_email'], $_POST['feedback'])) {
    $userEmail = $_SESSION['user_email'];
    $expression = $_POST['feedback'];
    $message = isset($_POST['message']) ? trim($_POST['message']) : ''; // Optional message

    try {
        // Insert feedback into the database
        $stmt = $conn->prepare("INSERT INTO customer_feedback (user_email, expression, message) VALUES (:user_email, :expression, :message)");
        $stmt->bindParam(':user_email', $userEmail);
        $stmt->bindParam(':expression', $expression);
        $stmt->bindParam(':message', $message);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Feedback submitted successfully']);
    } catch (PDOException $e) {
        error_log('Error saving feedback: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to submit feedback']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid feedback submission']);
}
?>

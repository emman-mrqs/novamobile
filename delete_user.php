<?php
include('connection.php'); // Include the database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['id'])) {
    $userId = $_POST['id'];

    // Ensure the user ID is valid
    if (empty($userId)) {
        echo 'error: no user ID received';
        exit;
    }

    try {
        // Prepare SQL query to delete the user
        $query = "DELETE FROM tb_user WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        // Execute the query
        if ($stmt->execute()) {
            echo 'success'; // Success response
        } else {
            echo 'error: could not execute query'; // Error if query execution fails
        }
    } catch (PDOException $e) {
        // Log any exceptions that occur
        error_log('Error deleting user: ' . $e->getMessage());
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'error: no ID received'; // Return error if no ID is provided
}
?>


<?php

// delete_product.php
include('connection.php');

if (isset($_POST['id'])) {
    $productId = $_POST['id'];

    try {
        // Delete product from the database
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error: failed to delete product';
        }
    } catch (PDOException $e) {
        error_log('Error deleting product: ' . $e->getMessage());
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'error: no product ID received';
}
?>
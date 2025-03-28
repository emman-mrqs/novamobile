<?php
include('connection.php'); // Include database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect POST data
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $color = isset($_POST['color']) ? $_POST['color'] : null;
    $storage = isset($_POST['storage']) ? $_POST['storage'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $specification = isset($_POST['specifications']) ? $_POST['specifications'] : null; // Note the singular column name
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $type = isset($_POST['type']) ? $_POST['type'] : null;

    // Handle image upload
    $image = $_FILES['image']['name'];
    $target = __DIR__ . "/uploads/" . basename($image);

    // Check if the uploads directory exists
    if (!file_exists(__DIR__ . "/uploads/")) {
        mkdir(__DIR__ . "/uploads/", 0777, true); // Create the uploads directory if it doesn't exist
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        try {
            // Prepare SQL query to insert the product
            $query = "INSERT INTO products (name, color, storage, description, specification, quantity, price, type, image) 
                      VALUES (:name, :color, :storage, :description, :specification, :quantity, :price, :type, :image)";
            $stmt = $conn->prepare($query);

            // Bind parameters to the SQL query
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':storage', $storage);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':specification', $specification); // Singular to match your database table
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':image', $image); // Use $image to store just the filename

            // Execute the query
            if ($stmt->execute()) {
                echo "Product added successfully";
            } else {
                echo "Failed to add product: " . print_r($stmt->errorInfo(), true);
            }
        } catch (PDOException $e) {
            error_log('Error adding product: ' . $e->getMessage());
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Failed to upload image";
    }
}

?>

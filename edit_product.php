<?php
include('connection.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id']; // Get the product ID

    // Ensure the ID is present
    if (empty($id)) {
        echo "Error: Product ID is missing";
        exit;
    }

    // Collect form data
    $name = $_POST['name'];
    $color = $_POST['color'];
    $storage = $_POST['storage'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $specification = $_POST['specification'] ?? null; // Fallback to null if not set
    $image = $_FILES['image']['name'];

    // Handle image upload
    if ($image) {
        $uploadDir = "uploads/";
        $target = $uploadDir . basename($image);
    
        // Validate file type
        $allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
    
        if (!in_array($fileType, $allowedFileTypes)) {
            echo "Error: Invalid file type. Only JPG, PNG, and GIF are allowed.";
            exit;
        }
    
        // Validate file size (e.g., max 5MB)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            echo "Error: File size exceeds 5MB.";
            exit;
        }
    
        // Move the uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            echo "Error: Failed to upload image.";
            exit;
        }
    } else {
        // If no new image, retain the existing one
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $target = $product['image'] ?? ''; // Keep existing image if no new one
    }
    
    // Build the SQL query
    $query = "UPDATE products SET 
    name = :name, 
    color = :color, 
    storage = :storage, 
    description = :description, 
    quantity = :quantity, 
    price = :price, 
    type = :type, 
    image = :image";

    // Include specification only if it's provided
    if (!is_null($specification)) {
        $query .= ", specification = :specification";
    }

    $query .= " WHERE id = :id";

    // Prepare and bind parameters
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':color', $color);
    $stmt->bindParam(':storage', $storage);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':image', $target);

    if (!is_null($specification)) {
        $stmt->bindParam(':specification', $specification);
    }

    // Execute the query
    try {
        if ($stmt->execute()) {
            echo "Product updated successfully";
        } else {
            echo "Error: Failed to update product.";
        }
    } catch (PDOException $e) {
        error_log('Error updating product: ' . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
}
?>

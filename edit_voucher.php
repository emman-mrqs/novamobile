<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $promo_code = $_POST['promo_code'];
    $code_percentage = $_POST['code_percentage'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];

    try {
        $query = "UPDATE vouchers SET promo_code = :promo_code, code_percentage = :code_percentage, description = :description, quantity = :quantity WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':promo_code', $promo_code);
        $stmt->bindParam(':code_percentage', $code_percentage);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity);

        if ($stmt->execute()) {
            echo 'Voucher updated successfully';
        } else {
            echo 'Failed to update voucher';
        }
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>

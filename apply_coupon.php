<?php
include('connection.php');
session_start();

// Get the promo code from the AJAX request
$promoCode = $_POST['promo_code'];

try {
    // Check if the promo code exists in the database
    $stmt = $conn->prepare("SELECT * FROM vouchers WHERE promo_code = :promo_code");
    $stmt->bindParam(':promo_code', $promoCode);
    $stmt->execute();
    $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($voucher) {
        // Check if the voucher is out of stock
        if ($voucher['quantity'] <= 0) {
            $response = [
                'success' => false,
                'message' => 'This promo code is out of stock.',
            ];
        } else {
            $discountValue = $voucher['code_percentage']; // This can be either "50%" or "-₱100"

            $response = [
                'success' => true,
                'discount_value' => $discountValue,
            ];
        }
    } else {
        // Promo code not found in the database
        $response = [
            'success' => false,
            'message' => 'Invalid coupon code. Please try again.',
        ];
    }
} catch (PDOException $e) {
    error_log('Error fetching voucher: ' . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'An error occurred while applying the coupon. Please try again later.',
    ];
}

// Return the response as JSON
echo json_encode($response);
?>

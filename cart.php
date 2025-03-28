<?php
// Start the session only if it hasn't already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Get logged-in user's email
$userEmail = $_SESSION['user_email'];

// Handle POST request for adding to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['productName'] ?? '';
    $productPrice = $_POST['productPrice'] ?? '';
    $productImg = $_POST['productImg'] ?? '';
    $productColor = $_POST['productColor'] ?? '';
    $productStorage = $_POST['productStorage'] ?? '';
    $quantity = 1; // Default quantity to add is 1

    // Check available stock
    $stockStmt = $conn->prepare("SELECT quantity FROM products WHERE name = :name");
    $stockStmt->execute([':name' => $productName]);
    $product = $stockStmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
        exit();
    }

    $availableStock = $product['quantity'];

    // Check current quantity in cart
    $cartStmt = $conn->prepare("SELECT SUM(quantity) as totalQuantity FROM cart_items WHERE product_name = :name AND user_email = :email");
    $cartStmt->execute([':name' => $productName, ':email' => $_SESSION['user_email']]);
    $cartData = $cartStmt->fetch(PDO::FETCH_ASSOC);
    $currentCartQuantity = $cartData['totalQuantity'] ?? 0;

    // Validate if adding will exceed stock
    if ($currentCartQuantity + $quantity > $availableStock) {
        echo json_encode(['status' => 'error', 'message' => 'Not enough stock available.']);
        exit();
    }

    // Add to cart logic (Insert or Update)
    $stmt = $conn->prepare("SELECT id FROM cart_items WHERE user_email = :email AND product_name = :name AND product_color = :color AND product_storage = :storage");
    $stmt->execute([
        ':email' => $_SESSION['user_email'],
        ':name' => $productName,
        ':color' => $productColor,
        ':storage' => $productStorage
    ]);

    if ($stmt->rowCount() > 0) {
        // Update quantity if product exists
        $updateStmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + :quantity WHERE user_email = :email AND product_name = :name AND product_color = :color AND product_storage = :storage");
        $updateStmt->execute([
            ':quantity' => $quantity,
            ':email' => $_SESSION['user_email'],
            ':name' => $productName,
            ':color' => $productColor,
            ':storage' => $productStorage
        ]);
    } else {
        // Insert new product into the cart
        $insertStmt = $conn->prepare("INSERT INTO cart_items (user_email, product_name, product_price, product_image, product_color, product_storage, quantity) 
                                      VALUES (:email, :name, :price, :image, :color, :storage, :quantity)");
        $insertStmt->execute([
            ':email' => $_SESSION['user_email'],
            ':name' => $productName,
            ':price' => $productPrice,
            ':image' => $productImg,
            ':color' => $productColor,
            ':storage' => $productStorage,
            ':quantity' => $quantity
        ]);
    }

    echo json_encode(['status' => 'success']);
    exit();
}


// Handle GET request to display cart items
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $stmt = $conn->prepare("
        SELECT 
            ci.*, 
            p.quantity AS stock_quantity 
        FROM 
            cart_items ci
        JOIN 
            products p 
        ON 
            ci.product_name = p.name 
        WHERE 
            ci.user_email = :email
    ");
    $stmt->execute([':email' => $userEmail]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add this block to calculate the total price
$totalPrice = 0;

if (!empty($cartItems)) {
    foreach ($cartItems as $item) {
        $totalPrice += $item['product_price'] * $item['quantity']; // Calculate total price
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Mobile Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="./css/cart.css">
</head>
<body>
    <section class="h-100 h-custom" style="background-color: rgba(245, 245, 245, 0.9)">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-lg-8 col-md-10 col-sm-12">
                    <div class="card" style="height: 70vh; ">
                        <div class="card-body p-4 overflow-auto">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">
                                        <a href="./store.php" class="text-body">
                                            <i class="bi bi-arrow-left me-2"></i>Continue shopping
                                        </a>
                                    </h5>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <p class="mb-1 fw-bold">Shopping cart</p>
                                            <p class="mb-0">
                                                <?php echo !empty($cartItems) ? count($cartItems) : 0; ?> item(s) in your cart
                                            </p>
                                        </div>
                                    </div>
                                    <?php if (!empty($cartItems)): ?>
                                        <?php foreach ($cartItems as $item): ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center mb-2 mb-md-0">
                                                            <!-- Checkbox for item -->
                                                            <input type="checkbox" class="form-check-input me-3">
                                                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="Shopping item" class="img-fluid" style="width: 80px;">
                                                            <div class="ms-3">
                                                                <h5 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                                                <p class="small mb-0 fw-bold text-success">Available Stock: <?php echo $item['stock_quantity']; ?></p>
                                                                <p class="small mb-0">
                                                                <?php echo !empty($item['product_storage']) ? $item['product_storage'] . ' GB, ' : ''; ?>
                                                                <?php echo htmlspecialchars($item['product_color']); ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                            <div class="d-flex align-items-center mt-2 mt-md-0">
                                                                <div class="d-flex align-items-center me-3">
                                                                    <label class="me-2" for="qty-<?php echo $item['id']; ?>">Qty:</label>
                                                                    <input 
                                                                        type="number" 
                                                                        id="qty-<?php echo $item['id']; ?>" 
                                                                        name="qty" 
                                                                        min="1" 
                                                                        max="<?php echo $item['stock_quantity']; ?>" 
                                                                        value="<?php echo $item['quantity']; ?>" 
                                                                        class="form-control w-75 cart-quantity" 
                                                                        data-item-id="<?php echo $item['id']; ?>" 
                                                                        data-stock="<?php echo $item['stock_quantity']; ?>">
                                                                </div>
                                                            <div style="width: 100px;">
                                                            <h5 class="mb-0" data-price="<?php echo htmlspecialchars($item['product_price']); ?>">₱<?php echo number_format($item['product_price'], 2); ?></h5>
                                                            </div>
                                                            <a href="remove_cart_item.php?id=<?php echo $item['id']; ?>" class="text-body-secondary ms-2 custom-trash">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>Your cart is empty!</p>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="row align-items-center">
                                        <!-- Select All Checkbox -->
                                        <div class="col-md-6 col-sm-12 mb-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                                                <label class="form-check-label" for="inlineCheckbox1">Select all</label>
                                            </div>
                                        </div>

                                        <!-- Total and Proceed to Checkout Button -->
                                        <div class="col-md-6 col-sm-12 text-md-end text-sm-start">
                                             <p class="fw-bold mb-2">Total: <span id="total-price">₱<?php echo number_format($totalPrice, 2); ?></span></p>
                                             <a href="./checkout.php" id="proceedToCheckout">
                                                <button type="button" class="btn btn-primary">Proceed to Checkout</button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Validation Message Modal -->
    <div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="validationModalLabel">Notice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="validationModalBody">
                    <!-- The message will be injected here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this product?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
        <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Item removed from cart.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>



  
    <!--This is Footer -->
    <div style="background-color:  rgba(245, 245, 245, 0.9);" class="container-fluid ">
        <div style="padding: 1.5rem 0;">
        <footer  class="row row-cols-1 row-cols-sm-2 row-cols-md-5 py-5 my-0 border-top ">
            <div class="col mb-3">
            <a href="/" class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none">
                <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"></use></svg>
            </a>
            </div>
        
            <div class="col mb-3">
            <img class="logo2 " src="./img/logo/logo2.png" alt="logo2" height="120px">
            </div>
        
            <div class="col mb-3">
            <h5>Section</h5>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Home</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Features</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Pricing</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">FAQs</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">About</a></li>
            </ul>
            </div>
            <div class="col mb-3">
            <h5>Section</h5>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Home</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Features</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Pricing</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">FAQs</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">About</a></li>
            </ul>
            </div>
            <div class="col mb-3">
            
            <h5> Section</h5>
            
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Home</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Features</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">Pricing</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">FAQs</a></li>
                <li class="nav-item mb-2"><a href="#" class="nav-link p-0 text-body-secondary">About</a></li>
            </ul>
            </div>
        </footer>
        </div>
    </div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/cart.js"></script>
</body>
</html>

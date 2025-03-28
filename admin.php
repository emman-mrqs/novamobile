<?php
include('connection.php'); // Include database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Manage orders: Fetch all orders
try {
    // Fetch all orders
    $stmt = $conn->query("
    SELECT o.id AS order_id, 
           CONCAT(o.first_name, ' ', o.last_name) AS customer_name, 
           o.total_amount, 
           o.created_at, 
           GROUP_CONCAT(od.product_name SEPARATOR ', ') AS products, 
           od.status
    FROM orders o
    LEFT JOIN order_details od ON o.id = od.order_id
    GROUP BY o.id
");

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching orders: ' . $e->getMessage());
    $orders = []; // Default to an empty array if there's an error
}

// Ensure 'id' is received for deleting users
if (isset($_POST['id'])) {
    $userId = $_POST['id'];

    // Ensure userId is valid
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
        echo 'error: ' . $e->getMessage(); // Output error message
    }
}

// Count total orders
try {
    $stmt = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalOrders = $result['total_orders'];
} catch (PDOException $e) {
    error_log('Error fetching order count: ' . $e->getMessage());
    $totalOrders = 0; // Default to 0 if there's an error
}


// Count total users
try {
    $stmt = $conn->query("SELECT COUNT(*) AS total_users FROM tb_user");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $result['total_users'];
} catch (PDOException $e) {
    error_log('Error fetching user count: ' . $e->getMessage());
    $totalUsers = 0; // Default to 0 if there's an error
}

// Count total products
try {
    $stmt = $conn->query("SELECT COUNT(*) AS total_products FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalProducts = $result['total_products'];
} catch (PDOException $e) {
    error_log('Error fetching product count: ' . $e->getMessage());
    $totalProducts = 0;
}

// Fetch voucher data
try {
    $stmt = $conn->query("SELECT * FROM vouchers");
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching vouchers: ' . $e->getMessage());
    $vouchers = []; // Default to an empty array if there's an error
}

// Fetch customer feedback
try {
    $stmt = $conn->query("SELECT user_email, expression, message, created_at FROM customer_feedback ORDER BY created_at DESC");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching feedback: ' . $e->getMessage());
    $feedbacks = [];
}

// Fetch customer feedback
try {
    $stmt = $conn->query("SELECT user_email, expression, message, created_at FROM customer_feedback ORDER BY created_at DESC");
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching feedback: ' . $e->getMessage());
    $feedbacks = [];
}


?>

<?php
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'admin@example.com') {
    // Redirect to the login page if not logged in as admin
    header("Location: login.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Mobile Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="./css/admin.css">
</head>
<body>
    <!-- This is Navbar -->
    <div class="main-navbar shadow-sm sticky-top">
        <div class="top-navbar">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-2 d-none d-md-block">
                        <img class="brand-name logo" src="./img/logo/logo.png" alt="Admin Logo">
                    </div>
                    <div class="col-md-5">
                        <ul class="nav justify-content-end align-items-center">
                            <!-- Removed Cart and Profile sections -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg navbar-light custom-bg aesthetic-navbar">
            <div class="container-fluid">
                <a class="navbar-brand text-black" href="#">Admin Dashboard</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person"></i> Admin
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profile</a></li>

                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

<!-- This is the Admin Dashboard -->
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="list-group">
                <a href="#recent-orders" class="list-group-item list-group-item-action active" id="dashboard-link">Dashboard</a>
                <a href="#manage-products" class="list-group-item list-group-item-action" id="manage-products-link">Manage Products</a>
                <a href="#manage-orders" class="list-group-item list-group-item-action" id="manage-orders-link">Manage Orders</a>
                <a href="#manage-users" class="list-group-item list-group-item-action" id="manage-users-link">Manage Users</a>
                <a href="#manage-voucher" class="list-group-item list-group-item-action" id="manage-voucher-link">Manage Vouchers</a>
                <a href="#customer-feadback" class="list-group-item list-group-item-action" id="customer-feadback-link">Customer feadback</a>

            </div>
        </div>
        <div class="col-lg-9 col-md-8">
        <h3 id="admin-dashboard-heading" class="d-none">Admin Dashboard</h3>
        <div class="row mt-4" id="card-section">
                <!-- Total Products Card -->
                <div class="col-md-6 col-lg-4" id="total-products-card">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Products</h5>
                            <p class="card-text"><?php echo htmlspecialchars($totalProducts); ?></p> <!-- Display the product count here -->
                        </div>
                    </div>
                </div>
                <!-- Total Orders Card -->
                <div class="col-md-6 col-lg-4" id="total-orders-card">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Orders</h5>
                            <p class="card-text"><?= htmlspecialchars($totalOrders); ?></p> <!-- Display the total order count -->
                        </div>
                    </div>
                </div>

                <!-- Total Users Card -->
                <div class="col-md-6 col-lg-4" id="total-users-card">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text"><?php echo htmlspecialchars($totalUsers); ?></p> <!-- Display the user count here -->
                        </div>
                    </div>
                </div>


                <!-- Manage Products Section -->
                <div id="manage-products" class="card mt-4 d-none">
                    <div class="card-header">
                        Manage Products
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Product Image</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Color</th>
                                        <th scope="col">Storage</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Specifications</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Price</th> <!-- New Price column -->
                                        <th scope="col">Type</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="product-list">
                                    <?php
                                    // Fetch products from the database
                                    $stmt = $conn->query("SELECT * FROM products");
                                    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr data-id='" . htmlspecialchars($product['id']) . "'>";
                                        echo "<td>";
                                            if (!empty($product['image'])) {
                                                $relativePath = 'img/products/' . basename($product['image']);
                                                echo "<img src='" . htmlspecialchars($relativePath) . "' alt='Product Image' class='img-thumbnail' width='50'>";
                                            } else {
                                                echo "No Image";
                                            }
                                        echo "</td>";
                                        echo "<td>" . htmlspecialchars($product['name']) . "</td>"; // Product Name
                                        echo "<td>" . htmlspecialchars($product['color']) . "</td>"; // Color
                                        echo "<td>" . htmlspecialchars($product['storage']) . "</td>"; // Storage
                                        echo "<td>" . htmlspecialchars($product['description']) . "</td>"; // Description
                                        echo "<td>" . htmlspecialchars($product['specification']) . "</td>"; // Specifications
                                        echo "<td>" . htmlspecialchars($product['quantity']) . "</td>"; // Quantity
                                        echo "<td>" . htmlspecialchars($product['price']) . "</td>"; // Price
                                        echo "<td>" . htmlspecialchars($product['type']) . "</td>"; // Type

                                        // Actions column
                                        echo "<td>
                                        <div class='d-flex gap-2'>     
                                            <button class='btn btn-sm btn-secondary edit-product'>Edit</button>
                                            <button class='btn btn-sm btn-danger delete-product'>Delete</button>
                                        </div>
                                    </td>";
                                    echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Button to trigger add product modal -->
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                                Add Product
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Manage Order -->
                <div id="manage-orders" class="card mt-4 d-none">
                    <div class="card-header">
                        Orders
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Order ID</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($orders)): ?>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>#<?= htmlspecialchars($order['order_id']); ?></td>
                                                <td><?= htmlspecialchars($order['products']); ?></td>
                                                <td><?= htmlspecialchars($order['customer_name']); ?></td>
                                                <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $order['status'] == 'Completed' ? 'success' : ($order['status'] == 'Pending' ? 'warning' : 'danger'); ?>">
                                                        <?= htmlspecialchars($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary receipt-order" data-order-id="<?= $order['order_id']; ?>">Receipt</button>
                                                    <button class="btn btn-sm btn-secondary edit-order" data-order-id="<?= $order['order_id']; ?>">Edit</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No orders available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                        <!-- Manage User -->
                <div id="manage-users" class="card mt-4">
                    <div class="card-header">
                        User Management
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        // Fetch users from the database
                                        $stmt = $conn->query("SELECT * FROM tb_user");
                                        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr data-id='" . htmlspecialchars($user['id']) . "'>";  // Adding the user ID as a data attribute
                                            echo "<td scope='row'>#" . htmlspecialchars($user['id']) . "</td>";
                                            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                                            echo "<td><button class='btn btn-sm btn-danger delete-user'>Delete</button></td>";
                                            echo "</tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Manage Voucher -->
                <div id="voucher-heading" class="m-0 d-none">
                    <h3>Vouchers</h3>
                </div>
                <div id="manage-voucher-section">
                    <div id="manage-voucher" class="card mt-4">
                        <div class="card-header">
                            Manage Vouchers
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Promo Code</th>
                                            <th scope="col">Code Percentage</th>
                                            <th scope="col">Description</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vouchers as $voucher): ?>
                                            <tr data-id="<?php echo htmlspecialchars($voucher['id']); ?>">
                                                <td><?php echo htmlspecialchars($voucher['promo_code']); ?></td>
                                                <td><?php echo htmlspecialchars($voucher['code_percentage']); ?></td>
                                                <td><?php echo htmlspecialchars($voucher['description']); ?></td>
                                                <td><?php echo htmlspecialchars($voucher['quantity']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-secondary edit-voucher">Edit</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Customer Feedback Section -->
                <div id="customer-feedback-heading" class="m-0">
                    <h3>Customer Feedback</h3>
                </div>
                <div id="customer-feedback-section">
                    <div class="card mt-4">
                        <div class="card-header">
                            Customer Feedback
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col">Email</th>
                                            <th scope="col">Expression</th>
                                            <th scope="col">Message</th>
                                            <th scope="col">Date Submitted</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        try {
                                            // Fetch feedback from the database
                                            $stmt = $conn->query("SELECT user_email, expression, message, created_at FROM customer_feedback ORDER BY created_at DESC");
                                            $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                            if (!empty($feedbacks)) {
                                                foreach ($feedbacks as $feedback) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($feedback['user_email']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($feedback['expression']) . "</td>";
                                                    echo "<td>" . htmlspecialchars(substr($feedback['message'], 0, 50)) . "...</td>";
                                                    echo "<td>" . htmlspecialchars($feedback['created_at']) . "</td>";
                                                    echo "<td>";
                                                    echo "<button class='btn btn-sm btn-primary view-message' data-message='" . htmlspecialchars($feedback['message']) . "'>View</button>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center'>No feedback available.</td></tr>";
                                            }
                                        } catch (PDOException $e) {
                                            echo "<tr><td colspan='5' class='text-center'>Error fetching feedback.</td></tr>";
                                            error_log('Error fetching feedback: ' . $e->getMessage());
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Modals -->

                <!-- Add Product Modal -->
                <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="add-product-form" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="product-name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="product-name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-color" class="form-label">Color</label>
                                        <input type="text" class="form-control" id="product-color" name="color">
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-storage" class="form-label">Storage</label>
                                        <input type="number" class="form-control" id="product-storage" name="storage">
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-description" class="form-label">Description</label>
                                        <textarea class="form-control" id="product-description" name="description"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-specifications" class="form-label">Specifications</label>
                                        <textarea class="form-control" id="product-specifications" name="specifications"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="product-quantity" name="quantity" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-price" class="form-label">Price</label>
                                        <input type="number" class="form-control" id="product-price" name="price" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-type" class="form-label">Product Type</label>
                                        <select class="form-select" id="product-type" name="type">
                                            <option value="Phone">Phone</option>
                                            <option value="Accessories">Accessories</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-image" class="form-label">Product Image</label>
                                        <input type="file" class="form-control" id="product-image" name="image" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Add Product</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Product Modal -->
                <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="edit-product-form" enctype="multipart/form-data">
                                    <input type="hidden" id="edit-product-id" name="id">
                                    <div class="mb-3">
                                        <label for="edit-product-name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="edit-product-name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-product-color" class="form-label">Color</label>
                                        <input type="text" class="form-control" id="edit-product-color" name="color">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-product-storage" class="form-label">Storage</label>
                                        <input type="number" class="form-control" id="edit-product-storage" name="storage">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-product-description" class="form-label">Description</label>
                                        <textarea class="form-control" id="edit-product-description" name="description" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-product-specification" class="form-label">Specification</label>
                                        <textarea class="form-control" id="edit-product-specification" name="specification" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-product-quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="edit-product-quantity" name="quantity">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-product-price" class="form-label">Price</label>
                                        <input type="number" class="form-control" id="edit-product-price" name="price" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-product-type" class="form-label">Product Type</label>
                                        <select class="form-control" id="edit-product-type" name="type">
                                            <option value="Phone">Phone</option>
                                            <option value="Accessories">Accessories</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-product-image" class="form-label">Product Image</label>
                                        <input type="file" class="form-control" id="edit-product-image" name="image">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Product Modal -->
                <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteProductModalLabel">Delete Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this product?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="confirm-delete-product">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete User Modal -->
                <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this user?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger" id="confirm-delete-user">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Voucher Modal -->
                <div class="modal fade" id="editVoucherModal" tabindex="-1" aria-labelledby="editVoucherModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editVoucherModalLabel">Edit Voucher</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="edit-voucher-form">
                                    <input type="hidden" id="voucher-id">
                                    <div class="mb-3">
                                        <label for="voucher-code" class="form-label">Promo Code</label>
                                        <input type="text" class="form-control" id="voucher-code" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="voucher-percentage" class="form-label">Code Percentage</label>
                                        <input type="text" class="form-control" id="voucher-percentage">
                                    </div>
                                    <div class="mb-3">
                                        <label for="voucher-description" class="form-label">Description</label>
                                        <textarea class="form-control" id="voucher-description" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="voucher-quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="voucher-quantity" required>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Receipt Modal -->
                <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title" id="receiptModalLabel"><i class="bi bi-receipt-cutoff"></i> Order Receipt</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Billing Address -->
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="bi bi-person-circle"></i> Billing Address</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong id="billingName"></strong></p>
                                        <p class="mb-1" id="billingAddress"></p>
                                        <p id="billingEmail"></p>
                                    </div>
                                </div>

                                <!-- Order Details -->
                                <div class="card mb-3">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="bi bi-cart-check"></i> Order Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul id="orderDetailsList" class="list-group list-group-flush"></ul>
                                    </div>
                                </div>

                                <!-- Checkout Summary -->
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="bi bi-receipt"></i> Checkout Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>Subtotal:</strong> <span id="summarySubtotal"></span></p>
                                        <p class="mb-1"><strong>Shipping Fee:</strong> <span id="summaryShipping"></span></p>
                                        <p class="mb-1 text-success"><strong>Coupon Discount:</strong> <span id="summaryCoupon"></span></p>
                                        <hr>
                                        <p class="mb-1"><strong>Total Amount:</strong> <span id="summaryTotal"></span></p>
                                        <p class="mb-1"><strong>Amount Paid:</strong> <span id="summaryPaid"></span></p>
                                        <p class="mb-0"><strong>Change:</strong> <span id="summaryChange"></span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success w-100" data-bs-dismiss="modal"><i class="bi bi-check-circle"></i> Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Status order Modal -->
                <div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editOrderModalLabel">Edit Order Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="edit-order-form">
                                    <input type="hidden" id="edit-order-id" name="order_id">
                                    <div class="mb-3">
                                        <label for="edit-order-status" class="form-label">Status</label>
                                        <select class="form-select" id="edit-order-status" name="status">
                                            <option value="Pending">Pending</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="save-order-status">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Message Modal -->
                <div class="modal fade" id="viewMessageModal" tabindex="-1" aria-labelledby="viewMessageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewMessageModalLabel">Customer Feedback</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p id="feedbackMessageContent"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- End the table -->
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="./js/admin.js"></script>    
</body>
</html>
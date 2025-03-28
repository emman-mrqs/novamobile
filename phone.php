<?php

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
}


// Start the session only if it hasn't already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Mobile Phones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="./css/store.css">
</head>
<body>
    <!-- This is navbar -->
    <div class="main-navbar shadow-sm sticky-top">
    <div class="top-navbar">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-2 my-auto d-none d-sm-none d-md-block d-lg-block">
                    <img class="brand-name logo" src="./img/logo/logo.png" alt="">
                </div>
                <div class="col-md-5 my-auto">
                    <form role="search">
                        <div class="input-group">
                            <input type="search" placeholder="Search your product" class="form-control" />
                            <button class="btn bg-white" type="submit">
                                <!-- Bootstrap search icon -->
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-5 my-auto">
                    <ul class="nav justify-content-end">
                        <li class="nav-item">
                            <a class="nav-link" href="./cart.php">
                                <!-- Bootstrap cart icon -->
                                <i class="bi bi-cart"></i> Cart (<span id="cart-count">0</span>)
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <?php
                            // Start the session
                            if (session_status() === PHP_SESSION_NONE) {
                                session_start();
                            }

                            // Check if the user is logged in and display their name in the dropdown
                            if (isset($_SESSION['user_name'])) {
                                $userName = htmlspecialchars($_SESSION['user_name']); // Get the logged-in user's name safely
                            ?>
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <!-- Bootstrap user icon -->
                                    <i class="bi bi-person"></i> <?php echo $userName; ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profile</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-list"></i> My Orders</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-cart"></i> My Cart</a></li>
                                    <li><a class="dropdown-item" href="./logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                            <?php
                            } else {
                                // If not logged in, show the default "Username" text
                            ?>
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <!-- Bootstrap user icon -->
                                    <i class="bi bi-person"></i> Username
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="./login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                                    <li><a class="dropdown-item" href="./signup.php"><i class="bi bi-person-plus"></i> Sign Up</a></li>
                                </ul>
                            <?php
                            }
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand d-block d-sm-block d-md-none d-lg-none" href="#"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="./user.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./store.php">Store</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link">Phones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="./accessories.php">Accessories</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

        <!-- Products -->
        <div class="container mt-5">
        <div class="row justify-content-center">
            <?php
            // Fetch only products of type 'phone' from the database
            $stmt = $conn->prepare("SELECT * FROM products WHERE type = :type");
            $stmt->execute(['type' => 'phone']);
            
            while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="col-lg-3 col-md-6 mb-4">';
                echo '<div class="card m-2" style="width: 18rem;">';
                if (!empty($product['image'])) {
                    $relativePath = 'img/products/' . basename($product['image']);
                    echo '<img src="' . htmlspecialchars($relativePath) . '" class="card-img-top" alt="' . htmlspecialchars($product['name']) . '">';
                } else {
                    echo '<img src="./img/no-image.png" class="card-img-top" alt="No Image">';
                }
                echo '<div class="card-body">';
                echo '<h5 class="card-title"><b>' . htmlspecialchars($product['name']) . '</b></h5>';
                echo '<p class="card-text">₱' . htmlspecialchars(number_format($product['price'], 2)) . '</p>';
                echo '<a href="#" class="btn btn-primary add-to-cart" data-id="' . htmlspecialchars($product['id']) . '">Buy Now</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
  

<!--This is Footer -->
<div class="container-fluid">
    <footer class="row row-cols-1 row-cols-sm-2 row-cols-md-5 py-5 my-5 border-top">
      <div class="col mb-3">
        <a href="/" class="d-flex align-items-center mb-3 link-body-emphasis text-decoration-none">
          <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"></use></svg>
        </a>
       
      </div>
  
      <div class="col mb-3">
        <img class="logo2 " src="./img/logo/logo2.png" alt="logo2">
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="./js/store.js"></script>

</body>
</html>

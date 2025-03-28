<?php
// Include your database connection
include('connection.php');

// Start the session only if it hasn't already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Fetch vouchers from the database
try {
    $stmt = $conn->query("SELECT * FROM vouchers");
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching vouchers: ' . $e->getMessage());
    $vouchers = []; // Default to an empty array if there's an error
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Mobile Phone Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="./css/voucher.css">

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
                      <li class="nav-item ">
                          <a class="nav-link " href="./user.php">Home</a>
                      </li>
                      <li class="nav-item active">
                          <a class="nav-link" >Voucher</a>
                      </li>
                  </ul>
              </div>
          </div>
      </nav>
    </div>

<!-- Voucher Section -->
<div class="container mt-5">
    <div class="d-flex justify-content-center row">
    <?php foreach ($vouchers as $voucher): ?>
    <div class="col-md-6 mb-4">
        <div class="coupon p-4 bg-white shadow-sm rounded" style="background: linear-gradient(135deg, #f0f4f7, #ffffff); border: 2px solid #e0e0e0;">
            <div class="row no-gutters">
                <div class="col-md-4 border-right d-flex flex-column align-items-center justify-content-center" style="background: <?php echo getVoucherBackgroundColor($voucher['id']); ?>; color: #fff;">
                    <i class="<?php echo getVoucherIcon($voucher['id']); ?> display-4 mb-3"></i>
                    <small><?php echo getVoucherTitle($voucher['id']); ?></small>
                </div>
                <div class="col-md-8 p-3">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <h1 class="font-weight-bold" style="color: <?php echo getVoucherTextColor($voucher['id']); ?>;"><?php echo htmlspecialchars($voucher['code_percentage']); ?></h1>
                    </div>
                    <p class="mt-2 text-muted"><?php echo htmlspecialchars($voucher['description']); ?></p>
                    <div class="d-flex flex-row justify-content-between align-items-center px-2 py-1" style="border: 1px dashed #<?php echo getVoucherBorderColor($voucher['id']); ?>; border-radius: 5px;">
                        <span>Promo code:</span>
                        <button class="btn btn-sm view-code" style="background-color: <?php echo getVoucherButtonColor($voucher['id']); ?>; color: #fff;" data-code="<?php echo htmlspecialchars($voucher['promo_code']); ?>">Claim</button>
                        <span class="border px-3 rounded code font-weight-bold bg-light d-none"><?php echo htmlspecialchars($voucher['promo_code']); ?></span>
                    </div>
                    <small class="text-muted mt-2 d-block">Get Now there's only <?php echo htmlspecialchars($voucher['quantity']); ?> left!</small>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

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
<script src="./js/voucher.js"></script>
</body>
</html>

<?php
// Functions to determine dynamic styles
function getVoucherBackgroundColor($id) {
    switch ($id) {
        case 1: return '#1e90ff'; // Blue for "Special Offer"
        case 2: return '#e63946'; // Red for "Exclusive Deal"
        case 3: return '#f4c430'; // Yellow for "Seasonal Sale"
        case 4: return '#28a745'; // Green for "Free Shipping"
        default: return '#6c757d'; // Default gray if not matched
    }
}

function getVoucherIcon($id) {
    switch ($id) {
        case 1: return 'bi bi-phone-fill'; // Phone icon for Special Offer
        case 2: return 'bi bi-gift-fill'; // Gift icon for Exclusive Deal
        case 3: return 'bi bi-percent'; // Percentage icon for Seasonal Sale
        case 4: return 'bi bi-truck'; // Truck icon for Free Shipping
        default: return 'bi bi-question-circle'; // Default icon if not matched
    }
}

function getVoucherTitle($id) {
    switch ($id) {
        case 1: return 'Special Offer';
        case 2: return 'Exclusive Deal';
        case 3: return 'Seasonal Sale';
        case 4: return 'Free Shipping';
        default: return 'Special Offer'; // Default title if not matched
    }
}

function getVoucherTextColor($id) {
    switch ($id) {
        case 1: return '#1e90ff'; // Blue
        case 2: return '#e63946'; // Red
        case 3: return '#f4c430'; // Yellow
        case 4: return '#28a745'; // Green
        default: return '#6c757d'; // Default gray
    }
}

function getVoucherButtonColor($id) {
    switch ($id) {
        case 1: return '#1e90ff'; // Blue
        case 2: return '#e63946'; // Red
        case 3: return '#f4c430'; // Yellow
        case 4: return '#28a745'; // Green
        default: return '#6c757d'; // Default gray
    }
}

function getVoucherBorderColor($id) {
    switch ($id) {
        case 1: return '1e90ff'; // Blue
        case 2: return 'e63946'; // Red
        case 3: return 'f4c430'; // Yellow
        case 4: return '28a745'; // Green
        default: return '6c757d'; // Default gray
    }
}

?>
    <?php
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


    // Start the session only if it hasn't already been started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if the user is logged in
    if (!isset($_SESSION['user_name'])) {
        header("Location: login.php");
        exit();
    }

    // Get product ID from the URL query parameters
    $productID = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($productID > 0) {
        // Fetch product details from the database
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $productID, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo "Product not found!";
            exit();
        }
    } else {
        echo "Invalid Product ID!";
        exit();
    }

    // Assign product details to variables
    $productName = htmlspecialchars($product['name']);
    $productPrice = htmlspecialchars($product['price']); // Use raw value, do not format with commas or currency symbols
    $formattedPrice = '₱' . number_format($product['price'], 2);

    $productImg = !empty($product['image']) ? 'img/products/' . htmlspecialchars(basename($product['image'])) : './img/no-image.png';
    $productDescription = htmlspecialchars($product['description']);
    $productSpecification = htmlspecialchars($product['specification']);
    $productColor = htmlspecialchars($product['color']);
    $productStorage = htmlspecialchars($product['storage']);
    $productQuantity = intval($product['quantity']);  // Fetch quantity
    $productType = htmlspecialchars($product['type']); // Get the product type safely


    // Fetch related products based on the category
    $relatedProducts = [];
    if ($productType) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE type = :type AND id != :id LIMIT 4");
        $stmt->bindParam(':type', $productType, PDO::PARAM_STR);
        $stmt->bindParam(':id', $productID, PDO::PARAM_INT);
        $stmt->execute();
        $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    ?>



    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Nova Mobile Phone | Buy</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- Bootstrap Icons CSS -->
        <link rel="stylesheet" href="./css/buy.css">

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
                            <a class="nav-link">Products</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>


    <!-- Product Details Section -->
    <div class="container mt-5">
        <div class="row gx-4 gy-5 align-items-center">
            <div class="col-lg-6 col-md-12 d-flex justify-content-center align-items-center">
                <div class="d-flex justify-content-center align-items-center" style="width: 100%; max-width: 500px; height: 500px; background-color: #e0e5e5; border-radius: 20px;">
                    <img src="<?php echo $productImg; ?>" class="img-fluid custom-image" alt="<?php echo $productName; ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-lg-6 col-md-12">
                <h2 class="mb-2"><?php echo $productName; ?></h2>
                <p class="fs-5">₱<?php echo $productPrice; ?></p>
                <div class="mb-3">
                    <p id="type"><?php echo $productType; ?></p>
                </div>

                <div class="mb-3">
                    <label for="color" class="form-label">Color</label>
                    <select class="form-select custom-dropdown w-100" id="color">
                        <option selected disabled>Select Color</option>
                        <option><?php echo $productColor; ?></option>
                    </select>
                </div>
                <?php if (strtolower($productType) === 'phone') { ?>
                    <div class="mb-3">
                        <label for="storage" class="form-label">Storage</label>
                        <select class="form-select custom-dropdown w-100" id="storage">
                            <option selected disabled>Select Storage</option>
                            <option><?php echo $productStorage; ?></option>
                        </select>
                    </div>
                <?php } ?>
                <div class="mb-3">
                    <p class="fs-6 mt-3"><?php echo $productQuantity; ?> available</p>
                </div>
                <button type="button" class="btn btn-primary add-to-cart" id="addToCartBtn">Add to Cart</button>
            </div>
        </div>


        <!-- Tab Section -->
        <div class="row mt-5">
            <div class="col-12">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Description</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab" aria-controls="specifications" aria-selected="false">Specifications</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">Reviews (0)</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                        <p><?php echo $productDescription; ?></p>
                    </div>
                    <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                        <p><?php echo $productSpecification; ?></p>
                    </div>
                    <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                        <p>No reviews yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div class="row mt-5">
            <h2 class="mb-3">Related Products</h2>

            <?php if (!empty($relatedProducts)) { ?>
                <?php foreach ($relatedProducts as $relatedProduct) { 
                    $relatedImg = !empty($relatedProduct['image']) 
                        ? 'img/products/' . htmlspecialchars(basename($relatedProduct['image'])) 
                        : './img/no-image.png';
                    $relatedName = htmlspecialchars($relatedProduct['name']);
                    $relatedPrice = '₱' . number_format($relatedProduct['price'], 2);
                ?>
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                        <div class="card m-2" style="width: 100%;">
                            <img src="<?php echo $relatedImg; ?>" class="card-img-top" alt="<?php echo $relatedName; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><b><?php echo $relatedName; ?></b></h5>
                                <p class="card-text"><?php echo $relatedPrice; ?></p>
                                <a href="buy.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No related products found.</p>
            <?php } ?>
        </div>


        <!-- Bootstrap Modal for Out of Stock -->
        <div class="modal fade" id="outOfStockModal" tabindex="-1" aria-labelledby="outOfStockModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="outOfStockModalLabel">Out of Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Sorry, this product is currently out of stock.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap Modal for Custom Alerts -->
        <div class="modal fade" id="customModal" tabindex="-1" aria-labelledby="customModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="customModalLabel" >Notice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Message will be inserted here dynamically -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
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
    <script src="./js/buy.js"></script>
    <script>
        $(document).ready(function () {
            // Function to show modal with custom message
            function showModal(message) {
                $('#customModal .modal-body').text(message);
                $('#customModal').modal('show');
            }

            // Add-to-cart button click handler
            $('#addToCartBtn').on('click', function () {
            const productQuantity = <?php echo $productQuantity; ?>; // Available stock
            const productName = "<?php echo $productName; ?>";
            const productPrice = "<?php echo $productPrice; ?>"; // Raw price
            const productImg = "<?php echo $productImg; ?>";
            const productColor = $('#color').val();
            const productStorage = $('#storage').val();

            // Validate color selection
            if (!productColor || productColor === "Select Color") {
                showModal("Please select a color.");
                return;
            }

            // Validate storage selection (if applicable)
            if (<?php echo strtolower($productType) === 'phone' ? 'true' : 'false'; ?> && (!productStorage || productStorage === "Select Storage")) {
                showModal("Please select storage.");
                return;
            }

            // Validate stock before adding to cart
            $.ajax({
                type: "POST",
                url: "cart.php",
                data: {
                    productName: productName,
                    productPrice: productPrice,
                    productImg: productImg,
                    productColor: productColor,
                    productStorage: productStorage
                },
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.status === "success") {
                        showModal("Product added to cart!");
                    } else {
                        showModal(res.message || "Failed to add product to cart!");
                    }
                },
                error: function () {
                    showModal("An error occurred while adding the product to the cart.");
                }
            });
        });


            // Update cart count in real-time
            function updateCartCount() {
                $.ajax({
                    url: 'cart_count.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        $('#cart-count').text(response.cartCount);
                    }
                });
            }

            // Update the cart count every 5 seconds (or adjust as needed)
            setInterval(updateCartCount, 5000);

            // Call the function immediately when the page loads
            updateCartCount();

            // Update cart count after adding a product
            $(document).on('click', '.add-to-cart', function () {
                updateCartCount();
            });
        });
    </script>
    </body>
    </html>

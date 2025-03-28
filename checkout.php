<?php
session_start();

// Check if checkout items are set
if (!isset($_SESSION['checkout_items']) || empty($_SESSION['checkout_items'])) {
    header('Location: cart.php'); // Redirect back to cart if no items
    exit();
}   

// Fetch checkout items
$checkoutItems = $_SESSION['checkout_items'];
?>

<?php
// Count total products in order details
$totalProducts = 0;

if (!empty($checkoutItems)) {
    foreach ($checkoutItems as $item) {
        $totalProducts += $item['quantity']; // Add the quantity of each item
    }
}

// count total items in order details
$totalProducts = !empty($checkoutItems) ? count($checkoutItems) : 0;


$subtotal = 0; // Initialize subtotal
$shippingFee = 100.00; // Fixed shipping fee
$couponDiscount = 100.00; // Fixed coupon discount (if applicable)

// Calculate the subtotal based on the selected items
if (!empty($checkoutItems)) {
    foreach ($checkoutItems as $item) {
        $subtotal += $item['price'] * $item['quantity']; // Sum up item prices
    }
}

// Automatically add the shipping fee if the subtotal is greater than 0
$totalAmount = $subtotal + $shippingFee;



?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Mobile Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="./css/checkout.css">
</head>
<body>
  
<!-- Cart Section -->
<section class="h-100 h-custom" style="background-color: rgba(245, 245, 245, 0.9)">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-lg-10">
        <div style="height: auto;" class="card sm">
          <div class="card-body p-4 overflow-auto">
            <div class="row">
              <h5 class="mb-3">
                <a href="./cart.php" class="text-body">
                  <i class="bi bi-arrow-left me-2"></i>Back to Cart
                </a>
              </h5>
              <hr>
            </div>

            <main>  
              <div class="row g-5">
                <div class="col-md-5 col-lg-4 order-md-last">

                  <!-- Order Details -->
                  <div class="card mb-3">
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <h5 class="card-title">Order Details</h5>
                        <p class="mb-0">Total Products: (x<?php echo $totalProducts; ?>)</p> <!-- Display the total number of products -->
                        <hr>
                        <ul class="list-group list-group-flush">
                            <?php if (!empty($checkoutItems)): ?>
                                <?php foreach ($checkoutItems as $item): ?>
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col-6">
                                                <?php echo htmlspecialchars($item['name']) . ' (x' . htmlspecialchars($item['quantity']) . ')'; ?>
                                            </div>
                                            <div class="col-6 text-end">
                                                ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item text-center text-muted">No items selected.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Checkout Summary -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Checkout Summary</h5>
                        <ul class="list-group list-group-flush">
                            <!-- Subtotal -->
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-6">Subtotal</div>
                                    <div class="col-6 text-end" id="subtotal-amount">₱<?php echo number_format($subtotal, 2); ?></div>
                                </div>
                            </li>

                            <!-- Shipping Fee -->
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-6">Shipping</div>
                                    <div class="col-6 text-end" id="shipping-fee">₱<?php echo number_format($shippingFee, 2); ?></div>
                                </div>
                            </li>

                            <!-- Coupon Discount -->
                            <li class="list-group-item text-success">
                                <div class="row">
                                    <div class="col-6">Coupon</div>
                                    <div class="col-6 text-end" id="coupon-discount">-₱0.00</div>
                                </div>
                            </li>

                            <!-- Total Amount -->
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-6"><strong>Total Amount</strong></div>
                                    <div class="col-6 text-end" id="total-amount"><strong>₱<?php echo number_format($totalAmount, 2); ?></strong></div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>


                  <!-- Promo Code Section -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Coupon Code</h5>
                            <div class="input-group">
                                <input type="text" id="coupon-code" class="form-control" placeholder="Coupon Code">
                                <button class="btn btn-outline-primary apply-coupon" type="button">Apply</button>
                            </div>
                            <small id="couponMessage" class="form-text mt-2"></small>
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="col-md-7 col-lg-8">
                  <h4 class="mb-3">Billing Address</h4>
                  <form id="checkoutForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" placeholder="John" required>
                        <div class="invalid-feedback">First name is required.</div>
                      </div>

                      <div class="col-sm-6">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" placeholder="Doe" required>
                        <div class="invalid-feedback">Last name is required.</div>
                      </div>

                      <div class="col-12">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="you@example.com" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                      </div>

                      <div class="col-12">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" placeholder="1234 Main St" required>
                        <div class="invalid-feedback">Please enter your address.</div>
                      </div>

                      <div class="col-md-4">
                        <label for="country" class="form-label">Country</label>
                        <select class="form-select" id="country" required>
                          <option value="">Choose...</option>
                          <option>Philippines</option>
                        </select>
                        <div class="invalid-feedback">Please select a valid country.</div>
                      </div>

                      <div class="col-md-4">
                        <label for="state" class="form-label">State/Province</label>
                        <select class="form-select" id="state" required>
                          <option value="">Choose...</option>
                          <option>Metro Manila</option>
                          <option>Cebu</option>
                          <option>Davao</option>
                        </select>
                        <div class="invalid-feedback">Please select a valid state.</div>
                      </div>

                      <div class="col-md-4">
                        <label for="zip" class="form-label">Zip Code</label>
                        <input type="number" class="form-control" id="zip" placeholder="1234" required pattern="\d{4}">
                        <div class="invalid-feedback">Zip code must be 4 digits.</div>
                      </div>
                    </div>

                    <hr class="my-4">

                    <h4 class="mb-3">Payment</h4>
                    <div class="col-md-6">
                      <label for="money" class="form-label">Enter Your Money</label>
                      <input type="number" class="form-control" id="money" placeholder="100000" required min="0">
                      <div class="invalid-feedback">Please enter a valid amount.</div>
                    </div>

                    <hr class="my-4">

                    <button class="w-100 btn btn-primary btn-lg" type="submit">Place Order</button>
                  </form>
                </div>
              </div>
            </main>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
      

<!-- Modal -->

<!-- Insufficient Funds Modal -->
<!-- Insufficient Funds Modal -->
<div class="modal fade" id="insufficientFundsModal" tabindex="-1" aria-labelledby="insufficientFundsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="insufficientFundsModalLabel">Insufficient Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Message updated by JavaScript -->
                Insufficient funds!
            </div>
            <div class="modal-footer">
                <!-- Close Button at the Bottom -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Receipt Modal -->
<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="receiptModalLabel"><i class="bi bi-receipt-cutoff"></i> Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="text-success text-center pb-2">
                  <h3 ><i class="bi bi-check-circle-fill"> Thank you for Purchasing</i></h3>
                  <p>Nova Mobile</p>
              </div>
                <!-- Billing Address -->
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="bi bi-person-circle"></i> Billing Address</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong id="billingName"></strong></p>
                        <p class="mb-1" id="billingAddress"></p>
                        <p class="mb-1" id="billingCityCountry"></p>
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
              <button id="closeReceiptModal" class="btn btn-success w-100"><i class="bi bi-check-circle"></i> Close</button>
            </div>
        </div>
    </div>
</div>


  <!--This is Footer -->
  <div style="background-color:  rgba(245, 245, 245, 0.9);" class="container-fluid">
    <div style="padding: 2rem 0;">
      <footer  class="row row-cols-1 row-cols-sm-2 row-cols-md-5 py-5 my-0 border-top">
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
<script src="./js/checkout.js"></script>

</body>
</html>
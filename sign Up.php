<?php
require 'connection.php';

session_start();

if (isset($_SESSION['user_id'])) {
  // Redirect to user dashboard or another appropriate page
  header("Location: user.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Check if the email already exists
        $check_email_query = "SELECT id FROM tb_user WHERE email = :email";
        $stmt = $conn->prepare($check_email_query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email already exists
            $_SESSION['error_modal'] = "The email address is already registered. Please use a different email.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        // Insert the new user
        $insert_query = "INSERT INTO tb_user (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);

        if ($stmt->execute()) {
            // Set session variable to indicate successful registration
            $_SESSION['registration_successful'] = true;

            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // Handle error
            $_SESSION['error_message'] = "An error occurred. Please try again later.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } catch (PDOException $e) {
        // Handle database errors
        die("Database Error: " . $e->getMessage());
    }
}

// Display error message if set
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']); // Clear the error message
}

// Display Modal for registration successful
if (isset($_SESSION['registration_successful']) && $_SESSION['registration_successful']): ?>
  <!-- Modal -->
  <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="registrationModalLabel">Registration Successful!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Your account has been created successfully.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <a href="login.php" class="btn btn-primary">Go to Login</a>
        </div>
      </div>
    </div>
  </div>

  <script>
      // Display the registration success modal
      window.onload = function() {
          var myModal = new bootstrap.Modal(document.getElementById('registrationModal'));
          myModal.show();
      };
  </script>

  <?php 
  // Clear session after showing the modal to prevent it from showing again on reload
  unset($_SESSION['registration_successful']); 
endif; 

// Display Modal for duplicate email error
if (isset($_SESSION['error_modal'])): ?>
  <!-- Modal -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger" id="errorModalLabel">Registration Error</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php echo $_SESSION['error_modal']; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
      // Display the duplicate email error modal
      window.onload = function() {
          var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
          errorModal.show();
      };
  </script>

  <?php 
  // Clear the error modal session variable after showing the modal
  unset($_SESSION['error_modal']); 
endif;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Mobile Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <!-- This is Form fils -->
    <div class="container d-flex justify-content-center align-items-center mt-5" style="min-height: 80vh;">
      <div class="border border-white p-5 rounded-3 shadow-lg" style="background-color: #fff; max-width: 650px; width: 100%;">
          <form id="signUpForm" class="needs-validation" novalidate method="post" action="sign Up.php">
              <div>
                   <a href="./Landing Page/index.php">
                    <img class="mb-4" src="./img/logo/logo.png" alt="Logo" width="150" height="110">
                  </a>
                  <h1 class="h3 mb-3 fw-bold">Create Account</h1>
              </div>
              
              <div class="form-floating mb-4">
                  <input type="text" name="name" class="form-control form-control-lg" id="floatingName" placeholder="Name" required>
                  <label for="floatingName">Your Name</label>
                  <div class="invalid-feedback">Please enter your name.</div>
              </div>
  
              <div class="form-floating mb-4">
                  <input type="email" name="email" class="form-control form-control-lg" id="floatingEmail" placeholder="name@example.com" required>
                  <label for="floatingEmail">Email address</label>
                  <div class="invalid-feedback">Please provide a valid email address.</div>
              </div>
  
              <div class="form-floating mb-4">
                  <input type="password" name="password" class="form-control form-control-lg" id="floatingPassword" placeholder="Password" required>
                  <label for="floatingPassword">Password</label>
                  <div class="invalid-feedback">Password must be at least 8 characters, include one number and one special character.</div>
              </div>
  
              <div class="form-floating mb-4">
                  <input type="password" name="confirm_password" class="form-control form-control-lg" id="floatingConfirmPassword" placeholder="Re-enter Password" required>
                  <label for="floatingConfirmPassword">Re-enter Password</label>
                  <div class="invalid-feedback">Passwords must match.</div>
                  <p class="mt-4">Already have an account? <a href="./login.php" class="text-decoration-none">Sign in</a></p>
              </div>
  
              <button class="btn btn-primary w-100 py-3" type="submit">Sign Up</button>
              <p class="mt-4 mb-3 text-body-secondary text-center">© 2024</p>
          </form>
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

  

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="./js/sign Up.js"></script>
</body>
</html>
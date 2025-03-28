<?php
require 'connection.php';
session_start();

if (isset($_SESSION['user_id'])) {
    // Redirect to user dashboard or another appropriate page
    header("Location: user.php");
    exit();
}

// Define admin credentials
$adminEmail = "admin@example.com"; // Replace with your admin email
$adminPassword = "adminpassword"; // Replace with your admin password

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === $adminEmail && $password === $adminPassword) {
        // If admin email and password match, redirect to admin page
        $_SESSION['user_id'] = 0; // You can set a unique ID for admin
        $_SESSION['user_name'] = "Admin";
        $_SESSION['user_email'] = $adminEmail;

        header("Location: admin.php");
        exit();
    } else {
        // Check in the database for normal user credentials
        $sql = "SELECT * FROM tb_user WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            header("Location: user.php");
            exit();
        } else {
            $showModal = true; // Set the flag to true to show the modal
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Mobile Sign-In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"> <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="login.css">
</head>
<body>
  
<!-- This is Form fils -->
<div class="container d-flex justify-content-center align-items-center mt-5" style="min-height: 80vh;">
        <div class="border border-white p-5 rounded-3 shadow-lg" style="background-color: #fff; max-width: 650px; width: 100%;">
            <form method="post" action="login.php">
                <div>
                    <a href="./Landing Page/index.php">
                      <img class="mb-4" src="./img/logo/logo.png" alt="Logo" width="150" height="110">
                    </a>
                    <h1 class="h3 mb-3 fw-bold">Please Sign In</h1>
                </div>
    
                <div class="form-floating mb-4">
                    <input type="email" name="email" class="form-control form-control-lg" id="floatingInput" placeholder="name@example.com" required>
                    <label for="floatingInput">Email address</label>
                </div>
    
                <div class="form-floating mb-4">
                    <input type="password" name="password" class="form-control form-control-lg" id="floatingPassword" placeholder="Password" required>
                    <label for="floatingPassword">Password</label>
                    <p class="mt-4">Don't have an account yet? <a href="./sign Up.php" class="text-decoration-none">Register</a></p>
                </div>
    
                <button class="btn btn-primary w-100 py-3" type="submit">Sign in</button>
                <p class="mt-4 mb-3 text-body-secondary text-center">© 2024</p>
            </form>
        </div>
    </div>    
    
    <!-- Modal for Invalid Email or Password -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Login Failed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Invalid email or password. Please try again.
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
<script src="./js/login.js"></script>

<script>
        $(document).ready(function() {
            <?php if ($showModal): ?>
                $('#errorModal').modal('show');
            <?php endif; ?>
        });
    </script>

</body>
</html>
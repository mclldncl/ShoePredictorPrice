<?php
session_start();
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli("localhost", "root", "", "shoedb");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $fname = htmlspecialchars(trim($_POST["fname"]));
    $lname = htmlspecialchars(trim($_POST["lname"]));
    $email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST["message"]));

    if ($fname && $lname && $email && $message) {
        $stmt = $conn->prepare("INSERT INTO messages (fname, lname, email, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fname, $lname, $email, $message);
        if ($stmt->execute()) {
            $success = "✅ Your message has been submitted!";
        } else {
            $error = "❌ Failed to send message. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "❌ Please fill out all fields correctly.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Contact Us | ShoelyYours</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9f9f9;
    }
    .navbar {
      background-color: #fff;
      border-bottom: 1px solid #ddd;
    }
    .navbar-brand {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      font-weight: 700;
      color: #333;
    }
    .nav-link {
      text-transform: uppercase;
      font-weight: 500;
      color: #333 !important;
      margin-right: 1rem;
    }
    .section-title {
      font-size: 2rem;
      font-weight: 600;
      margin-bottom: 30px;
    }
    .info-box {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
    }
    .info-icon {
      font-size: 20px;
      color: #7b2cbf;
    }
    .contact-form input, .contact-form textarea {
      border-radius: 10px;
    }
    .contact-form .btn {
      background-color: #7b2cbf;
      color: white;
      border-radius: 30px;
      padding: 10px 30px;
      font-weight: 500;
    }
    .contact-form .btn:hover {
      background-color: #5e21a6;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand mx-auto" href="index.php">ShoelyYours</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center" id="mainNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="men.php">Men</a></li>
        <li class="nav-item"><a class="nav-link" href="women.php">Women</a></li>
        <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="btn btn-outline-danger ms-2" href="#" onclick="confirmLogout(event)">Logout</a>
</li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-outline-success me-2" href="user_register.php">Register</a></li>
          <li class="nav-item"><a class="btn btn-outline-primary" href="user_login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container" style="margin-top: 100px;">
  <div class="text-center mb-4">
    <h1 class="section-title">Contact Information</h1>
    <p class="text-muted">We’d love to hear from you</p>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success text-center"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger text-center"><?= $error ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Contact Details -->
    <div class="col-md-5">
      <address>
        <div class="info-box"><span class="info-icon">📍</span> Lucena City, Quezon Province</div>
        <div class="info-box"><span class="info-icon">📞</span> 0995-332-8830</div>
        <div class="info-box"><span class="info-icon">✉️</span> info@shoelyyours.com</div>
        <div class="info-box"><span class="info-icon">🌐</span> www.shoelyyours.com</div>
      </address>
    </div>

    <!-- Contact Form -->
    <div class="col-md-7">
      <form class="contact-form" method="post" action="contact.php" autocomplete="off" novalidate>
        <div class="row mb-3">
          <div class="col-md-6">
            <input type="text" class="form-control" name="fname" placeholder="First Name" required />
          </div>
          <div class="col-md-6">
            <input type="text" class="form-control" name="lname" placeholder="Last Name" required />
          </div>
        </div>
        <div class="mb-3">
          <input type="email" class="form-control" name="email" placeholder="Your Email" required />
        </div>
        <div class="mb-3">
          <textarea class="form-control" name="message" rows="5" placeholder="Your Message" required></textarea>
        </div>
        <button type="submit" class="btn">Send Message</button>
      </form>

      <div class="mt-4 text-end">
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">← Back to Home</a>
      </div>
    </div>
  </div>
</div>

<footer class="bg-dark text-white py-2 mt-5">
  <div class="container text-center">
    <p class="mb-1">&copy; <?= date('Y') ?> ShoelyYours. All rights reserved.</p>
    <small>
      <a href="contact.php" class="text-white text-decoration-none mx-2">Contact</a> |
      <a href="#" class="text-white text-decoration-none mx-2">Privacy</a> |
      <a href="#" class="text-white text-decoration-none mx-2">Terms</a>
    </small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
$cart_count = 0;

if (isset($_SESSION['user_id'])) {
    $conn = new mysqli("localhost", "root", "", "shoedb");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $uid = $_SESSION['user_id'];
    $result = $conn->query("SELECT SUM(quantity) AS total FROM cart_items WHERE user_id = $uid");
    if ($result) {
        $cart_count = $result->fetch_assoc()['total'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>ShoelyYours | Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Segoe UI', sans-serif; background-color: #f9f9f9; margin: 0; }
    .navbar { background-color: #fff; border-bottom: 1px solid #ddd; padding: 1rem; font-family: 'Poppins', sans-serif; }
    .navbar-brand { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #333; }
    .nav-link { font-weight: 500; text-transform: uppercase; color: #333 !important; margin-right: 1rem; }
    .nav-link:hover::after { width: 100%; }
    .btn-outline-primary, .btn-outline-success, .btn-outline-danger {
      border-radius: 30px; padding: 6px 18px; font-size: 0.9rem; font-family: 'Poppins', sans-serif;
    }

    .hero {
      position: relative; height: 85vh;
      background: url('images/men.jpg') center center/cover no-repeat;
      color: #fff; display: flex; align-items: center; justify-content: center; text-align: center;
    }

    .hero::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.55); z-index: 1;
    }

    .hero .content {
      position: relative; z-index: 2; max-width: 700px; padding: 30px;
    }

    .hero h1 { font-size: 3.5rem; font-weight: bold; font-family: 'Playfair Display', serif; }
    .hero h4 { font-weight: 300; font-size: 1.5rem; font-family: 'Poppins', sans-serif; }

    .shop-btn {
      background-color: #7b2cbf; color: #fff; padding: 12px 30px; font-weight: bold;
      border-radius: 30px; text-transform: uppercase; letter-spacing: 1px; text-decoration: none;
    }

    .shop-btn:hover { background-color: #5e21a6; }

    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: 2.5rem; margin: 60px 0 30px; text-align: center;
    }

    .product-card {
      background-color: #fff; border-radius: 12px; overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.06); transition: transform 0.3s ease;
    }

    .product-card:hover { transform: translateY(-5px); }
    .product-card img { width: 100%; height: 220px; object-fit: cover; }
    .product-info { padding: 15px; text-align: center; }
    .product-info h6 { font-weight: 500; margin: 0; }
    .product-info .price { color: #7b2cbf; font-weight: bold; margin-top: 5px; }

    @media (max-width: 768px) {
      .hero h1 { font-size: 2.3rem; }
      .hero h4 { font-size: 1.1rem; }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
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
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        <li class="nav-item">
  <a class="nav-link" href="orders.php">My Orders</a>
</li>
<li class="nav-item">
  <a class="nav-link" href="http://localhost:8503" target="_blank">Predict Price</a>
</li>


      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item d-flex align-items-center me-2">
            <span class="me-2 fw-semibold text-dark">👋 Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
          </li>
          <li class="nav-item me-2">
            <a href="cart.php" class="btn btn-outline-dark position-relative">
              🛒 Cart
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $cart_count ?>
              </span>
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-danger" href="logout.php" onclick="confirmLogout(event)">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-outline-success me-2" href="user_register.php">Register</a></li>
          <li class="nav-item"><a class="btn btn-outline-primary" href="user_login.php">User Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<section class="hero mt-5">
  <div class="content">
    <h1>MEN'S SHOES</h1>
    <h4>COLLECTION</h4>
    <p class="lead">Sole-mates for your every move</p>
    <a href="men.php" class="shop-btn">Shop Collection</a>
  </div>
</section>

<!-- Featured Products -->
<div class="container">
  <h2 class="section-title">Featured Styles</h2>
  <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
    <div class="col">
      <div class="product-card">
        <img src="images/nike air max.jpg" alt="Nike Air Max">
        <div class="product-info">
          <h6>Nike Air Max 90</h6>
          <div class="price">$149.99</div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="product-card">
        <img src="images/adidas ultraboost.jpg" alt="Adidas Ultraboost">
        <div class="product-info">
          <h6>Adidas Ultraboost 22</h6>
          <div class="price">$169.99</div>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="product-card">
        <img src="images/puma-classic.jpg" alt="Puma Classic">
        <div class="product-info">
          <h6>Puma Classic RS-X3</h6>
          <div class="price">$129.99</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Category Blocks -->
  <h2 class="section-title">Shop by Category</h2>
  <div class="row text-center g-4">
    <div class="col-md-4">
      <a href="men.php" class="text-decoration-none text-dark">
        <div class="product-card p-3">
          <img src="images/mens-shoes.jpg" alt="Men's Shoes" class="w-100 mb-3 rounded" style="height:300px; object-fit:cover;">
          <h5>Men's Shoes</h5>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="women.php" class="text-decoration-none text-dark">
        <div class="product-card p-3">
          <img src="images/womens-shoes.jpg" alt="Women's Shoes" class="w-100 mb-3 rounded" style="height:300px; object-fit:cover;">
          <h5>Women's Shoes</h5>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="#" class="text-decoration-none text-dark">
        <div class="product-card p-3">
          <img src="images/kids-shoes.jpg" alt="Kids Shoes" class="w-100 mb-3 rounded" style="height:300px; object-fit:cover;">
          <h5>Kids' Shoes</h5>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- Service Section -->
<div class="container py-5 bg-white rounded shadow-sm mb-5">
  <div class="row text-center">
    <div class="col-md-4 mb-3">
      <img src="images/free-shipping.jpg" width="60" alt="Free Shipping">
      <h6 class="mt-3">Free Shipping</h6>
      <p class="text-muted small">On all orders over $50</p>
    </div>
    <div class="col-md-4 mb-3">
      <img src="images/support.png" width="60" alt="24/7 Support">
      <h6 class="mt-3">24/7 Support</h6>
      <p class="text-muted small">We're here to help anytime</p>
    </div>
    <div class="col-md-4 mb-3">
      <img src="images/easy.png" width="60" alt="Easy Returns">
      <h6 class="mt-3">Easy Returns</h6>
      <p class="text-muted small">No-hassle 7-day return policy</p>
    </div>
  </div>
</div>

<!-- CTA -->
<section class="py-5 text-white" style="background-color: #7b2cbf;">
  <div class="container text-center">
    <h3 class="mb-3">Join the ShoelyYours Family</h3>
    <p>Get updates on latest arrivals and exclusive offers!</p>
    <a href="user_register.php" class="btn btn-light px-4 py-2 rounded-pill fw-bold">Create an Account</a>
  </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-2">
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
<script>
function confirmLogout(event) {
  event.preventDefault();
  if (confirm("Are you sure you want to log out?")) {
    window.location.href = "logout.php";
  }
}
</script>
</body>
</html>

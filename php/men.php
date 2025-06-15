<?php
session_start();
$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Cart count for navbar
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
  $uid = $_SESSION['user_id'];
  $res = $conn->query("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = $uid");
  if ($res && $row = $res->fetch_assoc()) {
    $cart_count = $row['total'] ?? 0;
  }
}

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total
$count_result = $conn->query("SELECT COUNT(*) AS total FROM products WHERE category = 'men'");
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch products
$stmt = $conn->prepare("SELECT * FROM products WHERE category = 'men' LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Men's Shoes | ShoelyYours</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f9f9f9; }

    .navbar {
      background-color: #fff;
      border-bottom: 1px solid #ddd;
      padding: 1rem 0.5rem;
    }
    .navbar-brand {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      font-weight: 700;
      color: #333;
    }
    .nav-link {
      font-weight: 500;
      text-transform: uppercase;
      color: #333 !important;
      margin-right: 1rem;
    }

    .hero {
      height: 80vh;
      background: url('images/men.jpg') center center/cover no-repeat;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }
    .hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.6);
      z-index: 1;
    }
    .hero .content {
      position: relative;
      z-index: 2;
      text-align: center;
    }
    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
    }

    .product-entry {
      background: #fff;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      transition: transform 0.3s ease;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .product-entry:hover {
      transform: translateY(-4px);
    }
    .product-img {
      width: 100%;
      height: 400px;
      object-fit: cover;
      border-radius: 8px;
    }
    .desc h5 {
      font-size: 1.1rem;
      margin-top: 10px;
    }
    .price {
      color: #7b2cbf;
      font-weight: 600;
    }

    .pagination .page-link {
      border-radius: 50px;
      color: #7b2cbf;
    }
    .pagination .page-item.active .page-link {
      background-color: #7b2cbf;
      color: white;
      border-color: #7b2cbf;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand mx-auto" href="index.php">ShoelyYours</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-center" id="mainNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-primary" href="men.php">Men</a></li>
        <li class="nav-item"><a class="nav-link" href="women.php">Women</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        <li class="nav-item">
  <a class="nav-link" href="orders.php">My Orders</a>
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
          <li class="nav-item"><a class="btn btn-outline-danger ms-2" href="#" onclick="confirmLogout(event)">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-outline-success me-2" href="user_register.php">Register</a></li>
          <li class="nav-item"><a class="btn btn-outline-primary" href="user_login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="hero mt-5">
  <div class="content">
    <h1>MEN'S COLLECTION</h1>
    <p class="lead">Step Up in Style</p>
  </div>
</section>

<!-- Product Grid -->
<div class="container my-5">
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    <?php while ($row = $products->fetch_assoc()): ?>
      <div class="col">
        <div class="product-entry">
          <img src="images/<?= htmlspecialchars($row['image']) ?>" class="product-img">
          <div class="desc text-center">
            <h5><?= htmlspecialchars($row['name']) ?></h5>
            <div class="price mb-2">$<?= number_format($row['price'], 2) ?></div>
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="add_to_cart.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">🛒 Add to Cart</a>
            <?php else: ?>
              <a href="user_login.php" class="btn btn-sm btn-outline-secondary disabled">🔒 Login to add</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- Pagination -->
  <nav class="mt-4">
    <ul class="pagination justify-content-center">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>

  <!-- Back to Home -->
  <div class="mt-4 text-end">
    <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">← Back to Home</a>
  </div>
</div>

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

<?php
$stmt->close();
$conn->close();
?>

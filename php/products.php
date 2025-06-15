<?php
session_start();
$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Cart count logic
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
  $uid = $_SESSION['user_id'];
  $res = $conn->query("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = $uid");
  if ($res && $row = $res->fetch_assoc()) {
    $cart_count = $row['total'] ?? 0;
  }
}

// Category filtering
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$whereClause = ($category === 'men' || $category === 'women') ? "WHERE category = '$category'" : "";

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total products
$count_query = "SELECT COUNT(*) AS total FROM products $whereClause";
$total = $conn->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch current page products
$stmt = $conn->prepare("SELECT * FROM products $whereClause LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Products | ShoelyYours</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
    .navbar { background-color: #fff; border-bottom: 1px solid #ddd; padding: 1rem; }
    .navbar-brand { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #333; }
    .nav-link { text-transform: uppercase; color: #333 !important; margin-right: 1rem; }

    .hero {
      height: 40vh;
      background: url('images/shoes.jpg') center center/cover no-repeat;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }
    .hero::before {
      content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.6);
    }
    .hero h1 {
      position: relative; z-index: 2;
      font-size: 3rem; font-weight: bold;
    }

    .filter-bar {
      margin-top: 2rem;
      text-align: center;
    }
    .filter-bar a {
      margin: 0 10px;
      text-decoration: none;
      padding: 8px 16px;
      border-radius: 20px;
      border: 1px solid #7b2cbf;
      color: #7b2cbf;
      transition: all 0.2s ease-in-out;
    }
    .filter-bar a.active, .filter-bar a:hover {
      background-color: #7b2cbf;
      color: #fff;
    }

    .product-entry {
      background: #fff;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
      min-height: 450px;
    }

    .product-img {
      width: 100%;
      height: 400px;
      object-fit: cover;
      border-radius: 10px;
    }

    .desc {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      text-align: center;
      margin-top: 15px;
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
        <li class="nav-item"><a class="nav-link fw-bold text-primary" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="men.php">Men</a></li>
        <li class="nav-item"><a class="nav-link" href="women.php">Women</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="orders.php">My Orders</a></li>
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
            <a class="btn btn-outline-danger" href="#" onclick="confirmLogout(event)">Logout</a>
          </li>
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
  <h1>All Shoes</h1>
</section>

<!-- Filters -->
<div class="container filter-bar">
  <a href="products.php?category=all" class="<?= $category === 'all' ? 'active' : '' ?>">All</a>
  <a href="products.php?category=men" class="<?= $category === 'men' ? 'active' : '' ?>">Men</a>
  <a href="products.php?category=women" class="<?= $category === 'women' ? 'active' : '' ?>">Women</a>
</div>

<!-- Products -->
<div class="container my-5">
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
    <?php while ($row = $products->fetch_assoc()): ?>
      <div class="col">
        <div class="product-entry">
          <img src="images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="product-img">
          <div class="desc mt-3">
            <h5 class="fw-semibold"><?= htmlspecialchars($row['name']) ?></h5>
            <div class="price mb-3 text-purple fw-bold">$<?= number_format($row['price'], 2) ?></div>
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="add_to_cart.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">🛒 Add to Cart</a>
            <?php else: ?>
              <a href="user_login.php" class="btn btn-sm btn-outline-secondary disabled" tabindex="-1" aria-disabled="true">
                🔒 Login required
              </a>
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
          <a class="page-link" href="?category=<?= $category ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>

  <div class="mt-4 text-end">
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">← Back to Home</a>
      </div>
    </div>
  </div>
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

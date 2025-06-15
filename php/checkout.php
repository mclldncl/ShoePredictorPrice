<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to checkout.'); window.location.href = 'user_login.php';</script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch cart items
$stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$grand_total = 0;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $grand_total += $row['price'] * $row['quantity'];
}

// Handle checkout
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['checkout'])) {
    if (!empty($cart_items)) {
        $conn->begin_transaction();

        try {
            // Insert into orders (now with username)
            $order_stmt = $conn->prepare("INSERT INTO orders (user_id, username, total, created_at) VALUES (?, ?, ?, NOW())");
            $order_stmt->bind_param("isd", $user_id, $username, $grand_total);
            $order_stmt->execute();
            $order_id = $order_stmt->insert_id;

            // Insert each item into order_items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $item_stmt->bind_param(
                    "iisdi",
                    $order_id,
                    $item['product_id'],
                    $item['name'],
                    $item['price'],
                    $item['quantity']
                );
                $item_stmt->execute();
            }

            // Clear cart
            $delete_stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $delete_stmt->bind_param("i", $user_id);
            $delete_stmt->execute();

            $conn->commit();

            $_SESSION['flash'] = "✅ Order placed successfully!";
            header("Location: orders.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('❌ Order failed. Please try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - ShoelyYours</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .checkout-card {
      background: white;
      border-radius: 10px;
      padding: 30px;
      margin-top: 60px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .btn-checkout {
      background-color: #7b2cbf;
      color: white;
      font-weight: bold;
    }
    .btn-checkout:hover {
      background-color: #6522ad;
    }
    .product-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 5px;
    }
    table.table-bordered {
      border: 2px solid #343a40;
    }
    .table-bordered th,
    .table-bordered td {
      border: 2px solid #343a40 !important;
    }
    .table th, .table td {
      vertical-align: middle !important;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="checkout-card">
    <h2 class="mb-4 text-center">🛍️ Checkout</h2>

    <?php if (empty($cart_items)): ?>
      <div class="alert alert-info text-center">Your cart is empty.</div>
      <div class="text-center">
        <a href="products.php" class="btn btn-primary rounded-pill">Browse Products</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead class="table-secondary">
            <tr>
              <th>Product</th>
              <th>Image</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart_items as $item): ?>
              <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><img src="images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-img"></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
            <tr class="fw-bold table-light">
              <td colspan="4" class="text-end">Grand Total:</td>
              <td>$<?= number_format($grand_total, 2) ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <form method="POST">
        <div class="text-end mt-3">
          <button type="submit" name="checkout" class="btn btn-checkout px-4">Confirm Checkout</button>
        </div>
      </form>
    <?php endif; ?>
  </div>

  <div class="mt-4 text-end">
    <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">← Back to Home</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>

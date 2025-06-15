<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to view your cart.'); window.location.href = 'user_login.php';</script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cart | ShoelyYours</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .product-img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>
<body class="bg-light">

<div class="container my-5">
  <h2 class="mb-4 text-center">🛒 Your Shopping Cart</h2>

  <?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>Product</th>
            <th>Image</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $grand_total = 0; ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php
              $total = $row['price'] * $row['quantity'];
              $grand_total += $total;
              $image_path = 'images/' . htmlspecialchars($row['image']);
              if (!file_exists($image_path)) $image_path = 'images/placeholder.png';
            ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><img src="<?= $image_path ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="product-img"></td>
              <td>$<?= number_format($row['price'], 2) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>$<?= number_format($total, 2) ?></td>
              <td>
                <form method="POST" action="remove_from_cart.php" onsubmit="return confirm('Remove this item from cart?');">
                  <input type="hidden" name="item_id" value="<?= $row['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">🗑 Remove</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          <tr class="fw-bold table-light">
            <td colspan="4" class="text-end">Grand Total:</td>
            <td colspan="2">$<?= number_format($grand_total, 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <a href="products.php" class="btn btn-secondary rounded-pill px-4">← Continue Shopping</a>
      <a href="checkout.php" class="btn btn-success rounded-pill px-4">Proceed to Checkout →</a>
    </div>

  <?php else: ?>
    <div class="alert alert-info text-center">Your cart is empty.</div>
    <div class="text-center">
      <a href="products.php" class="btn btn-primary rounded-pill">Browse Products</a>
    </div>
  <?php endif; ?>
  <div class="mt-4 text-end">
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">← Back to Home</a>
      </div>
    </div>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

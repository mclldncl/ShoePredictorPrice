<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$order_result = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders | ShoelyYours</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .order-card { background: #fff; border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    .order-title { font-weight: bold; color: #7b2cbf; }
    .badge-total { background-color: #7b2cbf; font-size: 0.9rem; }
    .btn-invoice { background-color: #5c3dac; color: #fff; font-size: 0.85rem; }
    .btn-invoice:hover { background-color: #482e92; }
  </style>
</head>
<body>

<div class="container my-5">

  <!-- Flash Notification -->
  <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
      <?= htmlspecialchars($_SESSION['flash']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <h2 class="mb-4">🧾 My Orders</h2>

  <?php if ($order_result->num_rows == 0): ?>
    <div class="alert alert-info">You have not placed any orders yet.</div>
    <a href="products.php" class="btn btn-primary">Shop Now</a>
  <?php else: ?>
    <?php $order_number = 1; ?>
    <?php while ($order = $order_result->fetch_assoc()): ?>
      <div class="order-card">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="order-title">Order #<?= $order_number ?></h5>
          <div class="d-flex align-items-center">
            <span class="badge badge-total text-white p-2 me-2">Total: $<?= number_format($order['total'], 2) ?></span>
            <a href="invoice.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-invoice">📄 Download Invoice</a>
          </div>
        </div>
        <small class="text-muted">Placed on: <?= date('F j, Y - h:i A', strtotime($order['created_at'])) ?></small>

        <table class="table mt-3">
          <thead class="table-light">
            <tr>
              <th>Item</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $oid = $order['id'];
              $items = $conn->query("SELECT * FROM order_items WHERE order_id = $oid");
              while ($item = $items->fetch_assoc()):
            ?>
              <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php $order_number++; ?>
    <?php endwhile; ?>
  <?php endif; ?>

  <div class="mt-4 text-end">
    <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">← Back to Home</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>

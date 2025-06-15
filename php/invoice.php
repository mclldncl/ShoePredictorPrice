<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid order ID.");
}

$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the logged-in user
$order_result = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_result->bind_param("ii", $order_id, $user_id);
$order_result->execute();
$order_data = $order_result->get_result()->fetch_assoc();

if (!$order_data) {
    die("Order not found or access denied.");
}

// Get order items
$items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice #<?= $order_id ?> | ShoelyYours</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; padding: 40px; }
    .invoice-box { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
    .invoice-title { font-size: 28px; font-weight: bold; color: #7b2cbf; margin-bottom: 20px; }
    .table th { background-color: #f0f0f0; }
    .total-row td { font-weight: bold; }
    .btn-print { background: #7b2cbf; color: white; }
    .btn-print:hover { background: #6522ad; }
  </style>
</head>
<body>

<div class="container invoice-box">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1 class="invoice-title">🧾 Invoice #<?= $order_id ?></h1>
      <p>Date: <?= date('F j, Y - h:i A', strtotime($order_data['created_at'])) ?></p>
    </div>
    <button class="btn btn-print" onclick="window.print()">🖨️ Print</button>
  </div>

  <hr>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Item Name</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($item = $items->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td>$<?= number_format($item['price'], 2) ?></td>
          <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
        </tr>
      <?php endwhile; ?>
      <tr class="total-row">
        <td colspan="3" class="text-end">Grand Total</td>
        <td>$<?= number_format($order_data['total'], 2) ?></td>
      </tr>
    </tbody>
  </table>

  <p class="text-muted mt-4 text-center">Thank you for shopping with ShoelyYours!</p>

    <div class="mt-4 text-end">
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">← Back to Home</a>
      </div>
    </div>
  </div>
</div>
</div>

</body>
</html>

<?php
$order_result->close();
$items_stmt->close();
$conn->close();
?>

<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "shoedb";

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM predictions ORDER BY created_at DESC";
$result = $conn->query($sql);

// Streamlit file uploads
$uploadDir = __DIR__ . "/uploads";
$files = file_exists($uploadDir) ? array_diff(scandir($uploadDir), ['.', '..']) : [];

$adminUser = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .container {
      margin-top: 40px;
    }
    h2 {
      color: #7b2cbf;
    }
    .header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 30px;
    }
    .table th {
      background-color: #7b2cbf;
      color: white;
    }
    .badge {
      font-size: 0.9rem;
    }
    .section-header {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 10px;
    }
    .logout-btn {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  </style>
</head>
<body>

<div class="container">

  <!-- Header -->
  <div class="header-bar">
    <h2>👟 Admin Dashboard</h2>
    <div class="text-end">
      <div class="text-muted small mb-1">Logged in as: <strong><?= htmlspecialchars($adminUser) ?></strong></div>
      <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
  </div>

  <!-- Prediction History -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">📊 Prediction History</div>
    <div class="card-body table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Brand</th>
            <th>Type</th>
            <th>Gender</th>
            <th>Material</th>
            <th>Size</th>
            <th>Predicted Price</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['brand']) ?></td>
              <td><?= htmlspecialchars($row['type']) ?></td>
              <td><?= htmlspecialchars($row['gender']) ?></td>
              <td><?= htmlspecialchars($row['material']) ?></td>
              <td><?= htmlspecialchars($row['size']) ?></td>
              <td><span class="badge bg-success">$<?= number_format($row['predicted_price'], 2) ?></span></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center text-muted">No predictions found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Uploaded CSV Files -->
  <div class="card shadow-sm">
    <div class="card-header bg-dark text-white">📁 Uploaded CSV Files</div>
    <div class="card-body table-responsive">
      <?php if (!empty($files)): ?>
        <table class="table table-sm table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Filename</th>
              <th>Uploaded At</th>
              <th>Download</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($files as $file): ?>
              <tr>
                <td><?= htmlspecialchars($file) ?></td>
                <td><?= date("Y-m-d H:i:s", filemtime($uploadDir . '/' . $file)) ?></td>
                <td>
                  <a href="uploads/<?= urlencode($file) ?>" class="btn btn-sm btn-primary" download>Download</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted">No CSV uploads found yet.</p>
      <?php endif; ?>
    </div>
  </div>

</div>

</body>
</html>

<?php $conn->close(); ?>

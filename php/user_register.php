<?php
session_start();
$error = $success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli("localhost", "root", "", "shoedb");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "⚠️ Username already taken.";
        } else {
            // Directly store plain password (⚠️ not recommended)
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);
            if ($stmt->execute()) {
                $success = "✅ Registration successful. You may now log in.";
            } else {
                $error = "❌ Failed to register.";
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f5f6fc;
      font-family: 'Segoe UI', sans-serif;
    }
    .register-card {
      max-width: 450px;
      margin: 80px auto;
      background: #fff;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    h3 {
      color: #7b2cbf;
      font-weight: bold;
    }
    .form-control {
      border-radius: 50px;
      background-color: #edf2ff;
      border: none;
      padding: 12px 20px;
    }
    .btn-register {
      background-color: #7b2cbf;
      color: white;
      border-radius: 50px;
      padding: 12px;
      font-weight: bold;
    }
    .btn-register:hover {
      background-color: #6522ad;
    }
    .text-small {
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<div class="register-card">
  <h3 class="text-center mb-4">📝 Register New User</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off" novalidate>
    <div class="mb-3">
      <input type="text" name="username" placeholder="Username" class="form-control" required autocomplete="off">
    </div>
    <div class="mb-3">
      <input type="password" name="password" placeholder="Password" class="form-control" required autocomplete="new-password">
    </div>
    <div class="mb-3">
      <input type="password" name="confirm_password" placeholder="Confirm Password" class="form-control" required autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-register w-100">Register</button>
    <div class="text-center mt-3 text-muted text-small">
      Already registered? <a href="user_login.php">Login here</a>
    </div>
  </form>
</div>

</body>
</html>

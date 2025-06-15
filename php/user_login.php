<?php
session_start();

// Redirect already-logged-in users
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli("localhost", "root", "", "shoedb");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
    $user = $res->fetch_assoc();

    // Plain text password check
    if ($user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "❌ Invalid password.";
    }
} else {
    $error = "⚠️ User not found.";
}


    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f5f6fc;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-card {
      background: #fff;
      border-radius: 15px;
      padding: 30px;
      max-width: 400px;
      margin: 80px auto;
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
    .btn-login {
      background-color: #7b2cbf;
      color: white;
      border-radius: 50px;
      padding: 12px;
      font-weight: bold;
    }
    .btn-login:hover {
      background-color: #6522ad;
    }
    .small-link {
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<div class="login-card">
  <h3 class="text-center mb-4">User Login</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off" class="card p-4 shadow">
  <div class="mb-3">
    <input type="text" name="username" id="username" class="form-control" placeholder="Username" required autocomplete="off">
  </div>
  <div class="mb-3">
    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required autocomplete="new-password">
  </div>
  <button type="submit" class="btn btn-login w-100">Login</button>
  <div class="text-center mt-3 small-link text-muted">
    Don’t have an account? <a href="user_register.php">Register</a>
  </div>
</form>

</div>

</body>
</html>

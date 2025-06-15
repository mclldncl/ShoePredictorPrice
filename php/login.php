<?php
session_start();
$error = "";

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $conn = new mysqli("localhost", "root", "", "shoedb");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $admin = $res->fetch_assoc();

        // ✅ Plain password match (no hashing)
        if ($password === $admin['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: admin.php");
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "⚠️ Admin account not found.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f4f7;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 80px auto;
        }
        .card {
            padding: 2rem;
            border-radius: 10px;
        }
        .btn-primary {
            background-color: #7b2cbf;
            border: none;
        }
        .btn-primary:hover {
            background-color: #5a189a;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(123, 44, 191, 0.25);
        }
        .text-center small {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container login-container">
    <div class="card shadow">
        <h4 class="text-center mb-3 text-primary">👟 Admin Login</h4>
        <p class="text-center small">Please enter your credentials to continue.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" autocomplete="off" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">🔐 Login</button>
        </form>
    </div>
</div>

</body>
</html>

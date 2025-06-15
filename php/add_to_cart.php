<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('You must be logged in to add items to cart.');
        window.location.href = 'user_login.php';
    </script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('❌ Invalid product ID.'); window.location.href = 'products.php';</script>";
    exit();
}

$product_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

// Fetch product details
$product_stmt = $conn->prepare("SELECT name, price, image FROM products WHERE id = ?");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
$product = $product_result->fetch_assoc();
$product_stmt->close();

if (!$product) {
    echo "<script>alert('❌ Product not found.'); window.location.href = 'products.php';</script>";
    exit();
}

// Check if product already exists in user's cart
$check_stmt = $conn->prepare("SELECT id FROM cart_items WHERE product_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $product_id, $user_id);
$check_stmt->execute();
$existing = $check_stmt->get_result();
$check_stmt->close();

if ($existing->num_rows > 0) {
    // Increase quantity by 1
    $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE product_id = ? AND user_id = ?");
    $update_stmt->bind_param("ii", $product_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();
} else {
    // Insert new item into cart
    $insert_stmt = $conn->prepare("INSERT INTO cart_items (product_id, name, price, image, quantity, user_id) VALUES (?, ?, ?, ?, 1, ?)");
    $insert_stmt->bind_param(
        "isdsi",
        $product_id,
        $product['name'],
        $product['price'],
        $product['image'],
        $user_id
    );
    $insert_stmt->execute();
    $insert_stmt->close();
}

$conn->close();

// Optionally set a session flash message to show in cart
// $_SESSION['message'] = "🛒 Product added to cart.";

header("Location: cart.php");
exit();
?>

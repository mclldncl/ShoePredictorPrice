<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['item_id'])) {
    header("Location: cart.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$item_id = intval($_POST['item_id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();

$conn->close();
header("Location: cart.php");
exit();

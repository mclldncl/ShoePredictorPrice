<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "shoedb";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Include id
$sql = "SELECT id, name, price, image FROM products LIMIT 8";
$result = $conn->query($sql);

$products = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($products);
$conn->close();
?>

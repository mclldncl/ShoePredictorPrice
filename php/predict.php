<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "shoedb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data from form
$brand = $_POST['brand'];
$type = $_POST['type'];
$gender = $_POST['gender'];
$material = $_POST['material'];
$size = floatval($_POST['size']);

// OPTIONAL: Call ML model via API (if you set it up with Flask)
$predicted_price = 0.0;
$data = [
    "Brand" => $brand,
    "Type" => $type,
    "Gender" => $gender,
    "Material" => $material,
    "Size" => $size
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => json_encode($data),
    ]
];
$context = stream_context_create($options);
$response = file_get_contents('http://localhost:5001/predict', false, $context);

if ($response !== FALSE) {
    $result = json_decode($response, true);
    $predicted_price = floatval($result['predicted_price']);
}

// Save to database
$stmt = $conn->prepare("INSERT INTO predictions (brand, type, gender, material, size, predicted_price) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssdd", $brand, $type, $gender, $material, $size, $predicted_price);
$stmt->execute();

echo "Prediction saved. Estimated price: $ " . number_format($predicted_price, 2);

$conn->close();
?>

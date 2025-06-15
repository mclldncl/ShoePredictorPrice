<?php
$conn = new mysqli("localhost", "root", "", "shoedb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM predictions ORDER BY created_at DESC");

echo "<table class='table table-bordered table-striped'>";
echo "<thead><tr>
        <th>#</th>
        <th>Brand</th>
        <th>Type</th>
        <th>Gender</th>
        <th>Material</th>
        <th>Size</th>
        <th>Predicted Price</th>
        <th>Date</th>
      </tr></thead><tbody>";

$count = 1;
while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$count}</td>
            <td>{$row['brand']}</td>
            <td>{$row['type']}</td>
            <td>{$row['gender']}</td>
            <td>{$row['material']}</td>
            <td>{$row['size']}</td>
            <td>$ {$row['predicted_price']}</td>
            <td>{$row['created_at']}</td>
          </tr>";
    $count++;
}
echo "</tbody></table>";

$conn->close();
?>

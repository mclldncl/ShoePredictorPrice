$result = $conn->query("SELECT * FROM predictions ORDER BY created_at DESC");
while($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['brand']}</td><td>{$row['type']}</td><td>{$row['size']}</td><td>{$row['predicted_price']}</td></tr>";
}

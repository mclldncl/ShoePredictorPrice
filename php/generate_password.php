<?php
$password = "admin123"; // Replace this with your actual desired password
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hash;
?>

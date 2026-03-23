<?php
require_once 'includes/config.php';

$conn = getDBConnection();
$newPassword = password_hash('Admin@123', PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@library.com'");
$stmt->bind_param("s", $newPassword);
$stmt->execute();
echo "Password updated successfully! Hash: " . $newPassword;
$conn->close();
?>
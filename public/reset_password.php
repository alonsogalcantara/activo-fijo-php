<?php
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Models/User.php';

use Models\User;

echo "<h1>Reset Password Script</h1>";

$email = isset($_GET['email']) ? $_GET['email'] : 'admin@sistema.local';
$new_password = isset($_GET['new_password']) ? $_GET['new_password'] : 'admin123';

echo "Updating password for Email: " . htmlspecialchars($email) . "<br>";
echo "New Password: " . htmlspecialchars($new_password) . "<br><br>";

$db = new Database();
$conn = $db->connect();

// 1. Check if user exists
$query = "SELECT * FROM users WHERE email = :email LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✅ User found!<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Current Hash: " . $user['password_hash'] . "<br>";
    
    // 2. Hash New Password
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    echo "New Hash Generated: $new_hash<br>";
    
    // 3. Update Database
    $update_query = "UPDATE users SET password_hash = :hash WHERE id = :id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':hash', $new_hash);
    $update_stmt->bindParam(':id', $user['id']);
    
    if ($update_stmt->execute()) {
        echo "<h2 style='color:green'>✅ Password Updated Successfully!</h2>";
        echo "You can now login with: <strong>$new_password</strong>";
    } else {
        echo "<h2 style='color:red'>❌ Failed to update password.</h2>";
    }
    
} else {
    echo "<h2 style='color:orange'>❌ User not found with that email.</h2>";
}

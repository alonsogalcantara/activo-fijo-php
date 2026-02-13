<?php
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Models/User.php';

use Models\User;

echo "<h1>Auth Debug Script</h1>";

$email = isset($_GET['email']) ? $_GET['email'] : 'test@example.com';
$password = isset($_GET['password']) ? $_GET['password'] : 'password';

echo "Testing with Email: " . htmlspecialchars($email) . "<br>";
echo "Testing with Password: " . htmlspecialchars($password) . "<br><br>";

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
    echo "Name: " . $user['name'] . "<br>";
    echo "Stored Hash: " . $user['password_hash'] . "<br>";
    
    // 2. Verify Password
    if (password_verify($password, $user['password_hash'])) {
        echo "<h2 style='color:green'>✅ Password Match!</h2>";
    } else {
        echo "<h2 style='color:red'>❌ Password Mismatch</h2>";
        
        // Debugging info for hashing
        echo "Entered Password: $password<br>";
        echo "Hash of Entered Password: " . password_hash($password, PASSWORD_DEFAULT) . "<br>";
    }
} else {
    echo "<h2 style='color:orange'>❌ User not found with that email.</h2>";
}

<?php
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die("❌ .env file not found at $envPath");
}

echo "✅ .env file found.<br>";

$env = parse_ini_file($envPath);

if (!$env) {
    die("❌ Failed to parse .env file.");
}

echo "<h3>Parsed Configuration:</h3>";
echo "<pre>";
foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $key) {
    $val = isset($env[$key]) ? $env[$key] : 'NOT SET';
    // Mask password
    if ($key === 'DB_PASS' && $val !== 'NOT SET') {
        $val = substr($val, 0, 2) . '******' . substr($val, -2) . " (Length: " . strlen($env[$key]) . ")";
    }
    echo "$key: [" . htmlspecialchars($val) . "]<br>";
}
echo "</pre>";

// trimming simple quotes if present, just to check if that's the issue
$host = trim($env['DB_HOST'], "'\"");
$db_name = trim($env['DB_NAME'], "'\"");
$username = trim($env['DB_USER'], "'\"");
$password = trim($env['DB_PASS'], "'\"");

echo "<h3>Attempting Connection (Cleaned Values):</h3>";
echo "Host: $host<br>";
echo "DB: $db_name<br>";
echo "User: $username<br>";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8");
    echo "<h2 style='color:green'>✅ Connection Successful!</h2>";
} catch(PDOException $exception) {
    echo "<h2 style='color:red'>❌ Connection Failed:</h2>";
    echo "Error: " . $exception->getMessage();
}

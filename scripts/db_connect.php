<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables from the current directory
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, '"\'');
            
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

$connectionString = getenv('DATABASE_URL');

if (!$connectionString) {
    die("❌ DATABASE_URL environment variable is not set");
}

try {
    $pdo = new PDO($connectionString);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("✅ Database connected successfully");
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>
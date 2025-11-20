<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Page</h1>";
echo "<p>If you can see this, PHP is working.</p>";

// Test database connection
require 'db_connect.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Database connection successful! Users in database: " . $result['user_count'] . "</p>";
} catch (Exception $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}

// Test session
session_start();
$_SESSION['test'] = 'Session is working!';
echo "<p>Session test: " . $_SESSION['test'] . "</p>";
?>
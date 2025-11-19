<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session at the very beginning
session_start();

require 'db_connect.php';

// Log the request for debugging
error_log("Login.php accessed. Method: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received");
    
    $email = trim($_POST['email'] ?? '');
    error_log("Email received: " . $email);
    
    if (empty($email)) {
        error_log("Empty email");
        header('Location: ../login.html?error=empty_email');
        exit();
    }
    
    try {
        // Check if user exists with this email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            error_log("User found: " . $user['email']);
            
            // Login successful - set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_data'] = $user;
            $_SESSION['logged_in'] = true;
            
            error_log("Redirecting to userdetails.php");
            
            // Redirect to userdetails
            header('Location: userdetails.php');
            exit();
        } else {
            error_log("User not found with email: " . $email);
            header('Location: ../login.html?error=invalid_email');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Location: ../login.html?error=server_error');
        exit();
    }
} else {
    // If not POST request, redirect to login
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header('Location: ../login.html');
    exit();
}
?>
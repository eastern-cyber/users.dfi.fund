<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration for XAMPP MySQL
$host = 'localhost';
$dbname = 'dfi_fund';
$username = 'root';
$password = '';

// Function to send JSON response
function sendResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('c')
    ]);
    exit();
}

// Function to sanitize user data (remove sensitive fields)
function sanitizeUserData($user) {
    unset($user['password_hash']);
    if ($user['plan_a']) {
        $user['plan_a'] = json_decode($user['plan_a'], true);
    }
    return $user;
}

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Get all users or specific user
            if (isset($_GET['email'])) {
                // Get user by email
                $email = $_GET['email'];
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $user = sanitizeUserData($user);
                    sendResponse(true, $user, 'User found');
                } else {
                    sendResponse(false, null, 'User not found', 404);
                }
            } else {
                // Get all users (without sensitive data)
                $stmt = $pdo->query("SELECT id, name, email, user_id, referrer_id, token_id, created_at, updated_at, plan_a FROM users");
                $users = $stmt->fetchAll();
                
                // Convert plan_a from JSON string to object for each user
                foreach ($users as &$user) {
                    if ($user['plan_a']) {
                        $user['plan_a'] = json_decode($user['plan_a'], true);
                    }
                }
                
                sendResponse(true, $users, 'Users retrieved successfully');
            }
            break;
            
        case 'POST':
            // Handle login validation
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                sendResponse(false, null, 'Invalid JSON input', 400);
            }
            
            if (isset($input['email'])) {
                // Login validation
                $email = $input['email'];
                $password = $input['password'] ?? '';
                
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // For demo purposes, we're accepting any password
                    // In production, you would verify with: password_verify($password, $user['password_hash'])
                    
                    $user = sanitizeUserData($user);
                    
                    sendResponse(true, $user, 'Login successful');
                } else {
                    sendResponse(false, null, 'User not found', 404);
                }
            } else {
                sendResponse(false, null, 'Email is required', 400);
            }
            break;
            
        default:
            sendResponse(false, null, 'Method not allowed', 405);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    sendResponse(false, null, 'Database connection failed', 500);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    sendResponse(false, null, 'Server error', 500);
}
?>
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and check login
session_start();
require 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: ../login.html');
    exit();
}

// Get fresh user data from database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // User not found in database, logout
        session_destroy();
        header('Location: ../login.html?error=user_not_found');
        exit();
    }
    
    // Update session with fresh data
    $_SESSION['user_data'] = $user;
    
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Details - DProject</title>
  <link rel="stylesheet" href="../style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" rel="stylesheet">
  <style>
    /* Your existing CSS styles here */
    /* Background solution */
    .login {
        position: relative;
        min-height: 100vh;
        display: grid;
        align-items: start;
        padding: 2rem 0;
        background-image: url('../images/login-bg.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .login__img {
        display: none;
    }

    /* Add dark overlay to improve readability */
    .login::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 0;
    }

    .user-details-container {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
        z-index: 1;
    }
    
    .user-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid rgba(255,255,255,0.2);
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .logo__img {
        width: 54px;
        height: 54px;
        border-radius: 8px;
        object-fit: cover;
        object-position: center;
        position: static;
        margin: 0;
    }
    
    .user-card {
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        transition: transform 0.3s ease;
        color: white;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }

    .user-card:hover {
        transform: translateY(-5px);
        background: rgba(0, 0, 0, 0.7);
    }
    
    .logout-btn {
        background: rgba(255,255,255,0.1);
        color: white;
        border: 2px solid rgba(255,255,255,0.3);
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }
    
    .logout-btn:hover {
        background: white;
        color: black;
        border-color: white;
    }
    
    .user-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .user-id {
        font-family: monospace;
        background: rgba(255, 255, 255, 0.1);
        padding: 0.5rem;
        border-radius: 0.5rem;
        word-break: break-all;
        font-size: 0.9rem;
        display: block;
        margin-top: 0.5rem;
        color: white;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        color: white;
    }
    
    .card-icon {
        font-size: 2rem;
        margin-right: 1rem;
        color: #4cc9f0;
    }
    
    .welcome-section {
        text-align: center;
        margin-bottom: 3rem;
        color: white;
    }
    
    .welcome-section h2 {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        background: linear-gradient(45deg, #4cc9f0, #4361ee);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        color: transparent;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    
    .action-btn {
        background: rgba(255,255,255,0.08);
        color: white;
        border: 1px solid rgba(255,255,255,0.15);
        padding: 1rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: inherit;
        font-size: 0.9rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        backdrop-filter: blur(10px);
    }
    
    .action-btn:hover {
        background: rgba(255,255,255,0.15);
        transform: translateY(-2px);
        border-color: rgba(255,255,255,0.3);
    }
    
    .action-btn i {
        font-size: 1.5rem;
    }
    
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    /* Ensure all text is visible with better contrast */
    .user-card p,
    .user-card strong,
    .user-card span:not(.user-id) {
        color: rgba(255, 255, 255, 0.95);
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
    }
    
    .user-card h3 {
        color: white;
        margin: 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }

    /* Status colors with better visibility */
    .status-active {
        color: #51cf66 !important;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
    }
    
    .status-inactive {
        color: #ff6b6b !important;
        font-weight: 600;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
    }
  </style>
</head>
<body>
  <div class="login">
    <img src="../images/login-bg.png" alt="background image" class="login__img">
    <div class="user-details-container">
      <div class="user-header">
        <div class="header-left">
          <img src="../images/favicon.ico" alt="DProject Logo" class="logo__img">
          <h1 style="font-size: 2.5rem; background: linear-gradient(45deg, #ff9a9e, #fad0c4); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; color: transparent;">
            Dashboard
          </h1>
        </div>
        <a href="logout.php" class="logout-btn">
          <i class="ri-logout-box-r-line"></i> Logout
        </a>
      </div>
      
      <div class="welcome-section">
        <h2 id="welcomeMessage">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h2>
        <p>ข้อมูลสมาชิก</p>
      </div>

      <div class="user-grid">
        <div class="user-card">
          <div class="card-header">
            <i class="ri-user-3-line card-icon"></i>
            <h3>Personal Information</h3>
          </div>
          <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
          <p><strong>User ID:</strong> <span class="user-id"><?php echo htmlspecialchars($user['user_id']); ?></span></p>
        </div>

        <div class="user-card">
          <div class="card-header">
            <i class="ri-time-line card-icon"></i>
            <h3>Account Timeline</h3>
          </div>
          <p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
          <p><strong>Referrer ID:</strong> <span class="user-id"><?php echo htmlspecialchars($user['referrer_id'] ?? 'None'); ?></span></p>
        </div>

        <div class="user-card">
          <div class="card-header">
            <i class="ri-shield-keyhole-line card-icon"></i>
            <h3>Security</h3>
          </div>
          <p><strong>Password:</strong> <span class="status-inactive"><?php echo empty($user['password_hash']) ? 'Not Set' : 'Set'; ?></span></p>
          <p><strong>Status:</strong> <span class="status-active">Active</span></p>
        </div>
      </div>

      <div class="user-grid">
        <div class="user-card">
          <div class="card-header">
            <i class="ri-token-swap-line card-icon"></i>
            <h3>Token Information</h3>
          </div>
          <p><strong>Token ID:</strong> <?php echo htmlspecialchars($user['token_id'] ?? 'Not assigned'); ?></p>
          <p><strong>Token Balance:</strong> <span id="tokenBalance">0.00</span></p>
        </div>

        <div class="user-card">
          <div class="card-header">
            <i class="ri-team-line card-icon"></i>
            <h3>Referral Program</h3>
          </div>
          <p><strong>Referrals:</strong> <span id="referralCount">0</span></p>
          <p><strong>Referral Code:</strong> <span class="user-id"><?php echo substr($user['user_id'], 0, 16) . '...'; ?></span></p>
        </div>
      </div>

      <div class="user-card">
        <div class="card-header">
          <i class="ri-flashlight-line card-icon"></i>
          <h3>Quick Actions</h3>
        </div>
        <div class="actions-grid">
          <button class="action-btn" onclick="showProfile()">
            <i class="ri-user-settings-line"></i>
            <span>Edit Profile</span>
          </button>
          <button class="action-btn" onclick="showReferrals()">
            <i class="ri-share-line"></i>
            <span>Share Referral</span>
          </button>
          <button class="action-btn" onclick="showTokens()">
            <i class="ri-coins-line"></i>
            <span>View Tokens</span>
          </button>
        </div>
      </div>

      <div class="user-card">
        <div class="card-header">
          <i class="ri-settings-5-line card-icon"></i>
          <h3>Plan Details</h3>
        </div>
        <div id="planDetails">
          <?php
          if (!empty($user['plan_a'])) {
              $planData = json_decode($user['plan_a'], true);
              if ($planData) {
                  echo '<p><strong>Plan:</strong> ' . htmlspecialchars($planData['plan'] ?? 'Unknown') . '</p>';
                  echo '<p><strong>Status:</strong> <span class="status-active">' . htmlspecialchars($planData['status'] ?? 'active') . '</span></p>';
                  if (isset($planData['features'])) {
                      echo '<p><strong>Features:</strong> ' . htmlspecialchars(implode(', ', $planData['features'])) . '</p>';
                  }
                  if (isset($planData['expires'])) {
                      echo '<p><strong>Expires:</strong> ' . htmlspecialchars($planData['expires']) . '</p>';
                  }
              } else {
                  echo '<p>No plan information available</p>';
              }
          } else {
              echo '<p>No plan information available</p>';
          }
          ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    function showProfile() {
      alert('Profile editing would open here in a real application');
    }
    
    function showReferrals() {
      const userId = '<?php echo $user['user_id']; ?>';
      alert(`Share your referral link: https://dfi.fund/ref/${userId}`);
    }
    
    function showTokens() {
      alert('Token management dashboard would open here');
    }
    
    // Calculate days since join for referral count
    document.addEventListener('DOMContentLoaded', function() {
      const joinDate = new Date('<?php echo $user['created_at']; ?>');
      const daysSinceJoin = Math.floor((new Date() - joinDate) / (1000 * 60 * 60 * 24));
      document.getElementById('referralCount').textContent = Math.floor(daysSinceJoin / 30);
    });
  </script>
</body>
</html>
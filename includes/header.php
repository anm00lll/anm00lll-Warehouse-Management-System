<?php
  /**
   * This is the global header file for the application.
   */

  // Start the session if it's not already started.
  if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }
  
  // Use __DIR__ to create a reliable, absolute path to the config file.
  // This ensures it's always found, no matter where the header is included from.
  require_once(__DIR__ . '/config.php');

  // Automatic Logout for Inactivity
  $inactive_timeout = 1800; // 30 minutes
  if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_timeout)) {
      session_unset();
      session_destroy();
      header("Location: " . BASE_URL . "login.php?reason=inactive");
      exit();
  }
  $_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Management System</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main Stylesheet (uses BASE_URL from config.php) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-warehouse"></i> WMS</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <!-- All links use BASE_URL to ensure they work from any page -->
                <li><a href="<?php echo BASE_URL; ?>home.php"><i class="fas fa-tachometer-alt nav-icon"></i> Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>products.php"><i class="fas fa-box-open nav-icon"></i> Products</a></li>
                <li><a href="<?php echo BASE_URL; ?>sales.php"><i class="fas fa-shopping-cart nav-icon"></i> Sales</a></li>
                <li><a href="<?php echo BASE_URL; ?>suppliers.php"><i class="fas fa-truck nav-icon"></i> Suppliers</a></li>
                <?php if (isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1): ?>
                <li><a href="<?php echo BASE_URL; ?>users.php"><i class="fas fa-users nav-icon"></i> Users</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content Wrapper (closed in footer.php) -->
    <main class="main-content">

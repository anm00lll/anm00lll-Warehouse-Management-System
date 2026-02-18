 <?php
  // Start the session to check for login status and store potential error messages.
  session_start();

  // Include necessary files.
  require_once('includes/functions.php');

  // --- Redirect if already logged in ---
  // If the user already has an active session, redirect them to the dashboard.
  if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
  }

  // We will include a simplified header for the login page that doesn't have the main navigation.
  // For now, we'll assume a generic header is used.
  include_once('includes/header.php');
?>

<div class="login-container">
    <div class="login-box">
        <div class="login-logo">
            <!-- You can replace this with your logo image -->
            <i class="fas fa-warehouse"></i>
            <h2>WMS Login</h2>
            <p>Welcome back! Please log in to your account.</p>
        </div>

        <?php
          // --- Display Login Errors ---
          // Check if there's a login error message in the session and display it.
          if (isset($_SESSION['login_error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
            // Unset the error message so it doesn't show again on refresh.
            unset($_SESSION['login_error']);
          }
        ?>

        <!-- Login Form -->
        <form method="post" action="_actions/process_login.php" class="login-form">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-icon"><i class="fas fa-user"></i></span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</div>

<!-- We can add some specific styles for the login page here or in the main CSS file -->
<style>
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh; /* Adjust to fit within header/footer */
        background-color: var(--background-color, #f4f7fa);
    }
    .login-box {
        width: 100%;
        max-width: 400px;
        padding: 40px;
        background-color: #fff;
        border-radius: var(--border-radius, 12px);
        box-shadow: var(--shadow, 0 4px 12px rgba(0,0,0,0.08));
        text-align: center;
    }
    .login-logo {
        margin-bottom: 30px;
    }
    .login-logo .fa-warehouse {
        font-size: 3rem;
        color: var(--primary-color, #4A90E2);
        margin-bottom: 10px;
    }
    .login-logo h2 {
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .login-logo p {
        color: var(--subtle-text-color, #777);
    }
    .login-form .form-group {
        margin-bottom: 20px;
        text-align: left;
    }
    .login-form .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    .login-form .input-group {
        position: relative;
    }
    .login-form .input-group-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #ccc;
    }
    .login-form .form-control {
        width: 100%;
        padding: 12px 12px 12px 40px; /* Add padding for icon */
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
    }
    .btn-login {
        width: 100%;
        padding: 12px;
        background-color: var(--primary-color, #4A90E2);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .btn-login:hover {
        background-color: #3a7bc8;
    }
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
</style>

<?php
  // Include the footer file.
  include_once('includes/footer.php');
?>


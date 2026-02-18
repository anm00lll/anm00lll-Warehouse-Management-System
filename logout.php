 <?php
  /**
   * This script handles the user logout process.
   * It destroys the current session and redirects the user to the login page.
   */

  // Always start the session to access and manipulate session data.
  session_start();

  // 1. Unset all of the session variables.
  // $_SESSION = array(); is a comprehensive way to clear all session data.
  $_SESSION = array();

  // 2. Destroy the session.
  // This will remove the session data from the server.
  session_destroy();

  // 3. Redirect to the login page.
  // After the session is destroyed, the user is no longer authenticated.
  header("Location: login.php");

  // 4. Ensure no further code is executed after the redirect.
  exit();
?>


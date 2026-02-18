 <?php
  /**
   * This is the main entry point of the application.
   * It checks for an active user session and redirects the user accordingly.
   *
   * - If a session exists and the user is logged in, they are redirected to home.php.
   * - If no session exists or the user is not logged in, they are redirected to login.php.
   */

  // Start the session to check for login status.
  // Note: In a more advanced setup, this would be part of an initialization file.
  session_start();

  // Check if the 'user_id' session variable is set.
  // This variable should be set upon successful login.
  if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to the main dashboard.
    header('Location: home.php');
    exit(); // Always call exit() after a header redirect.
  } else {
    // User is not logged in, redirect to the login page.
    header('Location: login.php');
    exit();
  }
?>


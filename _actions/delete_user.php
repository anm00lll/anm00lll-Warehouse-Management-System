 <?php
  session_start();
  require_once('../includes/functions.php');
  require_once('../includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
  }

  // Role-based protection: Only Admins (level 1) can delete users.
  if ($_SESSION['user_level'] != 1) { 
      $_SESSION['error'] = "You do not have permission to perform this action.";
      header('Location: ../users.php');
      exit();
  }

  // 1. Validate that a numeric user ID is provided via GET request
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      $_SESSION['error'] = "Invalid request. User ID not specified.";
      header('Location: ../users.php');
      exit();
  }

  $user_id_to_delete = (int)$_GET['id'];

  // 2. Prevent an admin from deleting their own account
  if ($user_id_to_delete == $_SESSION['user_id']) {
      $_SESSION['error'] = "You cannot delete your own account.";
      header('Location: ../users.php');
      exit();
  }

  // Note: Deleting a user might have cascading effects if there are foreign keys
  // in other tables (like 'sales') that reference the user ID. 
  // Our schema uses ON DELETE CASCADE, so related sales records will also be deleted.

  // 3. Prepare and execute the DELETE statement
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id_to_delete);

  if ($stmt->execute()) {
      // Check if any rows were actually deleted
      if ($stmt->affected_rows > 0) {
          $_SESSION['message'] = "User has been deleted successfully!";
      } else {
          $_SESSION['error'] = "User not found or already deleted.";
      }
  } else {
      $_SESSION['error'] = "Failed to delete user.";
  }

  $stmt->close();
  $conn->close();

  // 4. Redirect the user back to the users page
  header('Location: ../users.php');
  exit();
?>


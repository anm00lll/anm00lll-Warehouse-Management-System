 <?php
  session_start();
  require_once('../includes/functions.php');
  require_once('../includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
  }

  // Role-based protection: Allow only Admins (1) and Managers (2) to delete
  if ($_SESSION['user_level'] > 2) { 
      $_SESSION['error'] = "You do not have permission to perform this action.";
      header('Location: ../suppliers.php');
      exit();
  }

  // 1. Validate that a numeric supplier ID is provided via GET request
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      $_SESSION['error'] = "Invalid request. Supplier ID not specified.";
      header('Location: ../suppliers.php');
      exit();
  }

  $supplier_id = (int)$_GET['id'];

  // Note: Deleting a supplier will set the 'supplier_id' for related products to NULL
  // because of the ON DELETE SET NULL constraint in the database schema.
  // This prevents data loss and maintains integrity.

  // 2. Prepare and execute the DELETE statement
  $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
  $stmt->bind_param("i", $supplier_id);

  if ($stmt->execute()) {
      // Check if any rows were actually deleted
      if ($stmt->affected_rows > 0) {
          $_SESSION['message'] = "Supplier has been deleted successfully!";
      } else {
          $_SESSION['error'] = "Supplier not found or already deleted.";
      }
  } else {
      $_SESSION['error'] = "Failed to delete supplier. It might be in use by products.";
  }

  $stmt->close();
  $conn->close();

  // 3. Redirect the user back to the suppliers page
  header('Location: ../suppliers.php');
  exit();
?>


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
      header('Location: ../sales.php');
      exit();
  }

  // 1. Validate that a numeric sale ID is provided via GET request
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      $_SESSION['error'] = "Invalid request. Sale ID not specified.";
      header('Location: ../sales.php');
      exit();
  }

  $sale_id = (int)$_GET['id'];

  // 2. Get the sale details (product_id and quantity_sold) before deleting
  $stmt_select = $conn->prepare("SELECT product_id, quantity_sold FROM sales WHERE id = ?");
  $stmt_select->bind_param("i", $sale_id);
  $stmt_select->execute();
  $result = $stmt_select->get_result();
  
  if ($result->num_rows > 0) {
      $sale = $result->fetch_assoc();
      $product_id = (int)$sale['product_id'];
      $quantity_to_restore = (int)$sale['quantity_sold'];
  } else {
      $_SESSION['error'] = "Sale record not found.";
      header('Location: ../sales.php');
      exit();
  }
  $stmt_select->close();

  // 3. Start a database transaction to ensure both operations succeed or fail together
  $conn->begin_transaction();

  try {
      // First, delete the sale record
      $stmt_delete = $conn->prepare("DELETE FROM sales WHERE id = ?");
      $stmt_delete->bind_param("i", $sale_id);
      $stmt_delete->execute();
      $stmt_delete->close();

      // Second, add the quantity back to the product's stock
      $stmt_update = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
      $stmt_update->bind_param("ii", $quantity_to_restore, $product_id);
      $stmt_update->execute();
      $stmt_update->close();

      // If both queries were successful, commit the transaction
      $conn->commit();
      $_SESSION['message'] = "Sale record has been deleted and stock has been restored.";

  } catch (mysqli_sql_exception $exception) {
      // If any part of the transaction fails, roll back all changes
      $conn->rollback();
      $_SESSION['error'] = "Failed to delete sale record. An error occurred: " . $exception->getMessage();
  }

  $conn->close();

  // 4. Redirect the user back to the sales page
  header('Location: ../sales.php');
  exit();
?>


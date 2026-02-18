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
      header('Location: ../products.php');
      exit();
  }

  // 1. Validate that a numeric product ID is provided via GET request
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      $_SESSION['error'] = "Invalid request. Product ID not specified.";
      header('Location: ../products.php');
      exit();
  }

  $product_id = (int)$_GET['id'];

  // 2. Get the product's image filename before deleting the database record
  $stmt_select = $conn->prepare("SELECT product_image FROM products WHERE id = ?");
  $stmt_select->bind_param("i", $product_id);
  $stmt_select->execute();
  $result = $stmt_select->get_result();
  
  if ($result->num_rows > 0) {
      $product = $result->fetch_assoc();
      $image_filename = $product['product_image'];
  } else {
      // If the product doesn't exist, redirect with an error
      $_SESSION['error'] = "Product not found.";
      header('Location: ../products.php');
      exit();
  }
  $stmt_select->close();


  // 3. Prepare and execute the DELETE statement
  $stmt_delete = $conn->prepare("DELETE FROM products WHERE id = ?");
  $stmt_delete->bind_param("i", $product_id);

  if ($stmt_delete->execute()) {
      // 4. If deletion was successful, remove the associated image file
      // Avoid deleting the default placeholder image
      if ($image_filename != 'no_image.png') {
          $file_path = '../uploads/products/' . $image_filename;
          if (file_exists($file_path)) {
              unlink($file_path); // Deletes the file from the server
          }
      }
      $_SESSION['message'] = "Product has been deleted successfully!";
  } else {
      $_SESSION['error'] = "Failed to delete product: " . $conn->error;
  }

  $stmt_delete->close();
  $conn->close();

  // 5. Redirect the user back to the products page
  header('Location: ../products.php');
  exit();
?>

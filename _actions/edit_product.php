 <?php
  session_start();
  require_once('../includes/functions.php');
  require_once('../includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
  }

  // --- Handle Form Submission (POST Request) ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and validate input data
    $product_id = (int)$_POST['product_id'];
    $product_name = trim($_POST['product_name']);
    $quantity = (int)$_POST['quantity'];
    $purchase_price = (float)$_POST['purchase_price'];
    $sale_price = (float)$_POST['sale_price'];
    $supplier_id = (int)$_POST['supplier_id'];
    $current_image = $_POST['current_image']; // Keep track of the old image

    // Basic validation
    if (empty($product_name) || $quantity < 0 || $purchase_price < 0 || $sale_price < 0) {
        $_SESSION['error'] = "Please fill in all required fields with valid data.";
        header("Location: edit_product.php?id=$product_id");
        exit();
    }

    // 2. Handle File Upload
    $product_image = $current_image; // Default to the current image
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $upload_dir = '../uploads/products/';
        $image_name = time() . '_' . basename($_FILES['product_image']['name']);
        $target_file = $upload_dir . $image_name;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            $product_image = $image_name;
            // Delete the old image if it's not the default placeholder
            if ($current_image != 'no_image.png' && file_exists($upload_dir . $current_image)) {
                unlink($upload_dir . $current_image);
            }
        }
    }

    // 3. Prepare and Execute SQL UPDATE statement
    $stmt = $conn->prepare("UPDATE products SET product_name = ?, quantity = ?, purchase_price = ?, sale_price = ?, supplier_id = ?, product_image = ? WHERE id = ?");
    $stmt->bind_param("siddssi", $product_name, $quantity, $purchase_price, $sale_price, $supplier_id, $product_image, $product_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating product: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // 4. Redirect back to the products list
    header('Location: ../products.php');
    exit();
  }


  // --- Display Form (GET Request) ---
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      $_SESSION['error'] = "Invalid product ID.";
      header('Location: ../products.php');
      exit();
  }

  $product_id = (int)$_GET['id'];

  // Fetch the product to edit
  $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
  $stmt->bind_param("i", $product_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $product = $result->fetch_assoc();
  $stmt->close();

  if (!$product) {
      $_SESSION['error'] = "Product not found.";
      header('Location: ../products.php');
      exit();
  }

  // Fetch suppliers for the dropdown
  $suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name ASC");

  // Include the header file
  include_once('../includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Edit Product</h1>
        <a href="../products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="edit_product.php" method="post" enctype="multipart/form-data" class="form-container">
                <!-- Hidden fields to pass the product ID and current image name -->
                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['product_image']); ?>">

                <div class="form-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" class="form-control" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Quantity in Stock</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" value="<?php echo (int)$product['quantity']; ?>" required min="0">
                    </div>
                    <div class="form-group">
                        <label for="supplier_id">Supplier</label>
                        <select id="supplier_id" name="supplier_id" class="form-control">
                            <option value="">Select a supplier</option>
                            <?php while($supplier = $suppliers->fetch_assoc()): ?>
                                <option value="<?php echo (int)$supplier['id']; ?>" <?php if ($product['supplier_id'] == $supplier['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_price">Purchase Price ($)</label>
                        <input type="number" id="purchase_price" name="purchase_price" class="form-control" value="<?php echo htmlspecialchars($product['purchase_price']); ?>" required step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label for="sale_price">Selling Price ($)</label>
                        <input type="number" id="sale_price" name="sale_price" class="form-control" value="<?php echo htmlspecialchars($product['sale_price']); ?>" required step="0.01" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="product_image">Change Product Image</label>
                    <img src="../uploads/products/<?php echo htmlspecialchars($product['product_image']); ?>" alt="Current Image" width="100" style="margin-bottom: 10px; border-radius: 8px;">
                    <input type="file" id="product_image" name="product_image" class="form-control-file">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="../products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .page-container { padding: 20px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .page-header h1 { font-size: 1.8rem; }
    .card { background-color: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .card-body { padding: 30px; }
    .form-container { display: flex; flex-direction: column; gap: 20px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { margin-bottom: 8px; font-weight: 500; }
    .form-control, .form-control-file {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
    }
    .form-actions { display: flex; gap: 10px; margin-top: 20px; }
    .btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; color: white; font-weight: 500; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
    .btn-primary { background-color: #4A90E2; }
    .btn-secondary { background-color: #6c757d; }
</style>

<?php
  include_once('../includes/footer.php');
?>


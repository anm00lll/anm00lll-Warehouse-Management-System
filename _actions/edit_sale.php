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
    $sale_id = (int)$_POST['sale_id'];
    $new_product_id = (int)$_POST['product_id'];
    $new_quantity_sold = (int)$_POST['quantity_sold'];
    $original_product_id = (int)$_POST['original_product_id'];
    $original_quantity = (int)$_POST['original_quantity'];

    // Basic validation
    if (empty($new_product_id) || $new_quantity_sold <= 0) {
        $_SESSION['error'] = "Please select a product and enter a valid quantity.";
        header("Location: edit_sale.php?id=$sale_id");
        exit();
    }

    // 2. Get new product details and check stock
    $stmt = $conn->prepare("SELECT sale_price, quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $new_product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();
    $stmt->close();

    // Calculate available stock, considering the stock that will be returned from the original sale item
    $available_stock = $product['quantity'];
    if ($new_product_id == $original_product_id) {
        $available_stock += $original_quantity;
    }

    if (!$product || $new_quantity_sold > $available_stock) {
        $_SESSION['error'] = "Not enough stock available for this product. Only {$available_stock} available.";
        header("Location: edit_sale.php?id=$sale_id");
        exit();
    }

    // 3. Calculate total price
    $total_price = $product['sale_price'] * $new_quantity_sold;

    // 4. Use a transaction for data integrity
    $conn->begin_transaction();
    try {
        // Restore stock for the original product
        $stmt_restore = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $stmt_restore->bind_param("ii", $original_quantity, $original_product_id);
        $stmt_restore->execute();
        $stmt_restore->close();

        // Deduct stock for the new product
        $stmt_deduct = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $stmt_deduct->bind_param("ii", $new_quantity_sold, $new_product_id);
        $stmt_deduct->execute();
        $stmt_deduct->close();

        // Update the sales record
        $stmt_update_sale = $conn->prepare("UPDATE sales SET product_id = ?, quantity_sold = ?, total_price = ? WHERE id = ?");
        $stmt_update_sale->bind_param("iidi", $new_product_id, $new_quantity_sold, $total_price, $sale_id);
        $stmt_update_sale->execute();
        $stmt_update_sale->close();

        // If all queries succeed, commit the transaction
        $conn->commit();
        $_SESSION['message'] = "Sale updated successfully!";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating sale: " . $exception->getMessage();
    }

    $conn->close();
    header('Location: ../sales.php');
    exit();
  }

  // --- Display Form (GET Request) ---
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      $_SESSION['error'] = "Invalid sale ID.";
      header('Location: ../sales.php');
      exit();
  }

  $sale_id = (int)$_GET['id'];

  // Fetch the sale to edit
  $stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
  $stmt->bind_param("i", $sale_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $sale = $result->fetch_assoc();
  $stmt->close();

  if (!$sale) {
      $_SESSION['error'] = "Sale not found.";
      header('Location: ../sales.php');
      exit();
  }

  // Fetch all products for the dropdown
  $products = $conn->query("SELECT id, product_name, quantity FROM products ORDER BY product_name ASC");

  include_once('../includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Edit Sale</h1>
        <a href="../sales.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sales
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="edit_sale.php" method="post" class="form-container">
                <!-- Hidden fields to pass necessary IDs and original data -->
                <input type="hidden" name="sale_id" value="<?php echo (int)$sale['id']; ?>">
                <input type="hidden" name="original_product_id" value="<?php echo (int)$sale['product_id']; ?>">
                <input type="hidden" name="original_quantity" value="<?php echo (int)$sale['quantity_sold']; ?>">

                <div class="form-group">
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id" class="form-control" required>
                        <option value="">Select a product</option>
                        <?php while($product = $products->fetch_assoc()): ?>
                            <option value="<?php echo (int)$product['id']; ?>" <?php if ($sale['product_id'] == $product['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($product['product_name']); ?> (In Stock: <?php echo (int)$product['quantity']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity_sold">Quantity Sold</label>
                    <input type="number" id="quantity_sold" name="quantity_sold" class="form-control" value="<?php echo (int)$sale['quantity_sold']; ?>" required min="1">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Sale</button>
                    <a href="../sales.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .page-container { padding: 20px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .page-header h1 { font-size: 1.8rem; }
    .card { background-color: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); max-width: 600px; margin: auto; }
    .card-body { padding: 30px; }
    .form-container { display: flex; flex-direction: column; gap: 20px; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { margin-bottom: 8px; font-weight: 500; }
    .form-control {
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


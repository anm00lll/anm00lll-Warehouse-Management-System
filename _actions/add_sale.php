 <?php
  session_start();
  require_once('../includes/functions.php');
  require_once('../includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
  }

  // --- Fetch Products for the dropdown menu ---
  // Only products with a quantity greater than 0 should be sellable.
  $product_query = "SELECT id, product_name, sale_price, quantity FROM products WHERE quantity > 0 ORDER BY product_name ASC";
  $products = $conn->query($product_query);


  // --- Handle Form Submission ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and validate input data
    $product_id = (int)$_POST['product_id'];
    $quantity_sold = (int)$_POST['quantity_sold'];
    $user_id = (int)$_SESSION['user_id'];

    // Basic validation
    if (empty($product_id) || $quantity_sold <= 0) {
        $_SESSION['error'] = "Please select a product and enter a valid quantity.";
        header('Location: add_sale.php');
        exit();
    }

    // 2. Get product details and check stock
    $stmt = $conn->prepare("SELECT sale_price, quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product || $quantity_sold > $product['quantity']) {
        $_SESSION['error'] = "Not enough stock available for this product.";
        header('Location: add_sale.php');
        exit();
    }

    // 3. Calculate total price
    $total_price = $product['sale_price'] * $quantity_sold;

    // 4. Use a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // Insert into sales table
        $stmt_sale = $conn->prepare("INSERT INTO sales (product_id, quantity_sold, total_price, user_id, sale_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt_sale->bind_param("iidi", $product_id, $quantity_sold, $total_price, $user_id);
        $stmt_sale->execute();
        $stmt_sale->close();

        // Update product quantity
        $new_quantity = $product['quantity'] - $quantity_sold;
        $stmt_product = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $stmt_product->bind_param("ii", $new_quantity, $product_id);
        $stmt_product->execute();
        $stmt_product->close();

        // If both queries succeed, commit the transaction
        $conn->commit();
        $_SESSION['message'] = "Sale recorded successfully!";
    } catch (mysqli_sql_exception $exception) {
        // If any query fails, roll back the changes
        $conn->rollback();
        $_SESSION['error'] = "Error recording sale: " . $exception->getMessage();
    }

    $conn->close();

    // 5. Redirect back to the sales list
    header('Location: ../sales.php');
    exit();
  }

  // Include the header file
  include_once('../includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Add New Sale</h1>
        <a href="../sales.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sales
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="add_sale.php" method="post" class="form-container">
                <div class="form-group">
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id" class="form-control" required>
                        <option value="">Select a product</option>
                        <?php while($product = $products->fetch_assoc()): ?>
                            <option value="<?php echo (int)$product['id']; ?>">
                                <?php echo htmlspecialchars($product['product_name']); ?> (In Stock: <?php echo (int)$product['quantity']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity_sold">Quantity to Sell</label>
                    <input type="number" id="quantity_sold" name="quantity_sold" class="form-control" required min="1">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Record Sale</button>
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


 <?php
  session_start();
  require_once('includes/functions.php');
  require_once('includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
  }

  // --- Fetch All Products from Database ---
  // We join with the suppliers table to get the supplier's name instead of just the ID.
  $query = "SELECT p.*, s.supplier_name 
            FROM products p 
            LEFT JOIN suppliers s ON p.supplier_id = s.id 
            ORDER BY p.date_added DESC";

  $result = $conn->query($query);

  // Include the header file
  include_once('includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Manage Products</h1>
        <!-- This button will eventually link to a form for adding new products -->
        <a href="_actions/add_product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <!-- Display Success/Error Messages -->
    <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
    ?>

    <div class="card">
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>In Stock</th>
                        <th>Purchase Price</th>
                        <th>Sale Price</th>
                        <th>Supplier</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($product = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="product-image-cell">
                                    <img src="uploads/products/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-thumbnail">
                                </td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                <td>$<?php echo number_format($product['purchase_price'], 2); ?></td>
                                <td>$<?php echo number_format($product['sale_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date("M d, Y", strtotime($product['date_added'])); ?></td>
                                <td class="actions-cell">
                                    <a href="_actions/edit_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="_actions/delete_product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add some specific styles for this page -->
<style>
    .page-container {
        padding: 20px;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .page-header h1 {
        font-size: 1.8rem;
    }
    .btn {
        padding: 10px 15px;
        border-radius: 8px;
        text-decoration: none;
        color: white;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-primary { background-color: var(--primary-color, #4A90E2); }
    .btn-warning { background-color: #f0ad4e; }
    .btn-danger { background-color: #d9534f; }
    .btn-sm { padding: 5px 10px; font-size: 0.8rem; }

    .card {
        background-color: #fff;
        border-radius: var(--border-radius, 12px);
        box-shadow: var(--shadow, 0 4px 12px rgba(0,0,0,0.08));
    }
    .card-body {
        padding: 20px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table th, .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eef2f7;
    }
    .data-table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
    .product-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
    .actions-cell {
        display: flex;
        gap: 5px;
    }
    .text-center {
        text-align: center;
    }
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
</style>

<?php
  // Close the database connection.
  $conn->close();
  // Include the footer file.
  include_once('includes/footer.php');
?>


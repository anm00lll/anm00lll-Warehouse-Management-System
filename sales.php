 <?php
  session_start();
  require_once('includes/functions.php');
  require_once('includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
  }

  // --- Fetch All Sales from Database ---
  // We join with products and users tables to get meaningful names.
  $query = "SELECT s.id, s.quantity_sold, s.total_price, s.sale_date, p.product_name, u.name as cashier_name
            FROM sales s
            LEFT JOIN products p ON s.product_id = p.id
            LEFT JOIN users u ON s.user_id = u.id
            ORDER BY s.sale_date DESC";

  $result = $conn->query($query);

  // Include the header file
  include_once('includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Manage Sales</h1>
        <!-- This button will link to a page for creating a new sale transaction -->
        <a href="_actions/add_sale.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Sale
        </a>
    </div>

    <!-- Display Success/Error Messages from session -->
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
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Cashier</th>
                        <th>Sale Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($sale = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int)$sale['id']; ?></td>
                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                <td><?php echo (int)$sale['quantity_sold']; ?></td>
                                <td>$<?php echo number_format($sale['total_price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($sale['cashier_name']); ?></td>
                                <td><?php echo date("Y-m-d H:i:s", strtotime($sale['sale_date'])); ?></td>
                                <td class="actions-cell">
                                    <a href="_actions/edit_sale.php?id=<?php echo (int)$sale['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="_actions/delete_sale.php?id=<?php echo (int)$sale['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this sale record?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No sales records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reusing the same styles from products.php for consistency -->
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


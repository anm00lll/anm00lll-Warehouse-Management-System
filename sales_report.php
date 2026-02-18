<?php
  session_start();
  require_once('includes/functions.php');
  require_once('includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
  }

  // --- Fetch All Sales from Database for the Report ---
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
        <h1>Sales Report</h1>
        <a href="home.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Product Name</th>
                        <th>Quantity Sold</th>
                        <th>Total Price</th>
                        <th>Cashier</th>
                        <th>Date & Time</th>
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
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No sales records found to generate a report.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reusing the same styles for consistency -->
<style>
    .page-container { padding: 20px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .page-header h1 { font-size: 1.8rem; }
    .card { background-color: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .card-body { padding: 20px; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eef2f7; }
    .data-table th { font-weight: 600; background-color: #f8f9fa; }
    .text-center { text-align: center; }
    .btn { padding: 10px 15px; border-radius: 8px; text-decoration: none; color: white; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; }
    .btn-secondary { background-color: #6c757d; }
</style>

<?php
  // Close the database connection.
  $conn->close();
  // Include the footer file.
  include_once('includes/footer.php');
?>

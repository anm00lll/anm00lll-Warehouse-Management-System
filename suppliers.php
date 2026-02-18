 <?php
  session_start();
  require_once('includes/functions.php');
  require_once('includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
  }

  // --- Fetch All Suppliers from Database ---
  $query = "SELECT * FROM suppliers ORDER BY supplier_name ASC";
  $result = $conn->query($query);

  // Include the header file
  include_once('includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Manage Suppliers</h1>
        <!-- This button will link to a page for adding a new supplier -->
        <a href="_actions/add_supplier.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Supplier
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
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($supplier = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int)$supplier['id']; ?></td>
                                <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contact_person'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($supplier['status'] == 1): ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <a href="_actions/edit_supplier.php?id=<?php echo (int)$supplier['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <a href="_actions/delete_supplier.php?id=<?php echo (int)$supplier['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this supplier?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No suppliers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reusing the same styles from other pages for consistency -->
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
    .status-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
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


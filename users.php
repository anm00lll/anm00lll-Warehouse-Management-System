 <?php
  session_start();
  require_once('includes/functions.php');
  require_once('includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
  }
  
  // Optional: Role-based protection. Only Admins should see this page.
  // You would set $_SESSION['user_level'] during login. '1' is for Admin.
  if ($_SESSION['user_level'] != 1) {
      // You can redirect to home.php or show an access denied message.
      $_SESSION['error'] = "You do not have permission to access this page.";
      header('Location: home.php');
      exit();
  }


  // --- Fetch All Users from Database ---
  $query = "SELECT id, name, username, user_level, status, last_login FROM users ORDER BY name ASC";
  $result = $conn->query($query);

  // Include the header file
  include_once('includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Manage Users</h1>
        <a href="_actions/add_user.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add New User
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
                        <th>Name</th>
                        <th>Username</th>
                        <th>User Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int)$user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td>
                                    <?php 
                                        // Convert user_level number to a readable role name
                                        switch ($user['user_level']) {
                                            case 1: echo 'Admin'; break;
                                            case 2: echo 'Manager'; break;
                                            case 3: echo 'Staff'; break;
                                            default: echo 'Unknown'; break;
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($user['status'] == 1): ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['last_login'] ?? 'Never'); ?></td>
                                <td class="actions-cell">
                                    <a href="_actions/edit_user.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <!-- Prevent admin from deleting their own account -->
                                    <?php if ($_SESSION['user_id'] != $user['id']): ?>
                                      <a href="_actions/delete_user.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
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

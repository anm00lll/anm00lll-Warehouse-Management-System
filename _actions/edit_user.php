 <?php
  session_start();
  require_once('../includes/functions.php');
  require_once('../includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
  }
  
  // Role-based protection: Only Admins (level 1) can edit users.
  if ($_SESSION['user_level'] != 1) {
      $_SESSION['error'] = "You do not have permission to perform this action.";
      header('Location: ../users.php');
      exit();
  }

  // --- Handle Form Submission (POST Request) ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and validate input data
    $user_id = (int)$_POST['user_id'];
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $user_level = (int)$_POST['user_level'];
    $status = (int)$_POST['status'];

    // Basic validation
    if (empty($name) || empty($username) || empty($user_level)) {
        $_SESSION['error'] = "Name, username, and role are required.";
        header("Location: edit_user.php?id=$user_id");
        exit();
    }

    // 2. Handle password update
    if (!empty($password)) {
        // If a new password is provided, hash it and update it in the DB
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, password = ?, user_level = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssiii", $name, $username, $hashed_password, $user_level, $status, $user_id);
    } else {
        // If password is blank, do not update it
        $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, user_level = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssiii", $name, $username, $user_level, $status, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "User updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating user: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header('Location: ../users.php');
    exit();
  }

  // --- Display Form (GET Request) ---
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      $_SESSION['error'] = "Invalid user ID.";
      header('Location: ../users.php');
      exit();
  }

  $user_id = (int)$_GET['id'];

  // Fetch the user to edit
  $stmt = $conn->prepare("SELECT id, name, username, user_level, status FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  if (!$user) {
      $_SESSION['error'] = "User not found.";
      header('Location: ../users.php');
      exit();
  }

  include_once('../includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Edit User</h1>
        <a href="../users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="edit_user.php" method="post" class="form-container">
                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="user_level">User Role</label>
                        <select id="user_level" name="user_level" class="form-control" required>
                            <option value="1" <?php if ($user['user_level'] == 1) echo 'selected'; ?>>Admin</option>
                            <option value="2" <?php if ($user['user_level'] == 2) echo 'selected'; ?>>Manager</option>
                            <option value="3" <?php if ($user['user_level'] == 3) echo 'selected'; ?>>Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="1" <?php if ($user['status'] == 1) echo 'selected'; ?>>Active</option>
                            <option value="0" <?php if ($user['status'] == 0) echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="../users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .page-container { padding: 20px; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .page-header h1 { font-size: 1.8rem; }
    .card { background-color: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); max-width: 800px; margin: auto; }
    .card-body { padding: 30px; }
    .form-container { display: flex; flex-direction: column; gap: 20px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
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


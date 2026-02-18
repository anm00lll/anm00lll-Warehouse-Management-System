<?php
  session_start();
  // Use __DIR__ to build a reliable, absolute path to the include files.
  require_once(__DIR__ . '/../includes/db_connect.php');
  require_once(__DIR__ . '/../includes/functions.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
  }
  
  // Role-based protection: Only Admins (level 1) can add new users.
  if ($_SESSION['user_level'] != 1) {
      $_SESSION['error'] = "You do not have permission to perform this action.";
      header('Location: ../users.php');
      exit();
  }

  // --- Handle Form Submission ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $user_level = (int)$_POST['user_level'];
    $status = (int)$_POST['status'];

    if (empty($name) || empty($username) || empty($password) || empty($user_level)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header('Location: add_user.php');
        exit();
    }
    
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $_SESSION['error'] = "This username is already taken.";
        $stmt_check->close();
        header('Location: add_user.php');
        exit();
    }
    $stmt_check->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, username, password, user_level, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $name, $username, $hashed_password, $user_level, $status);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User created successfully!";
        log_activity($conn, "created a new user: " . $username);
    } else {
        $_SESSION['error'] = "Error creating user: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header('Location: ../users.php');
    exit();
  }

  // Include the header to apply the layout and styles
  include_once(__DIR__ . '/../includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Add New User</h1>
        <a href="../users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="card" style="max-width: 800px; margin: auto;">
        <div class="card-body">
            <form action="add_user.php" method="post" class="form-container">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="user_level">User Role</label>
                        <select id="user_level" name="user_level" class="form-control" required>
                            <option value="">Select a role</option>
                            <option value="1">Admin</option>
                            <option value="2">Manager</option>
                            <option value="3">Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save User</button>
                    <a href="../users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
  // Include the footer to close the HTML structure
  include_once(__DIR__ . '/../includes/footer.php');
?>

 <?php
  session_start();
  require_once('../includes/functions.php');
  require_once('../includes/db_connect.php');

  // --- Handle Form Submission ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and retrieve username and password from the form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Basic validation
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username and password are required.";
        header('Location: ../login.php');
        exit();
    }

    // 2. Prepare a statement to find the user by username to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, username, password, user_level, status FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 3. Verify the password against the hashed password in the database
        if (password_verify($password, $user['password'])) {
            
            // Check if the user account is active
            if ($user['status'] == 1) {
                // 4. Password is correct, create the session
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['name']; // Use their full name for display
                $_SESSION['user_level'] = (int)$user['user_level'];

                // 5. Update the last_login timestamp
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();

                // 6. Redirect to the main dashboard
                header('Location: ../home.php');
                exit();
            } else {
                // Account is inactive
                $_SESSION['login_error'] = "Your account is currently inactive.";
                header('Location: ../login.php');
                exit();
            }
        }
    }

    // If we reach here, it means the username was not found or the password was incorrect.
    $_SESSION['login_error'] = "Invalid username or password.";
    header('Location: ../login.php');
    exit();

  } else {
    // If the page is accessed directly without a POST request, redirect away.
    header('Location: ../login.php');
    exit();
  }
?>


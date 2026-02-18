<?php
  session_start();
  require_once('../includes/functions.php');
  require_once('../includes/db_connect.php');

  // Page Protection: Redirect if not logged in
  if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
  }

  // --- Handle Form Submission ---
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize and validate input data
    $supplier_name = trim($_POST['supplier_name']);
    $contact_person = trim($_POST['contact_person']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $status = (int)$_POST['status'];

    // Basic validation
    if (empty($supplier_name)) {
        $_SESSION['error'] = "Supplier name is required.";
        header('Location: add_supplier.php');
        exit();
    }
    
    // Validate email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header('Location: add_supplier.php');
        exit();
    }

    // 2. Prepare and Execute SQL INSERT statement
    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $supplier_name, $contact_person, $phone, $email, $address, $status);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Supplier added successfully!";
    } else {
        $_SESSION['error'] = "Error adding supplier: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // 3. Redirect back to the suppliers list
    header('Location: ../suppliers.php');
    exit();
  }

  // Include the header file
  include_once('../includes/header.php');
?>

<div class="page-container">
    <div class="page-header">
        <h1>Add New Supplier</h1>
        <a href="../suppliers.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Suppliers
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="add_supplier.php" method="post" class="form-container">
                <div class="form-group">
                    <label for="supplier_name">Supplier Name</label>
                    <input type="text" id="supplier_name" name="supplier_name" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" id="contact_person" name="contact_person" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                    <a href="../suppliers.php" class="btn btn-secondary">Cancel</a>
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

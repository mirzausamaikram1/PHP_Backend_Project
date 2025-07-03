<?php
// Start the session
session_start();

// Include database connection
include '../General/db_connect.php';

// Check if customer is logged in
if (!isset($_SESSION['cid']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../General/login.php");
  exit;
}

// Get customer ID from session
$cid = $_SESSION['cid'];

// Fetch current customer information
$customer_info = "SELECT cname, cpassword, ctel, caddr FROM customer WHERE cid = '$cid'";
$customer_result = mysqli_query($conn, $customer_info);
$customer = mysqli_fetch_assoc($customer_result);

// Set default values
$cname = $customer['cname'] ?? 'Guest';
$error = "";
$success = "";

// Handle profile update when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
  // Get updated values from form
  $cpassword = mysqli_real_escape_string($conn, $_POST['cpassword']);
  $ctel = mysqli_real_escape_string($conn, $_POST['ctel']);
  $caddr = mysqli_real_escape_string($conn, $_POST['caddr']);

  // Update customer details in the database
  $update_info = "UPDATE customer SET cpassword = '$cpassword', ctel = '$ctel', caddr = '$caddr' WHERE cid = '$cid'";
  if (mysqli_query($conn, $update_info)) {
    $success = "Profile updated successfully!";
  } else {
    $error = "Error updating profile: " . mysqli_error($conn);
  }

  // Refresh customer data after update
  $customer_info = "SELECT cpassword, ctel, caddr FROM customer WHERE cid = '$cid'";
  $customer_result = mysqli_query($conn, $customer_info);
  $customer = mysqli_fetch_assoc($customer_result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Profile</title>
  <link rel="stylesheet" href="../Customer_css/updateProfile.css">
</head>
<body>
<div class="sidebar">
  <a href="customerDashboard.php">Home</a>
  <a href="customerorder.php">Make an Order</a>
  <a href="customerVieworder.php">View Order Records</a>
  <a href="customerDelete.php">Delete Order Record</a>
  <a href="../General/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>
<div class="content">
  <h2>Update Profile</h2>

  <!-- Display error or success messages -->
  <?php if ($error) echo "<div class='error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='success'>$success</div>"; ?>

  <!-- Profile update form -->
  <form method="post" action="">
    <div class="form-group">
      <label>Customer ID: <?php echo htmlspecialchars($cid); ?></label>
    </div>
    <div class="form-group">
      <label>Customer Name: <?php echo htmlspecialchars($cname); ?></label>
    </div>
    <div class="form-group">
      <label>New Password:</label>
      <input type="password" name="cpassword" value="<?php echo htmlspecialchars($customer['cpassword'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
      <label>Contact Number:</label>
      <input type="text" name="ctel" value="<?php echo htmlspecialchars($customer['ctel'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
      <label>Address:</label>
      <textarea name="caddr" id="caddr" required><?php echo htmlspecialchars($customer['caddr'] ?? ''); ?></textarea>
    </div>
    <button type="submit" name="update_profile">Update Profile</button>
  </form>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>

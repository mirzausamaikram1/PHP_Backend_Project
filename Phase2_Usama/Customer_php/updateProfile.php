<?php
// Turn on the session
session_start();

// Connect to the database
include '../General/db_connect.php';

// Check if customer is logged in
if (!isset($_SESSION['sid']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../General/login.php");
  exit;
}

$cid = $_SESSION['sid'];
$cname = $_SESSION['cname'] ?? '';
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
  $cpassword = mysqli_real_escape_string($conn, $_POST['cpassword']);
  $ctel = mysqli_real_escape_string($conn, $_POST['ctel']);
  $caddr = mysqli_real_escape_string($conn, $_POST['caddr']);

  $update_info = "UPDATE customer SET cpassword = '$cpassword', ctel = '$ctel', caddr = '$caddr' WHERE cid = '$cid'";
  if (mysqli_query($conn, $update_info)) {
    $success = "Profile updated successfully!";
  } else {
    $error = "Error updating profile: " . mysqli_error($conn);
  }
}

// Get current customer data
$customer_info = "SELECT cpassword, ctel, caddr FROM customer WHERE cid = '$cid'";
$customer_result = mysqli_query($conn, $customer_info);
$customer = mysqli_fetch_assoc($customer_result);
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
  <a href="customerDashboard.php?logout=1" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>
<div class="content">
  <h2>Update Profile</h2>
  <?php if ($error) echo "<div class='error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='success'>$success</div>"; ?>
  <form method="post" action="">
    <div class="form-group">
      <label>Customer ID: <?php echo $cid; ?></label>
    </div>
    <div class="form-group">
      <label>Customer Name: <?php echo $cname; ?></label>
    </div>
    <div class="form-group">
      <label>New Password:</label>
      <input type="password" name="cpassword" value="<?php echo $customer['cpassword']; ?>" required>
    </div>
    <div class="form-group">
      <label>Contact Number:</label>
      <input type="text" name="ctel" value="<?php echo $customer['ctel'] ?: ''; ?>" required>
    </div>
    <div class="form-group">
      <label>Address:</label>
      <input type="text" name="caddr" value="<?php echo $customer['caddr'] ?: ''; ?>" required>
    </div>
    <button type="submit" name="update_profile">Update Profile</button>
  </form>
</div>
</body>
</html>
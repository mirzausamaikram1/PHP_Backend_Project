<?php
// Include database connection and start session
include '../General/db_connect.php';
session_start();

// Check if the customer is logged in
if (!isset($_SESSION['cid']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../General/login.php"); // Redirect if not logged in
  exit;
}

// Get customer ID from session
$cid = $_SESSION['cid'];

// Fetch the customer name from the database
$sql = "SELECT cname FROM customer WHERE cid = '$cid'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Set customer name or fallback to 'Guest'
$cname = $user['cname'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="../Customer_css/customerDashboard.css">
</head>
<body>
<!-- Sidebar navigation -->
<div class="sidebar">
  <a href="customerorder.php">Make an Order</a>
  <a href="customerVieworder.php">View Order Records</a>
  <a href="updateProfile.php">Update Profile</a>
  <a href="customerDelete.php">Delete Order Record</a>
  <a href="../General/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>

<!-- Main dashboard content -->
<div class="content">
  <h1>Customer Dashboard</h1>
  <div class="welcome">
    Welcome, <?php echo htmlspecialchars($cname); ?>!
  </div>
</div>
</body>
</html>

<?php
// Close the database connection
if (isset($conn) && $conn) {
  mysqli_close($conn);
}
?>

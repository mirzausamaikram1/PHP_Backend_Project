<?php
include 'db_connect.php';
session_start();

// Check if user is logged in as staff
if (!isset($_SESSION['sid']) || $_SESSION['role'] !== 'staff') {
  header("Location: ../General/login.php");
  exit;
}
?>

  <!DOCTYPE html>
  <html>
  <head>
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="../Staff_css/staffdashboard.css">
  </head>
  <body>
  <div class="sidebar">
    <a href="insertProducts.php">Insert Products</a>
    <a href="updateOrder.php">Update Order Records</a>
    <a href="reports.php">Generate Report</a>
    <a href="deleteProduct.php">Delete Product</a>
    <a href="insertMaterials.php">Insert Materials</a>
    <a href="../General/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>

  </div>

  <div class="content">
    <h1>Welcome to Staff Dashboard</h1>
    <p>Select an option from the menu to manage the system.</p>
  </div>
  </body>
  </html>

<?php
if (isset($conn) && $conn) {
  mysqli_close($conn);
}
?>
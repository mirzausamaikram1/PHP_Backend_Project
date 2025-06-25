<?php
// Bring in the file that helps us connect to the database
include '../General/db_connect.php';
session_start(); // This is like a memory to remember who is logged in

// Check if they are logged in as a customer
if (!isset($_SESSION['sid']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../General/login.php"); // Go back to login if not a customer
  exit;
}

// Get the customer's details from the database
$cid = $_SESSION['sid']; // Get the customer ID from the session
$sql = "SELECT cname FROM customer WHERE cid = '$cid'"; // Look up their name
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result); // Get their details
$cname = $user['cname'] ?? 'Guest'; // Set the name, or 'Guest' if not found
?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../Customer_css/customerDashboard.css">
  </head>
  <body>
  <div class="sidebar">
    <a href="customerorder.php">Make an Order</a>
    <a href="customerVieworder.php">View Order Records</a>
    <a href="updateProfile.php">Update Profile</a>
    <a href="customerDelete.php">Delete Order Record</a>
    <a href="?logout=1" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
  </div>
  <div class="content">
    <h1>Customer Dashboard</h1>
    <div class="welcome">
      Welcome, <?php echo htmlspecialchars($cname); ?>!
    </div>
  </div>
  </body>
  </html>

<?php
if (isset($conn) && $conn) {
  mysqli_close($conn); // Close the connection when done
}
?>
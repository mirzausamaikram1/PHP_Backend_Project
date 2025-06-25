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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_order'])) {
  $oid = mysqli_real_escape_string($conn, $_POST['oid']);

  // Check if order exists and belongs to customer
  $order_check = "SELECT odeliverdate, pid, oqty FROM orders WHERE oid = '$oid' AND cid = '$cid'";
  $order_result = mysqli_query($conn, $order_check);
  if (mysqli_num_rows($order_result) > 0) {
    $order = mysqli_fetch_assoc($order_result);
    $deliver_date = $order['odeliverdate'];
    $pid = $order['pid'];
    $oqty = $order['oqty'];

    // Get current date
    $current_date = date('Y-m-d H:i:s');
    $two_days_before = date('Y-m-d H:i:s', strtotime('-2 days', strtotime($deliver_date)));

    if ($deliver_date && $current_date <= $two_days_before) {
      // Delete order
      $delete_order = "DELETE FROM orders WHERE oid = '$oid' AND cid = '$cid'";
      if (mysqli_query($conn, $delete_order)) {
        // Update material quantities
        $material_update = "UPDATE material m 
                           JOIN prodmat pm ON m.mid = pm.mid 
                           SET m.mqty = m.mqty + ($oqty * pm.pmqty) 
                           WHERE pm.pid = '$pid'";
        mysqli_query($conn, $material_update);
        $success = "Order deleted successfully!";
      } else {
        $error = "Error deleting order: " . mysqli_error($conn);
      }
    } else {
      $error = "Cannot delete order within 2 days of delivery date!";
    }
  } else {
    $error = "Order not found or not authorized!";
  }
}

// Get customer's orders
$orders_list = "SELECT oid, odate, pid, oqty, ocost, odeliverdate, ostatus 
                FROM orders WHERE cid = '$cid'";
$orders_result = mysqli_query($conn, $orders_list);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Delete Order Record</title>
  <link rel="stylesheet" href="../Customer_css/customerDelete.css">
</head>
<body>
<div class="sidebar">
  <a href="customerDashboard.php">Home</a>
  <a href="customerorder.php">Make an Order</a>
  <a href="customerVieworder.php">View Order Records</a>
  <a href="updateProfile.php">Update Profile</a>
  <a href="customerDashboard.php?logout=1" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>
<div class="content">
  <h2>Delete Order Record</h2>
  <?php if ($error) echo "<div class='error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='success'>$success</div>"; ?>
  <form method="post" action="">
    <div class="form-group">
      <label>Select Order to Delete:</label>
      <select name="oid" required>
        <?php while ($row = mysqli_fetch_assoc($orders_result)) { ?>
          <option value="<?php echo $row['oid']; ?>">
            Order #<?php echo $row['oid']; ?> (Date: <?php echo $row['odate']; ?>)
          </option>
        <?php } ?>
      </select>
    </div>
    <button type="submit" name="delete_order">Delete Order</button>
  </form>
</div>
</body>
</html>
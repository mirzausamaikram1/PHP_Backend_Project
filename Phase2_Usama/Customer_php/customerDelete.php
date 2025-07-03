<?php
// Start session and include database connection
session_start();
include '../General/db_connect.php';

// Check if customer is logged in
if (!isset($_SESSION['cid']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../General/login.php");
  exit;
}

// Get customer ID from session
$cid = $_SESSION['cid'];
$error = "";
$success = "";

// Handle order deletion when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_order'])) {
  $oid = mysqli_real_escape_string($conn, $_POST['delete_order']); // Get order ID to delete

  // Check if the order exists and belongs to the current customer
  $order_check = "SELECT odeliverdate, pid, oqty FROM orders WHERE oid = '$oid' AND cid = '$cid'";
  $order_result = mysqli_query($conn, $order_check);

  if (mysqli_num_rows($order_result) > 0) {
    $order = mysqli_fetch_assoc($order_result);
    $deliver_date = $order['odeliverdate'];
    $pid = $order['pid'];
    $oqty = $order['oqty'];

    // Calculate current date and 2 days before delivery date
    $current_date = date('Y-m-d H:i:s');
    $two_days_before = date('Y-m-d H:i:s', strtotime('-2 days', strtotime($deliver_date)));

    // Allow deletion only if delivery is more than 2 days away
    if (!$deliver_date || ($deliver_date && $current_date <= $two_days_before)) {
      // Delete the order
      $delete_order = "DELETE FROM orders WHERE oid = '$oid' AND cid = '$cid'";
      if (mysqli_query($conn, $delete_order)) {
        // Update reserved material quantity back to stock
        $material_update = "UPDATE material m 
                                   JOIN prodmat pm ON m.mid = pm.mid 
                                   SET m.mrqty = m.mrqty + ($oqty * pm.pmqty) 
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

// Fetch all customer's orders
$orders_list = "SELECT oid, odate, pid, oqty, ocost, odeliverdate, ostatus FROM orders WHERE cid = '$cid' ORDER BY odate DESC";
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
  <a href="../php/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>
<div class="content">
  <h2>Delete Order Record</h2>
  <!-- Display messages -->
  <?php if ($error) echo "<div class='error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='success'>$success</div>"; ?>

  <?php if (mysqli_num_rows($orders_result) > 0) { ?>
    <form method="post" action="">
      <table>
        <tr>
          <th>Order ID</th>
          <th>Order Date</th>
          <th>Product ID</th>
          <th>Quantity</th>
          <th>Cost (USD)</th>
          <th>Delivery Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php
        mysqli_data_seek($orders_result, 0); // Reset result pointer
        while ($row = mysqli_fetch_assoc($orders_result)) {
          $status = $row['ostatus'] == 1 ? 'Pending' : ($row['ostatus'] == 2 ? 'Processing' : 'Delivered');
          $deliver_date = $row['odeliverdate'] ? $row['odeliverdate'] : 'Not set';
          echo "<tr>";
          echo "<td>{$row['oid']}</td>";
          echo "<td>{$row['odate']}</td>";
          echo "<td>{$row['pid']}</td>";
          echo "<td>{$row['oqty']}</td>";
          echo "<td>" . number_format($row['ocost'], 2) . "</td>";
          echo "<td>$deliver_date</td>";
          echo "<td>$status</td>";
          echo "<td><button type='submit' name='delete_order' value='{$row['oid']}' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete Order #{$row['oid']}?\");'>Delete</button></td>";
          echo "</tr>";
        }
        ?>
      </table>
    </form>
  <?php } else { ?>
    <p>No orders found to delete.</p>
  <?php } ?>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>

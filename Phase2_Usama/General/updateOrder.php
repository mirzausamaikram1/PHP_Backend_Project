<?php
include 'db_connect.php';
session_start();

// Check if user is logged in as staff
if (!isset($_SESSION['sid']) || $_SESSION['role'] !== 'staff') {
  header("Location: customerLogin.php");
  exit;
}

$error = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_oid'])) {
  $oid = mysqli_real_escape_string($conn, $_POST['update_oid']);
  $oqty = mysqli_real_escape_string($conn, $_POST['oqty']);
  $ostatus = mysqli_real_escape_string($conn, $_POST['ostatus']);

  // Update order
  $update_order = "UPDATE orders SET oqty = '$oqty', ostatus = '$ostatus' WHERE oid = '$oid'";
  if (mysqli_query($conn, $update_order)) {
    // Update material reserved quantity (simplified example, adjust based on prodmat)
    $get_pid = "SELECT pid FROM orders WHERE oid = '$oid'";
    $pid_result = mysqli_query($conn, $get_pid);
    $pid = mysqli_fetch_assoc($pid_result)['pid'];
    $update_material = "UPDATE material m JOIN prodmat pm ON m.mid = pm.mid 
                           SET m.mqty_reserved = m.mqty_reserved + ($oqty - (SELECT oqty FROM orders WHERE oid = '$oid' LIMIT 1))
                           WHERE pm.pid = '$pid'";
    if (mysqli_query($conn, $update_material)) {
      $message = "Order and material updated successfully!";
    } else {
      $error = "Error updating material: " . mysqli_error($conn);
    }
  } else {
    $error = "Error updating order: " . mysqli_error($conn);
  }
}

// Fetch orders for display
$orders = mysqli_query($conn, "SELECT o.*, p.pname, c.cname, c.ctel, c.caddr, m.mname 
                              FROM orders o 
                              JOIN product p ON o.pid = p.pid 
                              JOIN customer c ON o.cid = c.cid 
                              JOIN prodmat pm ON p.pid = pm.pid 
                              JOIN material m ON pm.mid = m.mid");
?>

  <!DOCTYPE html>
  <html>
  <head>
    <title>Update Order Records</title>
    <link rel="stylesheet" href="../Staff_css/updateOrder.css">
  </head>
  <body>
  <div class="sidebar">
    <a href="staffDashboard.php">Staff Dashboard</a>
    <a href="deleteProduct.php">Delete Product</a>
    <a href="reports.php">Generate Report</a>
    <a href="insertMaterials.php">Insert Materials’ Information</a>
    <a href="insertProducts.php">Insert Products’ Information</a>
    <a href="login.php">Logout</a>
  </div>

  <div class="content">
    <h1>Update Order Records</h1>
    <?php if ($error): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
      <p class="success"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="update_oid">Select Order to Update:</label>
      <select name="update_oid" id="update_oid" required>
        <option value="">-- Select Order --</option>
        <?php while ($order = mysqli_fetch_assoc($orders)): ?>
          <option value="<?php echo htmlspecialchars($order['oid']); ?>">
            Order #<?php echo htmlspecialchars($order['oid']); ?> - <?php echo htmlspecialchars($order['pname']); ?>
          </option>
        <?php endwhile; ?>
      </select><br>
      <label for="oqty">New Quantity:</label>
      <input type="number" id="oqty" name="oqty" required><br>
      <label for="ostatus">Status:</label>
      <select name="ostatus" id="ostatus" required>
        <option value="1">Accepted</option>
        <option value="0">Rejected</option>
      </select><br><br>
      <button type="submit">Update Order</button>
    </form>
  </div>
  </body>
  </html>

<?php mysqli_close($conn); ?>
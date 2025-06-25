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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_pid'])) {
  $pid = mysqli_real_escape_string($conn, $_POST['delete_pid']);
  // Check if product has related orders
  $check_orders = "SELECT oid FROM orders WHERE pid = '$pid'";
  $orders_result = mysqli_query($conn, $check_orders);
  if (mysqli_num_rows($orders_result) == 0) {
    $delete_sql = "DELETE FROM product WHERE pid = '$pid'";
    if (mysqli_query($conn, $delete_sql)) {
      $message = "Product deleted successfully!";
    } else {
      $error = "Error deleting product: " . mysqli_error($conn);
    }
  } else {
    $error = "Cannot delete product with existing orders!";
  }
}

// Fetch products for display
$products = mysqli_query($conn, "SELECT * FROM product");
?>

  <!DOCTYPE html>
  <html>
  <head>
    <title>Delete Product</title>
    <link rel="stylesheet" href="../Staff_css/deleteProduct.css">
  </head>
  <body>
  <div class="sidebar">
    <a href="staffDashboard.php">Staff Dashboard</a>
    <a href="reports.php">Generate Report</a>
    <a href="updateOrder.php">Update Order Records</a>
    <a href="insertMaterials.php">Insert Materials’ Information</a>
    <a href="insertProducts.php">Insert Products’ Information</a>
    <a href="login.php">Logout</a>
  </div>

  <div class="content">
    <h1>Delete Product</h1>
    <?php if ($error): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
      <p class="success"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this product?');">
      <label for="delete_pid">Select Product to Delete:</label>
      <select name="delete_pid" id="delete_pid" required>
        <option value="">-- Select Product --</option>
        <?php while ($product = mysqli_fetch_assoc($products)): ?>
          <option value="<?php echo htmlspecialchars($product['pid']); ?>">
            <?php echo htmlspecialchars($product['pname']); ?>
          </option>
        <?php endwhile; ?>
      </select><br><br>
      <button type="submit">Delete Product</button>
    </form>
  </div>
  </body>
  </html>

<?php mysqli_close($conn); ?>
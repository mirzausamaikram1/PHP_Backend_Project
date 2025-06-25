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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $pid = mysqli_real_escape_string($conn, $_POST['pid']);
  $pname = mysqli_real_escape_string($conn, $_POST['pname']);
  $pcost = mysqli_real_escape_string($conn, $_POST['pcost']);
  $mid = mysqli_real_escape_string($conn, $_POST['mid']);
  $m_qty = mysqli_real_escape_string($conn, $_POST['m_qty']);

  if (empty($pid) || empty($pname) || empty($pcost) || empty($mid) || empty($m_qty)) {
    $error = "All fields are required!";
  } else {
    $insert_product = "INSERT INTO product (pid, pname, pcost) VALUES ('$pid', '$pname', '$pcost')";
    if (mysqli_query($conn, $insert_product)) {
      $insert_prodmat = "INSERT INTO prodmat (pid, mid, m_qty) VALUES ('$pid', '$mid', '$m_qty')";
      if (mysqli_query($conn, $insert_prodmat)) {
        $message = "Product and material association added successfully!";
      } else {
        $error = "Error adding material association: " . mysqli_error($conn);
        // Roll back product insertion if prodmat fails
        mysqli_query($conn, "DELETE FROM product WHERE pid = '$pid'");
      }
    } else {
      $error = "Error adding product: " . mysqli_error($conn);
    }
  }
}

// Fetch materials for dropdown
$materials = mysqli_query($conn, "SELECT * FROM material");
?>

  <!DOCTYPE html>
  <html>
  <head>
    <title>Insert Products’ Information</title>
    <link rel="stylesheet" href="../Staff_css/insertProducts.css">
  </head>
  <body>
  <div class="sidebar">
    <a href="staffDashboard.php">Staff Dashboard</a>
    <a href="deleteProduct.php">Delete Product</a>
    <a href="reports.php">Generate Report</a>
    <a href="updateOrder.php">Update Order Records</a>
    <a href="insertMaterials.php">Insert Materials’ Information</a>
    <a href="login.php">Logout</a>
  </div>

  <div class="content">
    <h1>Insert Products’ Information</h1>
    <?php if ($error): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
      <p class="success"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="pid">Product ID:</label>
      <input type="text" id="pid" name="pid" required><br>
      <label for="pname">Product Name:</label>
      <input type="text" id="pname" name="pname" required><br>
      <label for="pcost">Product Cost:</label>
      <input type="number" id="pcost" name="pcost" step="0.01" required><br>
      <label for="mid">Select Material:</label>
      <select name="mid" id="mid" required>
        <option value="">-- Select Material --</option>
        <?php while ($material = mysqli_fetch_assoc($materials)): ?>
          <option value="<?php echo htmlspecialchars($material['mid']); ?>">
            <?php echo htmlspecialchars($material['mname']); ?>
          </option>
        <?php endwhile; ?>
      </select><br>
      <label for="m_qty">Material Quantity:</label>
      <input type="number" id="m_qty" name="m_qty" required><br><br>
      <button type="submit">Add Product</button>
    </form>
  </div>
  </body>
  </html>

<?php mysqli_close($conn); ?>
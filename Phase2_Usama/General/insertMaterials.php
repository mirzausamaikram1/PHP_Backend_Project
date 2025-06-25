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
  $mname = mysqli_real_escape_string($conn, $_POST['mname']);
  $mqty = mysqli_real_escape_string($conn, $_POST['mqty']);
  $mqty_reserved = mysqli_real_escape_string($conn, $_POST['mqty_reserved']);
  $unit = mysqli_real_escape_string($conn, $_POST['unit']);
  $reorder_level = mysqli_real_escape_string($conn, $_POST['reorder_level']);

  if (empty($mname) || empty($mqty) || empty($mqty_reserved) || empty($unit) || empty($reorder_level)) {
    $error = "All fields are required!";
  } elseif ($mqty_reserved > $mqty) {
    $error = "Reserved Quantity cannot exceed Physical Quantity!";
  } else {
    $sql = "INSERT INTO material (mname, mqty, mrqty, munit, mreorderqty) 
            VALUES ('$mname', '$mqty', '$mqty_reserved', '$unit', '$reorder_level')";
    if (mysqli_query($conn, $sql)) {
      $message = "Material added successfully!";
    } else {
      $error = "Error: " . mysqli_error($conn);
    }
  }
}
?>

  <!DOCTYPE html>
  <html>
  <head>
    <title>Insert Materials’ Information</title>
    <link rel="stylesheet" href="../Staff_css/insertMaterials.css">
  </head>
  <body>
  <div class="sidebar">
    <a href="staffDashboard.php">Staff Dashboard</a>
    <a href="deleteProduct.php">Delete Product</a>
    <a href="reports.php">Generate Report</a>
    <a href="updateOrder.php">Update Order Records</a>
    <a href="insertProducts.php">Insert Products’ Information</a>
    <a href="login.php">Logout</a>
  </div>

  <div class="content">
    <h1>Insert Materials’ Information</h1>
    <?php if ($error): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
      <p class="success"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="mname">Material Name:</label>
      <input type="text" id="mname" name="mname" required><br>
      <label for="mqty">Physical Quantity:</label>
      <input type="number" id="mqty" name="mqty" required><br>
      <label for="mqty_reserved">Reserved Quantity:</label>
      <input type="number" id="mqty_reserved" name="mqty_reserved" required><br>
      <label for="unit">Unit:</label>
      <input type="text" id="unit" name="unit" required><br>
      <label for="reorder_level">Re-order Level:</label>
      <input type="number" id="reorder_level" name="reorder_level" required><br><br>
      <button type="submit">Add Material</button>
    </form>
  </div>
  </body>
  </html>

<?php mysqli_close($conn); ?>
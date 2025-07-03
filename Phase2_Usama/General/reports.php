<?php
// Include database connection and start session
include 'db_connect.php';
session_start();

// Check if user is logged in as staff
if (!isset($_SESSION['sid']) || $_SESSION['role'] !== 'staff') {
  header("Location: ../General/login.php");
  exit;
}

$error = '';

// Get date filters if provided
$start_date = isset($_POST['start_date']) ? mysqli_real_escape_string($conn, $_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? mysqli_real_escape_string($conn, $_POST['end_date']) : '';

// Base SQL query to fetch all order report data
$sql = "SELECT o.oid, o.odate, p.pname, p.pcost AS unit_price, o.oqty, o.ocost AS total_sales, c.cname 
        FROM orders o 
        JOIN product p ON o.pid = p.pid 
        JOIN customer c ON o.cid = c.cid";

// If both start and end dates are provided, add a WHERE clause to filter results
if (!empty($start_date) && !empty($end_date)) {
  $sql .= " WHERE o.odate BETWEEN '$start_date' AND '$end_date'";
}

// Execute the query
$reports = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html data-bs-theme="light" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <title>Reports</title>
  <link rel="stylesheet" href="../Staff_css/reports.css">
</head>
<body>
<!-- Sidebar for staff navigation -->
<div class="sidebar">
  <a href="../General/staffDashboard.php">Staff Dashboard</a>
  <a href="../General/deleteProduct.php">Delete Product</a>
  <a href="updateOrder.php">Update Order Records</a>
  <a href="insertMaterials.php">Insert Materials</a>
  <a href="insertProducts.php">Insert Products</a>
  <a href="../General/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>

</div>

<!-- Main content area -->
<div class="content">
  <h1>Reports</h1>

  <!-- Display any error messages -->
  <?php if ($error): ?>
    <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
  <?php endif; ?>

  <!-- Date filter form -->
  <form method="POST" action="" class="mb-4">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Start Date</label>
        <input class="form-control" type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">End Date</label>
        <input class="form-control" type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
      </div>
      <div class="col-md-4 align-self-end">
        <button type="submit" class="btn btn-primary">Filter</button>
      </div>
    </div>
  </form>

  <!-- Display report table -->
  <div class="row">
    <div class="col">
      <?php if (mysqli_num_rows($reports) > 0): ?>
        <table class="table table-striped">
          <thead>
          <tr>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>Product Name</th>
            <th>Unit Price (HKD)</th>
            <th>Quantity</th>
            <th>Total Sales (HKD)</th>
            <th>Customer Name</th>
          </tr>
          </thead>
          <tbody>
          <?php while ($report = mysqli_fetch_assoc($reports)): ?>
            <tr>
              <td><?php echo htmlspecialchars($report['oid']); ?></td>
              <td><?php echo htmlspecialchars($report['odate']); ?></td>
              <td><?php echo htmlspecialchars($report['pname']); ?></td>
              <td><?php echo number_format($report['unit_price'], 2); ?></td>
              <td><?php echo htmlspecialchars($report['oqty']); ?></td>
              <td><?php echo number_format($report['total_sales'], 2); ?></td>
              <td><?php echo htmlspecialchars($report['cname']); ?></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="alert alert-warning" role="alert">No reports available.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="reports.js"></script>
</body>
</html>

<?php mysqli_close($conn); ?>

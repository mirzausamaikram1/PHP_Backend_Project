<?php
// Include database connection
include '../General/db_connect.php';
session_start(); // Start session to use session variables

// echo "Session CID: " . (isset($_SESSION['cid']) ? $_SESSION['cid'] : "Not set") . "<br>";
// echo "Session Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : "Not set") . "<br>";

// Ensure the user is logged in and is a customer
if (!isset($_SESSION['cid']) || $_SESSION['role'] !== 'customer') {
  echo "Redirecting due to session issue...<br>";
  header("Location: ../General/login.php");
  exit;
}

// Get customer ID from session
$cid = $_SESSION['cid'];

// Handle sorting parameters from dropdowns
$sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'oid';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Define allowed column names and sort orders to prevent SQL injection
$valid_columns = ['oid', 'odate', 'pid', 'oqty', 'ocost', 'cid', 'odeliverdate', 'ostatus'];
$valid_orders = ['ASC', 'DESC'];

// Fallback to defaults if invalid input
if (!in_array($sort_column, $valid_columns)) {
  $sort_column = 'oid';
}
if (!in_array($sort_order, $valid_orders)) {
  $sort_order = 'ASC';
}

// Query to fetch order records of the customer with dynamic sorting
$sql = "SELECT oid, odate, pid, oqty, ocost, cid, odeliverdate, ostatus 
        FROM orders 
        WHERE cid = '$cid' 
        ORDER BY $sort_column $sort_order";

$result = mysqli_query($conn, $sql);
if (!$result) {
  die("Error fetching orders: " . mysqli_error($conn));
}

// Begin HTML layout
?>
<!DOCTYPE html>
<html>
<head>
  <title>View Orders</title>
  <link rel="stylesheet" href="../Customer_css/customerVieworder.css">
</head>
<body>
<div class="sidebar">
  <a href="customerDashboard.php">Home</a>
  <a href="customerOrder.php">Order</a>
  <a href="customerVieworder.php">View Orders</a>
  <a href="updateProfile.php">Update Profile</a>
  <a href="customerDelete.php">Delete Order</a>
  <a href="../php/logout.php">Logout</a>
</div>
<div class="content">
  <h2>View Your Orders</h2>

  <!-- Sorting options -->
  <div class="sort-options">
    <form method="GET" action="">
      <label for="sort_column">Sort By:</label>
      <select id="sort_column" name="sort_column" onchange="this.form.submit()">
        <option value="oid" <?= $sort_column == 'oid' ? 'selected' : '' ?>>Order ID</option>
        <option value="odate" <?= $sort_column == 'odate' ? 'selected' : '' ?>>Order Date</option>
      </select>
      <label for="sort_order">Order:</label>
      <select id="sort_order" name="sort_order" onchange="this.form.submit()">
        <option value="ASC" <?= $sort_order == 'ASC' ? 'selected' : '' ?>>Ascending</option>
        <option value="DESC" <?= $sort_order == 'DESC' ? 'selected' : '' ?>>Descending</option>
      </select>
    </form>
  </div>

  <!-- Order table -->
  <table>
    <thead>
    <tr>
      <th>Order ID</th>
      <th>Order Date</th>
      <th>Product ID</th>
      <th>Order Quantity</th>
      <th>Order Cost</th>
      <th>Customer ID</th>
      <th>Order Delivery Date</th>
      <th>Order Status</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // Display all orders in table rows
    if (mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        // Convert numeric status to text
        switch ($row['ostatus']) {
          case 1:
            $statusText = 'Pending'; break;
          case 2:
            $statusText = 'Confirm'; break;
          case 3:
            $statusText = 'Cancel'; break;
          default:
            $statusText = 'Unknown'; break;
        }
        echo "<tr>
                        <td>{$row['oid']}</td>
                        <td>{$row['odate']}</td>
                        <td>{$row['pid']}</td>
                        <td>{$row['oqty']}</td>
                        <td>{$row['ocost']}</td>
                        <td>{$row['cid']}</td>
                        <td>{$row['odeliverdate']}</td>
                        <td>$statusText</td>
                      </tr>";
      }
    } else {
      echo "<tr><td colspan='8'>No orders found.</td></tr>";
    }
    ?>
    </tbody>
  </table>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>

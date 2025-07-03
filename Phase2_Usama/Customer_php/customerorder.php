<?php
// Include the database connection file
include '../General/db_connect.php';
session_start(); // Start session to access user session variables
// Set timezone to Hong Kong
date_default_timezone_set("Asia/Hong_Kong");

// Check if customer is logged in using session data
if (!isset($_SESSION['cid']) || $_SESSION['role'] != 'customer') {
  echo "Please log in first!";
  header("Location: ../General/login.php"); // Redirect to login if not logged in as customer
  exit;
}

// Get customer ID from session
$customerID = $_SESSION['cid'];

// Fetch customer information from the database
$customerQuery = mysqli_query($conn, "SELECT cname, cpassword, ctel, caddr, company FROM customer WHERE cid = '$customerID'");
$customerInfo = mysqli_fetch_assoc($customerQuery);
if (!$customerInfo) {
  die("Customer not found!");
}

// Store customer details in variables for use
$customerName = $customerInfo['cname'];
$customerPassword = $customerInfo['cpassword'];
$customerPhone = $customerInfo['ctel'];
$customerAddress = $customerInfo['caddr'];
$customerCompany = $customerInfo['company'];

// Initialize variables to store messages and total price
$successMessage = '';
$errorMessage = '';
$totalPrice = 0; // Used to store final converted cost

// If form is submitted (order is placed)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get posted form values
  $selectedProductID = $_POST['pid'];
  $selectedQuantity = $_POST['oqty'];
  $chosenCurrency = $_POST['currency'];

  // Validate form inputs
  if (empty($selectedProductID) || empty($selectedQuantity) || empty($chosenCurrency)) {
    $errorMessage = "Please select a product, quantity, and currency!";
  } else {
    // Query to get product price
    $productQuery = mysqli_query($conn, "SELECT pcost FROM product WHERE pid = '$selectedProductID'");
    $productDetails = mysqli_fetch_assoc($productQuery);

    if ($productDetails) {
      // Calculate original order cost in USD
      $orderCost = $productDetails['pcost'] * $selectedQuantity;

      // Check if enough materials are available for the product
      $matQuery = mysqli_query($conn, "SELECT pm.mid, pm.pmqty, m.mrqty 
                                           FROM prodmat pm 
                                           JOIN material m ON pm.mid = m.mid 
                                           WHERE pm.pid = '$selectedProductID'");
      $canOrder = true; // Assume order can be placed

      // Loop through each required material
      while ($matRow = mysqli_fetch_assoc($matQuery)) {
        $neededQty = $matRow['pmqty'] * $selectedQuantity; // Total required material
        if ($matRow['mrqty'] < $neededQty) {
          $canOrder = false; // Not enough material
          $errorMessage = "Not enough reserved stock for material ID " . $matRow['mid'] . "!";
          break;
        }
      }

      if ($canOrder) {
        // Set current and delivery date
        $orderDate = date("Y-m-d H:i:s");
        $deliveryDate = date("Y-m-d H:i:s", strtotime("+3 days"));

        // Insert new order record into orders table
        $insertQuery = "INSERT INTO orders (odate, pid, oqty, ocost, cid, odeliverdate, ostatus) 
                               VALUES ('$orderDate', '$selectedProductID', '$selectedQuantity', '$orderCost', '$customerID', '$deliveryDate', '1')";

        if (mysqli_query($conn, $insertQuery)) {
          // Reset result pointer to loop through material rows again
          mysqli_data_seek($matQuery, 0);
          // Deduct required quantity from material reserved quantity
          while ($matRow = mysqli_fetch_assoc($matQuery)) {
            $mid = $matRow['mid'];
            $neededQty = $matRow['pmqty'] * $selectedQuantity;
            $updateQuery = "UPDATE material 
                                       SET mrqty = mrqty - $neededQty 
                                       WHERE mid = '$mid'";
            mysqli_query($conn, $updateQuery); // Update material quantity
          }

          // Call Flask API for currency conversion of order cost
          $successMessage = "Order placed! Converting total to $chosenCurrency...";
          $flaskURL = "http://127.0.0.1:8080/cost_convert/$orderCost/$chosenCurrency/1"; // Pass cost, currency, and dummy rate
          $response = @file_get_contents($flaskURL); // @ hides PHP warning

          if ($response !== false) {
            $data = json_decode($response, true);
            if ($data['result'] == 'accepted') {
              $convertedAmount = number_format($data['converted_amount'], 2);
              $successMessage .= " Total: $convertedAmount $chosenCurrency (converted via Flask API)";
            } else {
              $successMessage .= " But currency conversion failed: " . $data['reason'];
            }
          } else {
            // Show clean user message instead of raw warning
            $successMessage .= " But currency conversion could not be completed (API not available).";
          }
          // Get API response

          if ($response !== false) {
            $data = json_decode($response, true); // Convert JSON to PHP array
            if ($data['result'] == 'accepted') {
              // Format and display converted amount
              $convertedAmount = number_format($data['converted_amount'], 2);
              $successMessage .= " Total: $convertedAmount $chosenCurrency (converted via Flask API)";
            } else {
              // Handle API rejection with reason
              $successMessage .= " But currency conversion failed: " . $data['reason'];
            }
          } else {
            // Handle API connection failure
            $successMessage .= " But currency conversion failed: Could not reach Flask API";
          }

        } else {
          // Handle insert failure
          $errorMessage = "Problem placing order!";
        }
      }
    } else {
      // Handle invalid product ID
      $errorMessage = "Product not found!";
    }
  }
}

// Hardcoded list of products to simulate a product catalog
$products = [
    ['pid' => 1, 'pname' => 'Cyberpunk Truck C204', 'pcost' => 19.90],
    ['pid' => 2, 'pname' => 'XDD Wooden Plane', 'pcost' => 9.90],
    ['pid' => 3, 'pname' => 'iRobot 3233GG', 'pcost' => 249.90],
    ['pid' => 4, 'pname' => 'Apex Ball Helicopter M1297', 'pcost' => 30.00],
    ['pid' => 5, 'pname' => 'RoboKat AI Cat Robot', 'pcost' => 499.00]
];

// Sort product list by price if requested via URL
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'pid';
if ($sortBy == 'pcost') {
  usort($products, function($a, $b) {
    return $a['pcost'] - $b['pcost']; // Sort ascending by product cost
  });
}
// Start HTML using heredoc
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Place Order</title>
    <script src="customerorder.js" defer></script>
    <link rel="stylesheet" href="../Customer_css/customerOrder.css">
</head>
<body>
<div class="sidebar">
    <a href="customerDashboard.php">Home</a>
    <a href="customerorder.php">Order</a>
    <a href="customerVieworder.php">Order Record</a>
    <a href="updateProfile.php">Update Profile</a>
    <a href="customerDelete.php">Delete Order</a>
    <a href="../General/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>
<div class="content">
    <h1>SMILE & SUNSHINE TOY CO. LTD</h1>
HTML;

// Show messages
if ($errorMessage) {
  echo "<p class='error'>$errorMessage</p>";
}
if ($successMessage) {
  echo "<p class='success'>$successMessage</p>";
}

echo <<<HTML
    <div class="product-container">
HTML;

// Show products
foreach ($products as $product) {
  $pid = $product['pid'];
  $pname = $product['pname'];
  $pcost = $product['pcost'];
  $formattedCost = number_format($pcost, 2);
  echo <<<HTML
        <div class="product-item" data-pid="$pid">
            <img src="../images/product/$pid.jpg" alt="$pname" onerror="this.src='../images/placeholder.jpg';">
            <h3>$pname</h3>
            <p class="price">$$formattedCost USD</p>
            <div class="quantity-controls">
                <button class="minus">-</button>
                <input type="number" id="quantity_$pid" value="0" min="0">
                <button class="plus">+</button>
            </div>
        </div>
HTML;
}

echo <<<HTML
    </div>
    <form method="POST" action="" class="order-form" onsubmit="return confirmSelection()">
        <input type="hidden" id="pid" name="pid" value="">
        <input type="hidden" id="oqty" name="oqty" value="0">
        <div class="form-group">
            <label for="currency">Currency:</label>
            <select id="currency" name="currency" onchange="updateTotal()">
                <option value="HKD">HKD</option>
                <option value="EUR">EUR</option>
                <option value="JPY">JPY</option>
            </select>
        </div>
        <button type="submit" class="submit-order">Place Order</button>
        <div id="totalPrice" style="margin-top: 10px; font-weight: bold;">Total Price: 0.00 HKD</div>
    </form>
</div>
</body>
</html>
HTML;

mysqli_close($conn);
?>

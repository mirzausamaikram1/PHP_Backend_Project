<?php
include '../General/db_connect.php';
session_start();

// Set up some empty boxes to store messages and errors
$errorMessage = '';
$successMessage = '';

// Show some debug info to check if things are working
echo "<pre>";
echo "Session Customer ID: " . (isset($_SESSION['cid']) ? $_SESSION['cid'] : "Not set") . "\n";
echo "Session Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : "Not set") . "\n";
//echo "Request Type: " . $_SERVER["REQUEST_METHOD"] . "\n";
echo "</pre>";

if (!isset($_SESSION['cid']) || $_SESSION['role'] != 'customer') {
  echo "Going back to login because customer ID or role is missing.<br>";
  header("Location: ../General/login.php");
  exit;
}

// Get customer info from the database
$customerID = $_SESSION['cid'];
$customerQuery = mysqli_query($conn, "SELECT cname, cpassword, ctel, caddr, company FROM customer WHERE cid = '$customerID'");
if (!$customerQuery) {
  die("Problem getting customer info: " . mysqli_error($conn));
}
$customerInfo = mysqli_fetch_assoc($customerQuery);
if (!$customerInfo) {
  die("No customer found with ID: $customerID");
}

// Get the customer details like name and address
$customerName = $customerInfo['cname'];
$customerPassword = $customerInfo['cpassword'];
$customerPhone = $customerInfo['ctel'];
$customerAddress = $customerInfo['caddr'];
$customerCompany = $customerInfo['company'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get the form data
  $productID = isset($_POST['pid']) ? (int)$_POST['pid'] : 0;
  $orderQuantity = isset($_POST['oqty']) ? (int)$_POST['oqty'] : 1;
  $chosenCurrency = isset($_POST['currency']) ? $_POST['currency'] : '';

  echo "<pre>";
  echo "Form Data: productID=$productID, orderQuantity=$orderQuantity, chosenCurrency=$chosenCurrency\n";
  echo "</pre>";

  if (empty($productID) || empty($orderQuantity) || empty($chosenCurrency)) {
    $errorMessage = "Please fill in all fields!";
  } else {
    $productQuery = mysqli_query($conn, "SELECT pcost FROM product WHERE pid = '$productID'");
    if (!$productQuery) {
      die("Problem getting product price: " . mysqli_error($conn));
    }
    $productDetails = mysqli_fetch_assoc($productQuery);
    if ($productDetails) {
      $orderCost = $productDetails['pcost'] * $orderQuantity;
      $currencyRates = ['HKD' => 7.8, 'EUR' => 0.82, 'JPY' => 110];
      if (!isset($currencyRates[$chosenCurrency])) {
        $errorMessage = "Please pick a valid currency!";
      } else {
        $totalInCurrency = $orderCost * $currencyRates[$chosenCurrency];
        $orderID = 'O' . rand(100, 999);
        $orderDate = date("Y-m-d H:i:s");
        $deliveryDate = date("Y-m-d H:i:s", strtotime("+3 days"));

        $checkOrderQuery = mysqli_query($conn, "SELECT oid FROM orders WHERE oid = '$orderID'");
        if (!$checkOrderQuery) {
          die("Problem checking order ID: " . mysqli_error($conn));
        }
        if (mysqli_num_rows($checkOrderQuery) > 0) {
          $errorMessage = "This order ID is already used!";
        } else {
          $insertQuery = "INSERT INTO orders (oid, odate, pid, oqty, ocost, cid, odeliverdate, ostatus) 
                                   VALUES ('$orderID', '$orderDate', '$productID', '$orderQuantity', '$orderCost', '$customerID', '$deliveryDate', '1')";
          if (mysqli_query($conn, $insertQuery)) {
            $stockQuery = mysqli_query($conn, "SELECT pmqty FROM prodmat WHERE pid = '$productID'");
            if (!$stockQuery) {
              die("Problem checking stock: " . mysqli_error($conn));
            }
            $stockInfo = mysqli_fetch_assoc($stockQuery);
            if ($stockInfo && $stockInfo['pmqty'] >= $orderQuantity) {
              $updateStockQuery = mysqli_query($conn, "UPDATE prodmat SET pmqty = pmqty - $orderQuantity WHERE pid = '$productID'");
              if (!$updateStockQuery) {
                die("Problem updating stock: " . mysqli_error($conn));
              }
              $successMessage = "Order placed! Total: $totalInCurrency $chosenCurrency";
            } else {
              $errorMessage = "Not enough stock available!";
              mysqli_query($conn, "DELETE FROM orders WHERE oid = '$orderID'"); // Remove the order
            }
          } else {
            $errorMessage = "Problem placing order: " . mysqli_error($conn);
          }
        }
      }
    } else {
      $errorMessage = "Product not found!";
    }
  }
}

// Get products that have stock
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'pid';
$productQuery = "SELECT * FROM product WHERE pid IN (SELECT pid FROM prodmat WHERE pmqty > 0) ORDER BY $sortBy";
echo "<pre>";
echo "Product Query: $productQuery\n";
echo "</pre>";
$products = mysqli_query($conn, $productQuery);
if (!$products) {
  die("Problem getting products: " . mysqli_error($conn));
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Place Order</title>
    <link rel=\"stylesheet\" href=\"../Customer_css/customerOrder.css\">
</head>
<body>
<div class=\"sidebar\">
    <a href=\"customerDashboard.php\">Home</a>
    <a href=\"customerOrder.php\">Order</a>
    <a href=\"customerVieworder.php\">View Orders</a>
    <a href=\"updateProfile.php\">Update Profile</a>
    <a href=\"customerDelete.php\">Delete Order</a>
    <a href=\"../php/logout.php\">Logout</a>
</div>
<div class=\"content\">
    <h2>Place Order</h2>";

if ($errorMessage) {
  echo "<p class=\"error\">$errorMessage</p>";
}
if ($successMessage) {
  echo "<div class=\"success-message\" style=\"display: block;\">$successMessage</div>";
}

echo "<div class=\"container\">
    <div class=\"header\">
        <h1>Smile & Sunshine Toy Co. Ltd</h1>
    </div>
    <div class=\"title\">
        <h2>Place Your Order</h2>
    </div>
    <div class=\"sort\">
        <form method=\"GET\" action=\"\">
            <label for=\"sort-column\">Sort By:</label>
            <select id=\"sort-column\" name=\"sort\" onchange=\"this.form.submit()\">
                <option value=\"pid\" " . ($sortBy == 'pid' ? 'selected' : '') . ">Product ID</option>
                <option value=\"pcost\" " . ($sortBy == 'pcost' ? 'selected' : '') . ">Product Price</option>
            </select>
        </form>
    </div>
    <div class=\"product-list\">
        <h2>Products</h2>";

if (mysqli_num_rows($products) > 0) {
  while ($product = mysqli_fetch_assoc($products)) {
    $imagePath = "../Customer_Usama/images/products/{$product['pid']}.jpg";
    $defaultImage = "../Customer_Usama/images/products/default.jpg"; // Make sure this file exists
    echo "<div class=\"product\">
                <span>(ID: {$product['pid']}) {$product['pname']}</span>
                <img src=\"" . (file_exists($imagePath) ? $imagePath : $defaultImage) . "\" alt=\"{$product['pname']}\">
                <span>Price: \${$product['pcost']}.00</span>
            </div>";
  }
} else {
  echo "<p class=\"error\">No products available with stock.</p>";
}

echo "</div>
    <form class=\"order-form\" method=\"POST\" action=\"\">
        <div class=\"form-group\">
            <label for=\"Cid\">Customer ID:</label>
            <input type=\"text\" id=\"Cid\" name=\"Cid\" value=\"$customerID\" readonly />
        </div>
        <div class=\"form-group\">
            <label for=\"cname\">Customer Name:</label>
            <input type=\"text\" id=\"cname\" name=\"cname\" value=\"$customerName\" readonly />
        </div>
        <div class=\"form-group\">
            <label for=\"cpassword\">Password:</label>
            <input type=\"text\" id=\"cpassword\" name=\"cpassword\" value=\"$customerPassword\" readonly />
        </div>
        <div class=\"form-group\">
            <label for=\"ctel\">Telephone:</label>
            <input type=\"text\" id=\"ctel\" name=\"ctel\" value=\"$customerPhone\" readonly />
        </div>
        <div class=\"form-group\">
            <label for=\"caddr\">Address:</label>
            <input type=\"text\" id=\"caddr\" name=\"caddr\" value=\"$customerAddress\" readonly />
        </div>
        <div class=\"form-group\">
            <label for=\"company\">Company:</label>
            <input type=\"text\" id=\"company\" name=\"company\" value=\"$customerCompany\" readonly />
        </div>
        <div class=\"form-group\">
            <label for=\"pid\">Select Product:</label>
            <select id=\"pid\" name=\"pid\" required>
                <option value=\"\">-- Select a Product --</option>";

$products = mysqli_query($conn, $productQuery); // Start over to list products
if ($products && mysqli_num_rows($products) > 0) {
  while ($product = mysqli_fetch_assoc($products)) {
    echo "<option value=\"{$product['pid']}\">{$product['pname']} (\${$product['pcost']}.00)</option>";
  }
} else {
  echo "<option disabled>No products available</option>";
}

echo "</select>
        </div>
        <div class=\"form-group\">
            <label for=\"oqty\">Quantity:</label>
            <input type=\"number\" id=\"oqty\" name=\"oqty\" min=\"1\" value=\"1\" required />
        </div>
        <div class=\"form-group\">
            <label for=\"currency\">Currency:</label>
            <select id=\"currency\" name=\"currency\">
                <option value=\"HKD\">HKD (Hong Kong Dollar)</option>
                <option value=\"EUR\">EUR (Euro)</option>
                <option value=\"JPY\">JPY (Japanese Yen)</option>
            </select>
        </div>
        <button type=\"submit\" class=\"place-order\">Place Order</button>
    </form>
</div>
</div>
</body>
</html>";

mysqli_close($conn);
?>
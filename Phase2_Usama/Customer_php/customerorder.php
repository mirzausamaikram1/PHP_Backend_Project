<?php
// Include the database connection file
include '../General/db_connect.php';
session_start();

// Check if customer is logged in
if (!isset($_SESSION['cid']) || $_SESSION['role'] != 'customer') {
    echo "Please log in first!";
    header("Location: ../General/login.php");
    exit;
}

// Get customer info using their ID from the session
$customerID = $_SESSION['cid'];
$customerQuery = mysqli_query($conn, "SELECT cname, cpassword, ctel, caddr, company FROM customer WHERE cid = '$customerID'");
$customerInfo = mysqli_fetch_assoc($customerQuery);
if (!$customerInfo) {
    die("Customer not found!");
}

// Store customer details
$customerName = $customerInfo['cname'];
$customerPassword = $customerInfo['cpassword'];
$customerPhone = $customerInfo['ctel'];
$customerAddress = $customerInfo['caddr'];
$customerCompany = $customerInfo['company'];

// Handle form submission when the user places an order
$successMessage = '';
$errorMessage = '';
$totalPrice = 0; // Initialize total price
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedProductID = $_POST['pid'];
    $selectedQuantity = $_POST['oqty'];
    $chosenCurrency = $_POST['currency'];

    // Check if all fields are filled
    if (empty($selectedProductID) || empty($selectedQuantity) || empty($chosenCurrency)) {
        $errorMessage = "Please select a product, quantity, and currency!";
    } else {
        // Get product cost
        $productQuery = mysqli_query($conn, "SELECT pcost FROM product WHERE pid = '$selectedProductID'");
        $productDetails = mysqli_fetch_assoc($productQuery);
        if ($productDetails) {
            $orderCost = $productDetails['pcost'] * $selectedQuantity;

            // Check material stock availability using mrqty (reserved quantity)
            $matQuery = mysqli_query($conn, "SELECT pm.mid, pm.pmqty, m.mrqty 
                                           FROM prodmat pm 
                                           JOIN material m ON pm.mid = m.mid 
                                           WHERE pm.pid = '$selectedProductID'");
            $canOrder = true;
            while ($matRow = mysqli_fetch_assoc($matQuery)) {
                $neededQty = $matRow['pmqty'] * $selectedQuantity;
                if ($matRow['mrqty'] < $neededQty) {
                    $canOrder = false;
                    $errorMessage = "Not enough reserved stock for material ID " . $matRow['mid'] . "!";
                    break;
                }
            }

            if ($canOrder) {
                // Insert the order with a delivery date 3 days from now (adjustable per project needs)
                $orderDate = date("Y-m-d H:i:s");
                $deliveryDate = date("Y-m-d H:i:s", strtotime("+3 days"));
                $insertQuery = "INSERT INTO orders (odate, pid, oqty, ocost, cid, odeliverdate, ostatus) 
                               VALUES ('$orderDate', '$selectedProductID', '$selectedQuantity', '$orderCost', '$customerID', '$deliveryDate', '1')";
                if (mysqli_query($conn, $insertQuery)) {
                    // Update material reserved quantities
                    mysqli_data_seek($matQuery, 0); // Go back to start of result
                    while ($matRow = mysqli_fetch_assoc($matQuery)) {
                        $mid = $matRow['mid'];
                        $neededQty = $matRow['pmqty'] * $selectedQuantity;
                        $updateQuery = "UPDATE material 
                                       SET mrqty = mrqty - $neededQty 
                                       WHERE mid = '$mid'";
                        mysqli_query($conn, $updateQuery);
                    }
                    $successMessage = "Order placed! Converting total to $chosenCurrency...";
                    // Call Python Flask API for currency conversion
                    $exchangeRates = ['HKD' => 7.8, 'EUR' => 0.82, 'JPY' => 110];
                    $rate = $exchangeRates[$chosenCurrency];
                    $url = "http://127.0.0.1:8080/cost_convert/$orderCost/$chosenCurrency/$rate";
                    $response = file_get_contents($url);
                    $data = json_decode($response, true);
                    if ($data['result'] == 'accepted') {
                        $totalPrice = $data['converted_amount'];
                        $successMessage .= " Total: " . number_format($totalPrice, 2) . " $chosenCurrency";
                    } else {
                        $successMessage .= " Conversion failed: " . $data['reason'];
                    }
                } else {
                    $errorMessage = "Problem placing order!";
                }
            }
        } else {
            $errorMessage = "Product not found!";
        }
    }
}

// Manually list products (based on your image)
$products = [
    ['pid' => 1, 'pname' => 'Cyberpunk Truck C204', 'pcost' => 19.90],
    ['pid' => 2, 'pname' => 'XDD Wooden Plane', 'pcost' => 9.90],
    ['pid' => 3, 'pname' => 'iRobot 3233GG', 'pcost' => 249.90],
    ['pid' => 4, 'pname' => 'Apex Ball Helicopter M1297', 'pcost' => 30.00],
    ['pid' => 5, 'pname' => 'RoboKat AI Cat Robot', 'pcost' => 499.00]
];

$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'pid';
if ($sortBy == 'pcost') {
    usort($products, function($a, $b) {
        return $a['pcost'] - $b['pcost'];
    });
}

// Start HTML using heredoc
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Place Order</title>
    <script src="../Customer_php/customerorder.js" defer></script> <!-- Corrected path -->
    <link rel="stylesheet" href="../Customer_css/customerOrder.css">
</head>
<body>
<div class="sidebar">
    <a href="customerDashboard.php">Home</a>
    <a href="customerOrder.php">Order</a>
    <a href="customerVieworder.php">Order Record</a>
    <a href="updateProfile.php">Information</a>
    <a href="customerDelete.php">Delete Order</a>
    <a href="../php/logout.php">Logout</a>
</div>
<div class="content">
    <h1>SMILE & SUNSHINE TOY CO. LTD</h1>
HTML;

// Show error or success messages
if ($errorMessage) {
    echo "<p class='error'>$errorMessage</p>";
}
if ($successMessage) {
    echo "<p class='success'>$successMessage</p>";
}

echo <<<HTML
    <div class="product-container">
HTML;

// Display products in a grid layout with selection
foreach ($products as $product) {
    $pid = $product['pid'];
    $pname = $product['pname'];
    $pcost = $product['pcost'];
    $formattedCost = number_format($pcost, 2); // Format price outside heredoc
    echo <<<HTML
        <div class="product-item" data-pid="$pid">
            <img src="../images/product/$pid.jpg" alt="$pname" onerror="this.src='../images/placeholder.jpg';">
            <h3>$pname</h3>
            <p class="price">$$formattedCost USD</p> <!-- Use formatted value -->
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

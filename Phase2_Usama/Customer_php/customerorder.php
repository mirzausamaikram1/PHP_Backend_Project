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
    $productID = $_POST['pid'];
    $orderQuantity = $_POST['oqty'];
    $chosenCurrency = $_POST['currency'];

    // Check if all fields are filled
    if (empty($productID) || empty($orderQuantity) || empty($chosenCurrency)) {
        $errorMessage = "Please fill in all fields!";
    } else {
        // Get product cost
        $productQuery = mysqli_query($conn, "SELECT pcost FROM product WHERE pid = '$productID'");
        $productDetails = mysqli_fetch_assoc($productQuery);
        if ($productDetails) {
            $orderCost = $productDetails['pcost'] * $orderQuantity;

            // Check material stock availability
            $matQuery = mysqli_query($conn, "SELECT pm.mid, pm.pmqty, m.mqty 
                                           FROM prodmat pm 
                                           JOIN material m ON pm.mid = m.mid 
                                           WHERE pm.pid = '$productID'");
            $canOrder = true;
            while ($matRow = mysqli_fetch_assoc($matQuery)) {
                $neededQty = $matRow['pmqty'] * $orderQuantity;
                if ($matRow['mqty'] < $neededQty) {
                    $canOrder = false;
                    $errorMessage = "Not enough stock for material ID " . $matRow['mid'] . "!";
                    break;
                }
            }

            if ($canOrder) {
                // Insert the order
                $orderDate = date("Y-m-d H:i:s");
                $deliveryDate = date("Y-m-d H:i:s", strtotime("+3 days"));
                $insertQuery = "INSERT INTO orders (odate, pid, oqty, ocost, cid, odeliverdate, ostatus) 
                               VALUES ('$orderDate', '$productID', '$orderQuantity', '$orderCost', '$customerID', '$deliveryDate', '1')";
                if (mysqli_query($conn, $insertQuery)) {
                    // Update material quantities
                    mysqli_data_seek($matQuery, 0); // Go back to start of result
                    while ($matRow = mysqli_fetch_assoc($matQuery)) {
                        $mid = $matRow['mid'];
                        $neededQty = $matRow['pmqty'] * $orderQuantity;
                        $updateQuery = "UPDATE material 
                                       SET mqty = mqty - $neededQty, 
                                           mrqty = mrqty + $neededQty 
                                       WHERE mid = '$mid'";
                        mysqli_query($conn, $updateQuery);
                    }
                    $successMessage = "Order placed! Calculating total in $chosenCurrency...";
                    // Local currency conversion (fallback until Python API is running)
                    $rates = ['HKD' => 7.8, 'EUR' => 0.82, 'JPY' => 110];
                    $totalPrice = $orderCost * $rates[$chosenCurrency];
                    $successMessage .= " Total: " . number_format($totalPrice, 2) . " $chosenCurrency";
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
    ['pid' => 1, 'pname' => 'Cyber Truck C204', 'pcost' => 19.90],
    ['pid' => 2, 'pname' => 'XDD Woode', 'pcost' => 9.90],
    ['pid' => 3, 'pname' => 'iRobot 3233G', 'pcost' => 249.90],
    ['pid' => 4, 'pname' => 'Apex Ball Helicopter M1297', 'pcost' => 30.00],
    ['pid' => 5, 'pname' => 'RoboK AI Cat', 'pcost' => 499.00]
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
    <link rel="stylesheet" href="../Customer_css/customerOrder.css">
    <script>
        function updateTotal() {
            var productSelect = document.getElementById('pid');
            var quantityInput = document.getElementById('oqty');
            var currencySelect = document.getElementById('currency');
            var totalDisplay = document.getElementById('totalPrice');

            var productId = productSelect.value;
            var quantity = parseInt(quantityInput.value) || 1;
            var currency = currencySelect.value;
            var basePrice = 0;

            // Manually set prices based on product ID
            switch(productId) {
                case '1': basePrice = 19.90; break;
                case '2': basePrice = 9.90; break;
                case '3': basePrice = 249.90; break;
                case '4': basePrice = 30.00; break;
                case '5': basePrice = 499.00; break;
                default: basePrice = 0;
            }

            var subtotal = basePrice * quantity;
            var rate = (currency == 'HKD') ? 7.8 : (currency == 'EUR') ? 0.82 : 110;
            var total = subtotal * rate;
            totalDisplay.textContent = 'Total Price: ' + total.toFixed(2) + ' ' + currency;
        }
    </script>
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
    <h2>Place Order</h2>
HTML;

// Show error or success messages
if ($errorMessage) {
    echo "<p class='error'>$errorMessage</p>";
}
if ($successMessage) {
    echo "<p class='success'>$successMessage</p>";
}

echo <<<HTML
    <div class="container">
        <div class="header">
            <h1>Smile & Sunshine Toy Co. Ltd</h1>
        </div>
        <div class="title">
            <h2>Place Your Order</h2>
        </div>
        <div class="sort">
            <form method="GET" action="">
                <label for="sort-column">Sort By:</label>
                <select id="sort-column" name="sort" onchange="this.form.submit()">
                    <option value="pid" . ($sortBy == 'pid' ? 'selected' : '') . ">Product ID</option>
                    <option value="pcost" . ($sortBy == 'pcost' ? 'selected' : '') . ">Product Price</option>
                </select>
            </form>
        </div>
        <div class="product-list">
            <h2>Products</h2>
HTML;

// Manually list products with simple HTML and image fallback
echo "<div class='product'><span>(ID: 1) Cyber Truck C204</span><img src='../images/product/1.jpg' alt='Cyber Truck C204' \"><span>Price: $19.90</span></div>";
echo "<div class='product'><span>(ID: 2) XDD Woode</span><img src='../images/product/2.jpg' alt='XDD Woode' \"><span>Price: $9.90</span></div>";
echo "<div class='product'><span>(ID: 3) iRobot 3233G</span><img src='../images/product/3.jpg' alt='iRobot 3233G'\"><span>Price: $249.90</span></div>";
echo "<div class='product'><span>(ID: 4) Apex Ball Helicopter M1297</span><img src='../images/product/4.jpg' alt='Apex Ball Helicopter M1297' \"><span>Price: $30.00</span></div>";
echo "<div class='product'><span>(ID: 5) RoboK AI Cat</span><img src='../images/product/5.jpg' alt='RoboK AI Cat' \"><span>Price: $499.00</span></div>";

echo <<<HTML
        </div>
        <form class="order-form" method="POST" action="" oninput="updateTotal()">
            <div class="form-group">
                <label for="Cid">Customer ID:</label>
                <input type="text" id="Cid" name="Cid" value="$customerID" readonly />
            </div>
            <div class="form-group">
                <label for="cname">Customer Name:</label>
                <input type="text" id="cname" name="cname" value="$customerName" readonly />
            </div>
            <div class="form-group">
                <label for="cpassword">Password:</label>
                <input type="text" id="cpassword" name="cpassword" value="$customerPassword" readonly />
            </div>
            <div class="form-group">
                <label for="ctel">Telephone:</label>
                <input type="text" id="ctel" name="ctel" value="$customerPhone" readonly />
            </div>
            <div class="form-group">
                <label for="caddr">Address:</label>
                <input type="text" id="caddr" name="caddr" value="$customerAddress" readonly />
            </div>
            <div class="form-group">
                <label for="company">Company:</label>
                <input type="text" id="company" name="company" value="$customerCompany" readonly />
            </div>
            <div class="form-group">
                <label for="pid">Select Product:</label>
                <select id="pid" name="pid" required>
                    <option value="">-- Select a Product --</option>
                    <option value="1">Cyber Truck C204 ($19.90)</option>
                    <option value="2">XDD Woode ($9.90)</option>
                    <option value="3">iRobot 3233G ($249.90)</option>
                    <option value="4">Apex Ball Helicopter M1297 ($30.00)</option>
                    <option value="5">RoboK AI Cat ($499.00)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="oqty">Quantity:</label>
                <input type="number" id="oqty" name="oqty" min="1" value="1" required />
            </div>
            <div class="form-group">
                <label for="currency">Currency:</label>
                <select id="currency" name="currency">
                    <option value="HKD">HKD</option>
                    <option value="EUR">EUR</option>
                    <option value="JPY">JPY</option>
                </select>
            </div>
            <button type="submit" class="place-order">Place Order</button>
            <div id="totalPrice" style="margin-top: 10px; font-weight: bold;">Total Price: 0.00 HKD</div>
        </form>
    </div>
</div>
</body>
</html>
HTML;

mysqli_close($conn);
?>
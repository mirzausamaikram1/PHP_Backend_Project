<?php
// Include database connection
include 'db_connect.php';
session_start();

// Check if user is logged in as staff
if (!isset($_SESSION['sid']) || $_SESSION['role'] !== 'staff') {
    header("Location: customerLogin.php");
    exit;
}

// Initialize variables
$order_id = isset($_POST['update_oid']) ? mysqli_real_escape_string($conn, $_POST['update_oid']) : '';
$new_status = isset($_POST['ostatus']) ? mysqli_real_escape_string($conn, $_POST['ostatus']) : '';
$new_quantity = isset($_POST['oqty']) ? mysqli_real_escape_string($conn, $_POST['oqty']) : '';

$error = '';
$message = '';
$selected_order = null; // Initialize $selected_order to avoid undefined variable warning

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $order_id != '') {
    // Start transaction
    mysqli_query($conn, "START TRANSACTION");

    // Update order status and quantity with corrected ocost calculation
    $update_order = "UPDATE orders o 
                     JOIN product p ON o.pid = p.pid 
                     SET o.oqty = '$new_quantity', o.ostatus = '$new_status', o.ocost = p.pcost * '$new_quantity' 
                     WHERE o.oid = '$order_id'";
    $result_order = mysqli_query($conn, $update_order);

    if ($result_order) {
        // Get product ID and old quantity
        $order_query = "SELECT pid, oqty FROM orders WHERE oid = '$order_id'";
        $order_result = mysqli_query($conn, $order_query);
        $order_data = mysqli_fetch_assoc($order_result);
        $pid = $order_data['pid'];
        $old_quantity = $order_data['oqty'];

        // Calculate quantity difference (cast to int to avoid string subtraction)
        $quantity_diff = (int)$new_quantity - (int)$old_quantity;

        // Update material reserved quantities
        $material_query = "SELECT mid, pmqty FROM prodmat WHERE pid = '$pid'";
        $material_result = mysqli_query($conn, $material_query);
        while ($material = mysqli_fetch_assoc($material_result)) {
            $mid = $material['mid'];
            $pmqty = $material['pmqty'];
            $material_update = "UPDATE material SET mrqty = mrqty + ($quantity_diff * $pmqty) WHERE mid = '$mid'";
            mysqli_query($conn, $material_update);
        }

        // Commit transaction
        mysqli_query($conn, "COMMIT");
        $message = "Order and material updated successfully!";
    } else {
        // Rollback on error
        mysqli_query($conn, "ROLLBACK");
        $error = "Error updating order: " . mysqli_error($conn);
    }

    // Fetch updated order details after submission
    $selected_query = "SELECT o.oid, o.odate, p.pname, o.oqty, o.ocost, c.cname, c.ctel, c.caddr, o.odeliverdate, o.ostatus, m.mname 
                       FROM orders o 
                       JOIN product p ON o.pid = p.pid 
                       JOIN customer c ON o.cid = c.cid 
                       JOIN prodmat pm ON p.pid = pm.pid 
                       JOIN material m ON pm.mid = m.mid 
                       WHERE o.oid = '$order_id'";
    $selected_result = mysqli_query($conn, $selected_query);
    $selected_order = mysqli_fetch_assoc($selected_result);
}

// Fetch all orders for the dropdown
$orders_query = "SELECT o.oid, o.odate, p.pname, o.oqty, o.ocost, c.cname, c.ctel, c.caddr, o.odeliverdate, o.ostatus, m.mname 
                 FROM orders o 
                 JOIN product p ON o.pid = p.pid 
                 JOIN customer c ON o.cid = c.cid 
                 JOIN prodmat pm ON p.pid = pm.pid 
                 JOIN material m ON pm.mid = m.mid";
$orders_result = mysqli_query($conn, $orders_query);
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
        <a href="insertMaterials.php">Insert Materials</a>
        <a href="insertProducts.php">Insert Products</a>
        <a href="../General/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>

    <div class="content">
        <h1>Update Order Records</h1>

        <!-- Display Messages -->
        <?php if ($error): ?>
            <div class="error-message">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="success-message">
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <!-- Update Order Form -->
        <div class="update-form">
            <h2>Modify Order Details</h2>
            <form method="POST" action="">
                <label for="update_oid">Select Order to Update:</label>
                <select name="update_oid" id="update_oid" required onchange="this.form.submit()">
                    <option value="">-- Select Order --</option>
                    <?php
                    mysqli_data_seek($orders_result, 0); // Reset pointer
                    while ($order = mysqli_fetch_assoc($orders_result)) { ?>
                        <option value="<?php echo htmlspecialchars($order['oid']); ?>" <?php echo $order_id == $order['oid'] ? 'selected' : ''; ?>>
                            Order #<?php echo htmlspecialchars($order['oid']); ?> - <?php echo htmlspecialchars($order['pname']); ?> (Qty: <?php echo htmlspecialchars($order['oqty']); ?>)
                        </option>
                    <?php } ?>
                </select><br>

                <label for="oqty">New Quantity:</label>
                <input type="number" id="oqty" name="oqty" min="1" value="<?php echo $selected_order ? htmlspecialchars($selected_order['oqty']) : ''; ?>" required><br>

                <label for="ostatus">Order Status:</label>
                <select name="ostatus" id="ostatus" required>
                    <option value="1" <?php echo $selected_order && $selected_order['ostatus'] == 1 ? 'selected' : ''; ?>>Pending</option>
                    <option value="2" <?php echo $selected_order && $selected_order['ostatus'] == 2 ? 'selected' : ''; ?>>Accepted</option>
                    <option value="3" <?php echo $selected_order && $selected_order['ostatus'] == 3 ? 'selected' : ''; ?>>Rejected</option>
                </select><br><br>

                <button type="submit">Update Order</button>
            </form>
        </div>

        <!-- Order Details Table -->
        <div class="order-list">
            <h2>Current Order Details</h2>
            <?php if ($selected_order): ?>
                <table border="1">
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Cost</th>
                        <th>Customer Name</th>
                        <th>Customer Tel</th>
                        <th>Customer Address</th>
                        <th>Delivery Date</th>
                        <th>Status</th>
                        <th>Material</th>
                    </tr>
                    <tr>
                        <td><?php echo htmlspecialchars($selected_order['oid']); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['odate']); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['pname']); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['oqty']); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['ocost']); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['cname']); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['ctel']); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['caddr']); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['odeliverdate'] ?: 'N/A'); ?></td>
                        <td><?php echo $selected_order['ostatus'] == 1 ? 'Pending' : ($selected_order['ostatus'] == 2 ? 'Accepted' : 'Rejected'); ?></td>
                        <td><?php echo htmlspecialchars($selected_order['mname']); ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <p>Please select an order to view its details.</p>
            <?php endif; ?>
        </div>
    </div>
    </body>
    </html>

<?php
// Close connection
mysqli_close($conn);
?>
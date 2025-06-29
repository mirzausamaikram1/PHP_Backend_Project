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
$product_details = null; // Initialize variable for product details

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_pid'])) {
        $pid = mysqli_real_escape_string($conn, $_POST['delete_pid']);

        // Check if product has related orders
        $check_orders = "SELECT oid FROM orders WHERE pid = '$pid'";
        $orders_result = mysqli_query($conn, $check_orders);

        if (mysqli_num_rows($orders_result) == 0) {
            // Delete related records in prodmat table
            $delete_prodmat = "DELETE FROM prodmat WHERE pid = '$pid'";
            if (mysqli_query($conn, $delete_prodmat)) {
                // Delete the product from the product table
                $delete_sql = "DELETE FROM product WHERE pid = '$pid'";
                if (mysqli_query($conn, $delete_sql)) {
                    $message = "Product deleted successfully!";
                } else {
                    $error = "Error deleting product: " . mysqli_error($conn);
                }
            } else {
                $error = "Error deleting related materials: " . mysqli_error($conn);
            }
        } else {
            $error = "Cannot delete product with existing orders!";
        }
    } else if (isset($_POST['view_pid'])) {
        $pid = mysqli_real_escape_string($conn, $_POST['view_pid']);
        $product_query = "SELECT * FROM product WHERE pid = '$pid'";
        $product_result = mysqli_query($conn, $product_query);
        $product_details = mysqli_fetch_assoc($product_result);
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
        <a href="insertMaterials.php">Insert Materials</a>
        <a href="insertProducts.php">Insert Products</a>
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

        <form method="POST" action="">
            <label for="view_pid">Select Product to Delete:</label>
            <select name="view_pid" id="view_pid" required onchange="this.form.submit()">
                <option value="">-- Select Product --</option>
                <?php while ($product = mysqli_fetch_assoc($products)): ?>
                    <option value="<?php echo htmlspecialchars($product['pid']); ?>">
                        <?php echo htmlspecialchars($product['pname']); ?>
                    </option>
                <?php endwhile; ?>
            </select><br><br>
        </form>

        <?php if ($product_details): ?>
            <h2>Product Details</h2>
            <p><strong>Product ID:</strong> <?php echo htmlspecialchars($product_details['pid']); ?></p>
            <p><strong>Product Name:</strong> <?php echo htmlspecialchars($product_details['pname']); ?></p>
            <p><strong>Product Price:</strong> <?php echo htmlspecialchars($product_details['pcost']); ?></p>
            <p><strong>Product Description:</strong>
                <?php echo isset($product_details['description']) ? htmlspecialchars($product_details['description']) : 'No description available'; ?>
            </p>

            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                <input type="hidden" name="delete_pid" value="<?php echo htmlspecialchars($product_details['pid']); ?>">
                <button type="submit">Delete Product</button>
            </form>
        <?php endif; ?>
    </div>
    </body>
    </html>

<?php mysqli_close($conn); ?>
<?php
include '../General/db_connect.php';
session_start();

// Check if user is logged in as staff
if (!isset($_SESSION['sid']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../General/customerLogin.php");
    exit;
}

$error = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $mname = $_POST['mname'];
    $mqty = $_POST['mqty'];
    $mqty_reserved = $_POST['mqty_reserved'];
    $unit = $_POST['unit'];
    $reorder_level = $_POST['reorder_level'];
    $image = $_FILES['image']['name'];

    // Basic validation
    if (empty($mname) || empty($mqty) || empty($mqty_reserved) || empty($unit) || empty($reorder_level) || empty($image)) {
        $error = "Please fill in all fields!";
    } elseif ($mqty <= 0 || $mqty_reserved < 0 || $reorder_level <= 0) {
        $error = "Quantities and reorder level must be valid!";
    } elseif ($mqty_reserved > $mqty) {
        $error = "Reserved quantity cannot be more than available quantity!";
    } else {
        // Directory to store material images locally
        $target_dir = "../images/material/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Create folder if not exist
        }

        $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION)); // Get file extension

        // Insert material into DB (no image path in DB!)
        $sql = "INSERT INTO material (mname, mqty, mrqty, munit, mreorderqty)
                VALUES ('$mname', '$mqty', '$mqty_reserved', '$unit', '$reorder_level')";

        if (mysqli_query($conn, $sql)) {
            $last_mid = mysqli_insert_id($conn); // Get inserted material ID
            $new_image_name = $last_mid . '.' . $extension;
            $image_path = $target_dir . $new_image_name;

            // Save image locally with material ID as filename
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $message = "Material added successfully with local image!";
            } else {
                $error = "Material added but image upload failed!";
            }
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Material</title>
    <link rel="stylesheet" href="../Staff_css/insertMaterials.css">
    <style>
        .content { margin-left: 200px; padding: 20px; }
        .sidebar { width: 200px; position: fixed; top: 0; left: 0; bottom: 0; background: #f4f4f4; padding-top: 20px; }
        .sidebar a { display: block; padding: 10px; text-decoration: none; color: black; }
        .sidebar a:hover { background: #ddd; }
    </style>
</head>
<body>
<div class="sidebar">
    <a href="staffDashboard.php">Staff Dashboard</a>
    <a href="deleteProduct.php">Delete Product</a>
    <a href="reports.php">Generate Report</a>
    <a href="updateOrder.php">Update Order Records</a>
    <a href="insertProducts.php">Insert Product</a>
    <a href="../General/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
</div>
<div class="content">
    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($message): ?>
        <p style="color:green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <h2>Add New Material</h2>
        <label>Material Name:</label>
        <input type="text" name="mname" required><br>

        <label>Physical Quantity:</label>
        <input type="number" name="mqty" required min="1"><br>

        <label>Reserved Quantity:</label>
        <input type="number" name="mqty_reserved" required min="0"><br>

        <label>Unit (e.g., KG):</label>
        <input type="text" name="unit" required><br>

        <label>Re-order Level:</label>
        <input type="number" name="reorder_level" required min="1"><br>

        <label>Material Image:</label>
        <input type="file" name="image" required><br>

        <button type="submit">Add Material</button>
    </form>
</div>
</body>
</html>

<?php mysqli_close($conn); ?>
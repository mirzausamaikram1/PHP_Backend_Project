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
    $mname = $_POST['mname'];
    $mqty = $_POST['mqty'];
    $mqty_reserved = $_POST['mqty_reserved'];
    $unit = $_POST['unit'];
    $reorder_level = $_POST['reorder_level'];
    $image = $_FILES['image']['name'];

    // Validate all inputs
    if (empty($mname) || empty($mqty) || empty($mqty_reserved) || empty($unit) || empty($reorder_level) || empty($image)) {
        $error = "Please fill in all fields!";
    } elseif ($mqty <= 0 || $mqty_reserved < 0 || $reorder_level <= 0) {
        $error = "Physical Quantity, Reserved Quantity, and Re-order Level must be positive!";
    } elseif ($mqty_reserved > $mqty) {
        $error = "Reserved Quantity cannot exceed Physical Quantity!";
    } else {
        // Upload image to /Phase2_Usama/images/material/ and rename with mid
        $target_dir = "../images/material/"; // Move up one level to Phase2_Usama
        $absolute_path = __DIR__ . '/' . $target_dir; // Get absolute path for debugging
        if (!file_exists($absolute_path)) {
            mkdir($absolute_path, 0777, true);
        }
        // Get the original file extension
        $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        // Insert material to get mid first
        $sql = <<<EOD
            INSERT INTO material (mname, mqty, mrqty, munit, mreorderqty, mimage)
            VALUES ('$mname', '$mqty', '$mqty_reserved', '$unit', '$reorder_level', '')
        EOD;
        if (mysqli_query($conn, $sql)) {
            $last_mid = mysqli_insert_id($conn);
            $new_image_name = $last_mid . '.' . $extension;
            $image_path = $absolute_path . $new_image_name;

            // Move and rename the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                // Update the material with the new image name
                $update_sql = <<<EOD
                    UPDATE material SET mimage = '$new_image_name' WHERE mid = '$last_mid'
                EOD;
                mysqli_query($conn, $update_sql);
                $message = "Material added successfully!";
            } else {
                $error = "Error uploading image! Check permissions or path: " . $image_path;
                // Rollback: Delete material if image upload fails
                mysqli_query($conn, "DELETE FROM material WHERE mid = '$last_mid'");
            }
        } else {
            $error = "Error adding material: " . mysqli_error($conn);
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
        <a href="insertProducts.php">Insert Products</a>
        <a href="login.php">Logout</a>
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
            <label>Unit:</label>
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
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
    $pname = $_POST['pname'];
    $pdesc = $_POST['pdesc'];
    $pcost = $_POST['pcost'];
    $image = $_FILES['image']['name'];
    $materials = $_POST['mid'];
    $quantities = $_POST['m_qty'];

    // Check if all required fields are filled
    if (empty($pname) || empty($pdesc) || empty($pcost) || empty($image) || empty($materials[0])) {
        $error = "Please fill in all fields!";
    } elseif ($pcost <= 0) {
        $error = "Product cost must be positive!";
    } else {
        // Upload image to /images/product/ and rename with pid
        $target_dir = "images/product/"; // Relative to the script location
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        // Get the original file extension
        $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        // Insert product to get pid first
        $sql = <<<EOD
            INSERT INTO product (pname, pdesc, pcost, pimage)
            VALUES ('$pname', '$pdesc', '$pcost', '')
        EOD;
        if (mysqli_query($conn, $sql)) {
            $last_pid = mysqli_insert_id($conn);
            $new_image_name = $last_pid . '.' . $extension;
            $image_path = $target_dir . $new_image_name;

            // Move and rename the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                // Update the product with the new image name
                $update_sql = <<<EOD
                    UPDATE product SET pimage = '$new_image_name' WHERE pid = '$last_pid'
                EOD;
                mysqli_query($conn, $update_sql);

                // Insert materials
                $success = true;
                for ($i = 0; $i < count($materials); $i++) {
                    $mid = $materials[$i];
                    $m_qty = $quantities[$i];
                    if (!empty($mid) && $m_qty > 0) {
                        $mat_sql = <<<EOD
                            INSERT INTO prodmat (pid, mid, pmqty)
                            VALUES ('$last_pid', '$mid', '$m_qty')
                        EOD;
                        if (!mysqli_query($conn, $mat_sql)) {
                            $success = false;
                            break;
                        }
                    }
                }
                if ($success) {
                    $message = "Product added successfully!";
                } else {
                    $error = "Error adding materials!";
                    // Rollback: Delete product and image if materials fail
                    mysqli_query($conn, "DELETE FROM product WHERE pid = '$last_pid'");
                    unlink($image_path);
                }
            } else {
                $error = "Error uploading image!";
                // Rollback: Delete product if image upload fails
                mysqli_query($conn, "DELETE FROM product WHERE pid = '$last_pid'");
            }
        } else {
            $error = "Error adding product: " . mysqli_error($conn);
        }
    }
}

// Fetch materials for dropdown
$materials = mysqli_query($conn, "SELECT mid FROM material");
?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Add New Products</title>
        <link rel="stylesheet" href="../Staff_css/insertProducts.css">
        <style>
            .material-row { margin-bottom: 10px; }
            .add-material-btn { margin-top: 10px; }
        </style>
    </head>
    <body>
    <div class="sidebar">
        <a href="staffDashboard.php">Staff Dashboard</a>
        <a href="deleteProduct.php">Delete Product</a>
        <a href="reports.php">Generate Report</a>
        <a href="updateOrder.php">Update Order Records</a>
        <a href="insertMaterials.php">Insert Material</a>
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
            <h2>Add New Product</h2>
            <label>Product Name:</label>
            <input type="text" name="pname" required><br>
            <label>Product Description:</label>
            <input type="text" name="pdesc" required><br>
            <label>Product Image:</label>
            <input type="file" name="image" required><br>
            <label>Product Cost:</label>
            <input type="number" name="pcost" step="0.01" min="0.01" required><br>

            <div id="material-list">
                <div class="material-row">
                    <label>Select Material:</label>
                    <select name="mid[]">
                        <option value="">Select Material</option>
                        <?php
                        $material_names = [
                            1 => 'Cyberpunk Truck C204', // Cyberpunk Truck C204
                            2 => 'Wooden Plane',  // XDD Wooden Plane
                            3 => 'iRobot', // iRobot 3233GG
                            4 => 'Ball Helicopter', // Apex Ball Ball Helicopter M1297
                            5 => 'Cat' // RoboKat AI Cat Robot
                        ];
                        while ($row = mysqli_fetch_assoc($materials)):
                            ?>
                            <option value="<?php echo $row['mid']; ?>">
                                <?php echo htmlspecialchars($material_names[$row['mid']] ?? "Unknown Material"); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <label>Quantity:</label>
                    <input type="number" name="m_qty[]" min="1" placeholder="Quantity" required><br>
                </div>
            </div>
            <button type="button" class="add-material-btn" onclick="addMaterialRow()">Add Another Material</button><br><br>
            <button type="submit">Add Product</button>
        </form>
    </div>

    <script>
        let materialCount = 1;
        function addMaterialRow() {
            let list = document.getElementById('material-list');
            let row = document.createElement('div');
            row.className = 'material-row';
            row.innerHTML = `
            <label>Select Material:</label>
            <select name="mid[]">
                <option value="">Select Material</option>
                <?php
            mysqli_data_seek($materials, 0); // Reset pointer
            $material_names = [
                    1 => 'Cyberpunk Truck C204', // Cyberpunk Truck C204
                    2 => 'Wooden Plane',  // XDD Wooden Plane
                    3 => 'iRobot', // iRobot 3233GG
                    4 => 'Ball Helicopter', // Apex Ball Ball Helicopter M1297
                    5 => 'Cat' // RoboKat AI Cat Robot
            ];
            while ($row = mysqli_fetch_assoc($materials)):
            ?>
                    <option value="<?php echo $row['mid']; ?>">
                        <?php echo htmlspecialchars($material_names[$row['mid']] ?? "Unknown Material"); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <label>Quantity:</label>
            <input type="number" name="m_qty[]" min="1" placeholder="Quantity" required><br>
        `;
            list.appendChild(row);
            materialCount++;
        }
    </script>
    </body>
    </html>

<?php mysqli_close($conn); ?>
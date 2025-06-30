<?php
// Bring in the file that helps us connect to the database
include 'db_connect.php';
session_start(); // This is like a memory to remember who is logged in

$error = ''; // This will show a message if something goes wrong
$message = ''; // This will show a message if something goes right

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = mysqli_real_escape_string($conn, $_POST['username']); // Get the ID they typed (sid or cid)
    $password = mysqli_real_escape_string($conn, $_POST['password']); // Get the password they typed
    $role = isset($_POST['role']) ? mysqli_real_escape_string($conn, $_POST['role']) : ''; // Get the selected role

    if (empty($id) || empty($password) || empty($role)) {
        $error = "Please type your ID, password, and select a role!";
    } else {
        if ($role == 'staff') {
            // Check if they are a staff member using sid
            $staff_sql = "SELECT sid, spassword, sname, 'staff' AS role FROM staff WHERE sid = '$id'";
            $staff_result = mysqli_query($conn, $staff_sql);
            if ($staff_result && mysqli_num_rows($staff_result) > 0) {
                $user = mysqli_fetch_assoc($staff_result); // Get their details
                if ($password === $user['spassword']) { // Check if the password is correct
                    $_SESSION['sid'] = $user['sid']; // Save their ID
                    $_SESSION['role'] = $user['role']; // Save their role as 'staff'
                    header("Location: ../General/staffDashboard.php"); // Go to staff dashboard
                    exit;
                } else {
                    $error = "Wrong password for staff ID: $id!";
                }
            } else {
                $error = "Staff ID not found: $id";
            }
        } elseif ($role == 'customer') {
            // Check if they are a customer using cid
            $customer_sql = "SELECT cid, cpassword, cname, 'customer' AS role FROM customer WHERE cid = '$id'";
            $customer_result = mysqli_query($conn, $customer_sql);
            if ($customer_result && mysqli_num_rows($customer_result) > 0) {
                $user = mysqli_fetch_assoc($customer_result); // Get their details
                if ($password === $user['cpassword']) { // Check if the password is correct
                    $_SESSION['cid'] = $user['cid']; // Save their ID as cid for customers
                    $_SESSION['role'] = $user['role']; // Save their role as 'customer'
                    header("Location: ../Customer_php/customerDashboard.php"); // Go to customer dashboard
                    exit;
                } else {
                    $error = "Wrong password for customer ID: $id!";
                }
            } else {
                $error = "Customer ID not found: $id";
            }
        } else {
            $error = "Invalid role selected!";
        }
    }
}
?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Login</title>
        <link rel="stylesheet" href="../Staff_css/login.css">
    </head>
    <body>
    <div class="content">
        <h1>Login</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p> <!-- Show error if there is one -->
        <?php endif; ?>
        <?php if ($message): ?>
            <p class="success"><?php echo $message; ?></p> <!-- Show message if there is one -->
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">ID (Staff ID or Customer ID):</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <input type="radio" id="customer" name="role" value="customer" checked> <label for="customer">Customer</label>
            <input type="radio" id="staff" name="role" value="staff"> <label for="staff">Staff</label><br><br>
            <button type="submit">Login</button>
        </form>
    </div>
    </body>
    </html>

<?php
if (isset($conn) && $conn) {
    mysqli_close($conn); // Close the database connection when done
}
?>
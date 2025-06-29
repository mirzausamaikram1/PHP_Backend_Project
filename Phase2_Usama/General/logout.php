<?php
session_start(); // Start the session to access it

// Clear all session data
$_SESSION = array(); // Remove all session variables
session_destroy(); // End the session

// Clear the session cookie (like a tag that remembers you)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), // Name of the session cookie
        '', // Set it to empty
        time() - 42000, // Make it expire in the past
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Tell the browser not to save this page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Send the user to the login page
header("Location: ../General/login.php");
exit; // Stop the script
?>
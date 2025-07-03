<?php
// Start session to access session variables
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session completely
session_destroy();

// If session cookie is used, remove it by setting its expiration in the past
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
  );
}

// Prevent browser from caching the logout page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Redirect the user to the login page after logout
header("Location: ../General/login.php");
exit;
?>

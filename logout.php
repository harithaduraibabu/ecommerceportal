<?php
/**
 * Logout Handler (logout.php)
 * Smart E-Commerce Web Application
 * Safely destroys current session
 */
require_once __DIR__ . '/config/db.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookie if exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start a fresh new session to display confirmation message
session_start();
$_SESSION['success'] = "You have been logged out successfully.";

header("Location: login.php");
exit();
?>


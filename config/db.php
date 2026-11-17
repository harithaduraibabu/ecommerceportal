<?php
/**
 * Database Connection File
 * Smart E-Commerce Web Application
 * Uses MySQLi for database interactions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "ecommerceportal";

// Connect to MySQL
// Checks port 3307 first (configured for XAMPP on this environment), then port 3306 (default)
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, 3307);

if (!$conn) {
    $conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, 3306);
}

if (!$conn) {
    $conn = @mysqli_connect("localhost", $db_user, $db_pass, $db_name);
}

// If connection failed, show error
if (!$conn) {
    die("<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:6px;margin:20px;'>
        <h3>Database Connection Failed</h3>
        <p>Could not connect to MySQL database <strong>$db_name</strong>.</p>
        <p><strong>Error:</strong> " . mysqli_connect_error() . "</p>
    </div>");
}

// Set charset to utf8mb4 for unicode support
mysqli_set_charset($conn, "utf8mb4");

// Helper function to safely sanitize string inputs
function clean_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>


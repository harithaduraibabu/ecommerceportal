<?php
/**
 * Admin Logout Handler (admin/logout.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/../config/db.php';

// Unset admin session variables
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);

$_SESSION['info'] = "Admin session ended successfully.";
header("Location: login.php");
exit();
?>


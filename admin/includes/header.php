<?php
/**
 * Admin Master Header Component
 * Smart E-Commerce Web Application
 */
if (!isset($page_title)) {
    $page_title = "Admin Panel";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Admin Portal</title>

    <!-- Bootstrap 5 Grid CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: #f1f5f9;">

<?php include_once __DIR__ . '/navbar.php'; ?>

<!-- Main Admin Content Container -->
<main class="main-content">
    <div class="site-container">

        <!-- Flash Alert Messages for Admin -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible">
                <span>✓ <?= htmlspecialchars($_SESSION['success']) ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <span>✗ <?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['info'])): ?>
            <div class="alert alert-info alert-dismissible">
                <span>ℹ <?= htmlspecialchars($_SESSION['info']) ?></span>
            </div>
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>


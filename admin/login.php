<?php
/**
 * Admin Login Page (admin/login.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in as admin
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$email = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both admin email and password.";
    } else {
        // Query users table for admin role
        $stmt = mysqli_prepare($conn, "SELECT `id`, `name`, `email`, `password`, `role` FROM `users` WHERE `email` = ? AND `role` = 'admin'");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($res);

        // Verify hashed password
        if ($admin && password_verify($password, $admin['password'])) {
            // Establish Admin Session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_name']      = $admin['name'];
            $_SESSION['admin_email']     = $admin['email'];

            $_SESSION['success'] = "Welcome to the Admin Dashboard, " . $admin['name'] . "!";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials or account does not have admin permissions.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Smart E-Commerce</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">

<div style="max-width: 440px; width: 100%;">
    <div style="text-align: center; margin-bottom: 24px;">
        <h1 style="color: #ffffff; font-size: 26px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px;">
            ⚙️ Smart<span>Admin</span>
        </h1>
        <p style="color: #94a3b8; font-size: 14px;">Administrative Control Panel</p>
    </div>

    <!-- Demo Credentials Box -->
    <div style="background: rgba(37,99,235,0.15); border: 1px solid rgba(147,197,253,0.3); border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; color: #bfdbfe; font-size: 13px;">
        <strong>Demo Admin Credentials:</strong><br>
        Email: <code>admin@ecommerce.com</code><br>
        Password: <code>admin123</code>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <span>✗ <?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="card-box" style="box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email" class="form-label">Admin Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="admin@ecommerce.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="admin123" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 15px;">
                Authenticate &amp; Enter Dashboard
            </button>
        </form>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="../index.php" style="color: #94a3b8; font-size: 13.5px;">&larr; Return to Customer Storefront</a>
    </div>
</div>

</body>
</html>


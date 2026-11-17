<?php
/**
 * Customer Login Page (login.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$email = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Find user by email using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT `id`, `name`, `email`, `phone`, `password`, `role` FROM `users` WHERE `email` = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        // Verify password using password_verify()
        if ($user && password_verify($password, $user['password'])) {
            // Establish PHP session
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['role']       = $user['role'];

            $_SESSION['success'] = "Welcome back, " . $user['name'] . "!";

            // Redirect to cart if items waiting, otherwise index
            if (!empty($_SESSION['cart'])) {
                header("Location: cart.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = "Customer Login";
include_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-header">
        <h2>Customer Login</h2>
        <p>Access your shopping cart, personal wishlist, and orders</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <span>✗ <?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Demo Credentials Helper Callout -->
    <div class="alert alert-info" style="font-size: 13px; display: block; line-height: 1.5;">
        <strong>Quick Demo Login:</strong><br>
        Email: <code>rahul@gmail.com</code> | Password: <code>user123</code>
    </div>

    <div class="card-box">
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="rahul@gmail.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password *</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="user123" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 10px;">
                Sign In to Account
            </button>
        </form>
    </div>

    <div class="auth-footer">
        Don't have an account? <a href="register.php"><strong>Create one here &rarr;</strong></a>
        <div style="margin-top: 12px; font-size: 13px;">
            Are you an administrator? <a href="admin/login.php" style="color: var(--secondary-color);">Admin Login &rarr;</a>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


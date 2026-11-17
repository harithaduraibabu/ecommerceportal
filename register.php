<?php
/**
 * Customer Registration Page (register.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$name = "";
$email = "";
$phone = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = clean_input($_POST['name'] ?? '');
    $email            = clean_input($_POST['email'] ?? '');
    $phone            = clean_input($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Form Validations
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if (empty($phone) || strlen($phone) < 10) {
        $errors[] = "Please provide a valid phone number (at least 10 digits).";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Password and Confirm Password do not match.";
    }

    // Check if email already exists in users table
    if (empty($errors)) {
        $check_stmt = mysqli_prepare($conn, "SELECT `id` FROM `users` WHERE `email` = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $errors[] = "An account with email '$email' is already registered. Please login.";
        }
        mysqli_stmt_close($check_stmt);
    }

    // Insert new user if no validation errors
    if (empty($errors)) {
        // Hash password securely using PHP standard password_hash()
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = "user";

        $insert_stmt = mysqli_prepare($conn, "INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($insert_stmt, "sssss", $name, $email, $phone, $hashed_password, $role);

        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['success'] = "Registration successful! You can now login with your credentials.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed due to a database error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($insert_stmt);
    }
}

$page_title = "Create an Account";
include_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-header">
        <h2>Create an Account</h2>
        <p>Join Smart E-Commerce Portal to shop, save wishlist and track orders</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="display: block;">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card-box">
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="name" class="form-label">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Rahul Sharma" value="<?= htmlspecialchars($name) ?>" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. rahul@gmail.com" value="<?= htmlspecialchars($email) ?>" required>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number *</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g. 9876543210" value="<?= htmlspecialchars($phone) ?>" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password *</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 10px;">
                Register Account
            </button>
        </form>
    </div>

    <div class="auth-footer">
        Already registered? <a href="login.php"><strong>Login to your account &rarr;</strong></a>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


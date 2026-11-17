<?php
/**
 * Common Navigation Bar Component
 * Smart E-Commerce Web Application
 */

// Calculate cart item count
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += (int)$qty;
    }
}

// Calculate wishlist item count for logged-in user
$wishlist_count = 0;
if (!empty($_SESSION['user_id']) && isset($conn)) {
    $user_id_safe = (int)$_SESSION['user_id'];
    $w_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `wishlist` WHERE `user_id` = $user_id_safe");
    if ($w_res && $w_row = mysqli_fetch_assoc($w_res)) {
        $wishlist_count = (int)$w_row['total'];
    }
}

// Detect current page for active menu styling
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="site-header">
    <div class="site-container">
        <nav class="navbar">
            <div class="logo-area">
                <a href="index.php" class="brand-logo">
                    🛍️ Smart<span>Shop</span>
                    <span class="brand-badge">Online Store</span>
                </a>
            </div>

            <ul class="nav-links">
                <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="products.php" class="<?= $current_page == 'products.php' ? 'active' : '' ?>">Products</a></li>
                
                <li>
                    <a href="wishlist.php" class="<?= $current_page == 'wishlist.php' ? 'active' : '' ?>">
                        Wishlist
                        <?php if ($wishlist_count > 0): ?>
                            <span class="badge-count"><?= $wishlist_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li>
                    <a href="cart.php" class="<?= $current_page == 'cart.php' ? 'active' : '' ?>">
                        Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="badge-count"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li><a href="order_history.php" class="<?= $current_page == 'order_history.php' ? 'active' : '' ?>">My Orders</a></li>
                    <li class="user-greeting">
                        Hi, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                    </li>
                    <li><a href="logout.php" class="btn btn-sm btn-outline-danger" style="color:#f87171 !important; border-color:#f87171;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?>">Login</a></li>
                    <li><a href="register.php" class="btn btn-sm btn-primary <?= $current_page == 'register.php' ? 'active' : '' ?>">Register</a></li>
                <?php endif; ?>

                <li style="border-left: 1px solid rgba(255,255,255,0.2); padding-left: 10px;">
                    <a href="admin/login.php" target="_blank" style="color: #93c5fd; font-size: 13px;">Admin Portal &rarr;</a>
                </li>
            </ul>
        </nav>
    </div>
</header>


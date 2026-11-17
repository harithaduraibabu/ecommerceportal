<?php
/**
 * Admin Navigation Bar Component
 * Smart E-Commerce Web Application
 */
$admin_current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="site-header" style="background-color: #0f172a; border-bottom: 3px solid #2563eb;">
    <div class="site-container">
        <nav class="navbar">
            <div class="logo-area">
                <a href="dashboard.php" class="brand-logo">
                    ⚙️ Smart<span>Admin</span>
                    <span class="brand-badge" style="background: rgba(37,99,235,0.25); color: #93c5fd;">Control Panel</span>
                </a>
            </div>

            <ul class="nav-links">
                <li><a href="dashboard.php" class="<?= $admin_current_page == 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a></li>
                <li><a href="add_product.php" class="<?= $admin_current_page == 'add_product.php' ? 'active' : '' ?>">➕ Add Product</a></li>
                <li><a href="manage_products.php" class="<?= in_array($admin_current_page, ['manage_products.php', 'edit_product.php']) ? 'active' : '' ?>">📦 Manage Products</a></li>
                <li><a href="orders.php" class="<?= $admin_current_page == 'orders.php' ? 'active' : '' ?>">📑 Customer Orders</a></li>
                
                <li style="border-left: 1px solid rgba(255,255,255,0.2); padding-left: 12px;">
                    <a href="../index.php" target="_blank" style="color: #94a3b8; font-size: 13px;">🌐 View Storefront &rarr;</a>
                </li>
                
                <li>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger" style="color: #f87171 !important; border-color: #f87171;">
                        Admin Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>


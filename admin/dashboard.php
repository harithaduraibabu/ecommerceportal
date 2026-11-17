<?php
/**
 * Admin Dashboard (admin/dashboard.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/../config/db.php';

// Access Control Protection
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = "Access denied. Please login as administrator.";
    header("Location: login.php");
    exit();
}

// 1. Fetch Total Products Count
$p_count_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `products`");
$total_products = mysqli_fetch_assoc($p_count_res)['total'] ?? 0;

// 2. Fetch Total Orders Count and Total Revenue
$o_stat_res = mysqli_query($conn, "SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount), 0) AS total_revenue FROM `orders`");
$o_stat = mysqli_fetch_assoc($o_stat_res);
$total_orders  = $o_stat['total_orders'] ?? 0;
$total_revenue = $o_stat['total_revenue'] ?? 0.00;

// 3. Fetch Pending Orders Count
$pending_res = mysqli_query($conn, "SELECT COUNT(*) AS pending FROM `orders` WHERE `order_status` = 'Pending'");
$pending_orders = mysqli_fetch_assoc($pending_res)['pending'] ?? 0;

// 4. Fetch Total Registered Customers Count
$u_count_res = mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM `users` WHERE `role` = 'user'");
$total_users = mysqli_fetch_assoc($u_count_res)['total_users'] ?? 0;

// 5. Fetch Recent 5 Orders for Dashboard Summary
$recent_orders_res = mysqli_query($conn, "
    SELECT * FROM `orders` 
    ORDER BY `id` DESC 
    LIMIT 5
");

$page_title = "Admin Dashboard";
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">📊 Executive Dashboard</h2>
        <p class="section-subtitle">Real-time store overview, metrics, and latest sales activity</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="add_product.php" class="btn btn-primary btn-sm">➕ Add Product</a>
        <a href="orders.php" class="btn btn-secondary btn-sm">📑 View Orders</a>
    </div>
</div>

<!-- Dashboard Metric Cards Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">📦</div>
        <div class="stat-info">
            <h4>Total Products</h4>
            <div class="stat-number"><?= $total_products ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;">📑</div>
        <div class="stat-info">
            <h4>Total Orders</h4>
            <div class="stat-number"><?= $total_orders ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #fffbeb; color: #d97706;">⏳</div>
        <div class="stat-info">
            <h4>Pending Orders</h4>
            <div class="stat-number"><?= $pending_orders ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #faf5ff; color: #9333ea;">👥</div>
        <div class="stat-info">
            <h4>Customers</h4>
            <div class="stat-number"><?= $total_users ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #ecfdf5; color: #059669;">💰</div>
        <div class="stat-info">
            <h4>Total Revenue</h4>
            <div class="stat-number">₹<?= number_format($total_revenue, 2) ?></div>
        </div>
    </div>
</div>

<!-- Quick Navigation Banner -->
<div class="card-box" style="margin-bottom: 30px; background: linear-gradient(135deg, #1e293b, #0f172a); color: #ffffff;">
    <h3 style="font-size: 18px; margin-bottom: 8px;">Quick Administrative Tasks</h3>
    <p style="color: #94a3b8; font-size: 14px; margin-bottom: 16px;">Direct shortcuts for daily catalog management and customer order fulfillment.</p>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="add_product.php" class="btn btn-primary">➕ Upload New Product</a>
        <a href="manage_products.php" class="btn btn-secondary">📦 Manage Product Catalog</a>
        <a href="orders.php" class="btn btn-secondary">🚚 Process Customer Orders</a>
        <a href="../index.php" target="_blank" class="btn btn-outline-primary" style="color: #93c5fd !important; border-color: #93c5fd;">🌐 Open Customer View</a>
    </div>
</div>

<!-- Recent Orders Section -->
<div class="section-header">
    <div>
        <h3 class="section-title">⏱️ Recent Customer Orders</h3>
        <p class="section-subtitle">Latest 5 orders placed by shoppers</p>
    </div>
    <a href="orders.php" class="btn btn-outline-primary btn-sm">View All Orders &rarr;</a>
</div>

<div class="card-box" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer Name</th>
                    <th>Contact Phone</th>
                    <th>Total Amount</th>
                    <th>Payment</th>
                    <th>Date Placed</th>
                    <th>Status</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_orders_res && mysqli_num_rows($recent_orders_res) > 0): ?>
                    <?php while ($ord = mysqli_fetch_assoc($recent_orders_res)): ?>
                        <?php
                        $status_class = 'badge-pending';
                        $s_low = strtolower($ord['order_status']);
                        if ($s_low === 'processing') $status_class = 'badge-processing';
                        elseif ($s_low === 'shipped') $status_class = 'badge-shipped';
                        elseif ($s_low === 'delivered') $status_class = 'badge-delivered';
                        elseif ($s_low === 'cancelled') $status_class = 'badge-cancelled';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($ord['order_number']) ?></strong></td>
                            <td><?= htmlspecialchars($ord['customer_name']) ?></td>
                            <td><?= htmlspecialchars($ord['customer_phone']) ?></td>
                            <td><strong style="color: var(--primary-color);">₹<?= number_format($ord['total_amount'], 2) ?></strong></td>
                            <td><?= htmlspecialchars($ord['payment_method']) ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></td>
                            <td><span class="badge-status <?= $status_class ?>"><?= htmlspecialchars($ord['order_status']) ?></span></td>
                            <td style="text-align: center;">
                                <a href="orders.php" class="btn btn-sm btn-secondary">Manage Status</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No orders placed yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


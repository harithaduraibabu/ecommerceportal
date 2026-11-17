<?php
/**
 * Customer Order History Module (order_history.php)
 * Smart E-Commerce Web Application
 * Customers can only inspect their own orders
 */
require_once __DIR__ . '/config/db.php';

// Authentication Check: User must be logged in
if (empty($_SESSION['user_id'])) {
    $_SESSION['info'] = "Please log in to view your order history.";
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch all orders placed by the currently logged-in user
$order_stmt = mysqli_prepare($conn, "
    SELECT * FROM `orders` 
    WHERE `user_id` = ? 
    ORDER BY `id` DESC
");
mysqli_stmt_bind_param($order_stmt, "i", $user_id);
mysqli_stmt_execute($order_stmt);
$orders_result = mysqli_stmt_get_result($order_stmt);

$page_title = "My Order History";
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">📦 My Order History</h2>
        <p class="section-subtitle">Track previous purchases, item summaries, and delivery statuses</p>
    </div>
    <a href="products.php" class="btn btn-primary btn-sm">&larr; Shop More Products</a>
</div>

<?php if ($orders_result && mysqli_num_rows($orders_result) > 0): ?>
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
            <?php
            // Fetch individual items for this specific order
            $order_id = (int)$order['id'];
            $items_stmt = mysqli_prepare($conn, "
                SELECT oi.*, p.image 
                FROM `order_items` oi
                LEFT JOIN `products` p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            mysqli_stmt_bind_param($items_stmt, "i", $order_id);
            mysqli_stmt_execute($items_stmt);
            $items_result = mysqli_stmt_get_result($items_stmt);

            // Determine badge CSS class according to status
            $status_class = 'badge-pending';
            $status_lower = strtolower($order['order_status']);
            if ($status_lower === 'processing') $status_class = 'badge-processing';
            elseif ($status_lower === 'shipped') $status_class = 'badge-shipped';
            elseif ($status_lower === 'delivered') $status_class = 'badge-delivered';
            elseif ($status_lower === 'cancelled') $status_class = 'badge-cancelled';
            ?>
            <div class="card-box" style="padding: 0; overflow: hidden;">
                <!-- Order Top Header Strip -->
                <div style="background-color: #f8fafc; padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <span style="font-size: 13px; color: var(--text-muted);">Order Number:</span>
                        <strong style="font-size: 15px; color: var(--text-main); margin-left: 5px;">
                            <?= htmlspecialchars($order['order_number']) ?>
                        </strong>
                        <span style="margin: 0 10px; color: var(--border-color);">|</span>
                        <span style="font-size: 13px; color: var(--text-muted);">Placed on:</span>
                        <span style="font-size: 13px; font-weight: 500;">
                            <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="badge-status <?= $status_class ?>">
                            ● <?= htmlspecialchars($order['order_status']) ?>
                        </span>
                        <strong style="color: var(--primary-color); font-size: 16px;">
                            Total: ₹<?= number_format($order['total_amount'], 2) ?>
                        </strong>
                    </div>
                </div>

                <!-- Order Content & Delivery Info -->
                <div style="padding: 20px;">
                    <!-- Items Table -->
                    <div class="table-responsive" style="margin-bottom: 15px;">
                        <table class="custom-table" style="box-shadow: none;">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Item</th>
                                    <th>Product Name</th>
                                    <th style="width: 120px;">Unit Price</th>
                                    <th style="width: 100px;">Quantity</th>
                                    <th style="width: 120px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $img_src = !empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])
                                                ? 'uploads/' . htmlspecialchars($item['image'])
                                                : 'assets/css/placeholder.jpg';
                                            ?>
                                            <img src="<?= $img_src ?>" alt="" style="width: 48px; height: 48px; object-fit: contain; border-radius: 4px; background: #f8fafc;">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                        </td>
                                        <td>₹<?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><strong>₹<?= number_format($item['subtotal'], 2) ?></strong></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Delivery Details Snapshot -->
                    <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; padding: 12px 16px; font-size: 13px; color: var(--secondary-color);">
                        <strong>Delivery Address:</strong> <?= htmlspecialchars($order['delivery_address']) ?> 
                        &bull; <strong>Recipient:</strong> <?= htmlspecialchars($order['customer_name']) ?> (<?= htmlspecialchars($order['customer_phone']) ?>)
                        &bull; <strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']) ?>
                    </div>
                </div>
            </div>
            <?php mysqli_stmt_close($items_stmt); ?>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="card-box" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 54px; margin-bottom: 15px;">📦</div>
        <h3 style="font-size: 20px; margin-bottom: 10px;">No Orders Found</h3>
        <p style="color: var(--text-muted); margin-bottom: 25px;">You have not placed any orders yet. Once you complete checkout, your order history will appear here.</p>
        <a href="products.php" class="btn btn-primary btn-lg">Start Shopping Now</a>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


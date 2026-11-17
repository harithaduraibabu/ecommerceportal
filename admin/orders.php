<?php
/**
 * Customer Orders Management (admin/orders.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/../config/db.php';

// Access Control
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = "Access denied. Please login as administrator.";
    header("Location: login.php");
    exit();
}

// -------------------------------------------------------------------------
// Handle Order Status Update
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id   = (int)($_POST['order_id'] ?? 0);
    $new_status = clean_input($_POST['order_status'] ?? '');

    $allowed_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

    if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {
        $stmt = mysqli_prepare($conn, "UPDATE `orders` SET `order_status` = ? WHERE `id` = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "✓ Order #$order_id status changed to '$new_status'.";
        } else {
            $_SESSION['error'] = "Failed to update order status.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Invalid order or status selected.";
    }

    header("Location: orders.php");
    exit();
}

// -------------------------------------------------------------------------
// Fetch All Customer Orders from MySQL
// -------------------------------------------------------------------------
$filter_status = isset($_GET['status']) ? clean_input($_GET['status']) : '';
$sql = "SELECT * FROM `orders`";

if (!empty($filter_status)) {
    $status_safe = mysqli_real_escape_string($conn, $filter_status);
    $sql .= " WHERE `order_status` = '$status_safe'";
}

$sql .= " ORDER BY `id` DESC";
$orders_result = mysqli_query($conn, $sql);

$page_title = "Manage Customer Orders";
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">📑 Customer Orders Management</h2>
        <p class="section-subtitle">Monitor customer purchases and update fulfillment statuses</p>
    </div>
    
    <!-- Filter status pills -->
    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
        <a href="orders.php" class="btn btn-sm <?= empty($filter_status) ? 'btn-primary' : 'btn-secondary' ?>">All Orders</a>
        <a href="orders.php?status=Pending" class="btn btn-sm <?= $filter_status == 'Pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
        <a href="orders.php?status=Processing" class="btn btn-sm <?= $filter_status == 'Processing' ? 'btn-primary' : 'btn-secondary' ?>">Processing</a>
        <a href="orders.php?status=Shipped" class="btn btn-sm <?= $filter_status == 'Shipped' ? 'btn-primary' : 'btn-secondary' ?>">Shipped</a>
        <a href="orders.php?status=Delivered" class="btn btn-sm <?= $filter_status == 'Delivered' ? 'btn-primary' : 'btn-secondary' ?>">Delivered</a>
    </div>
</div>

<?php if ($orders_result && mysqli_num_rows($orders_result) > 0): ?>
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
            <?php
            $order_id = (int)$order['id'];
            
            // Query ordered items
            $item_query = "SELECT * FROM `order_items` WHERE `order_id` = $order_id";
            $items_res = mysqli_query($conn, $item_query);

            // Determine status badge style
            $status_class = 'badge-pending';
            $s_low = strtolower($order['order_status']);
            if ($s_low === 'processing') $status_class = 'badge-processing';
            elseif ($s_low === 'shipped') $status_class = 'badge-shipped';
            elseif ($s_low === 'delivered') $status_class = 'badge-delivered';
            elseif ($s_low === 'cancelled') $status_class = 'badge-cancelled';
            ?>
            <div class="card-box" style="padding: 0; overflow: hidden;">
                <!-- Header Strip -->
                <div style="background-color: #f8fafc; padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <span style="font-size: 13px; color: var(--text-muted);">Order ID:</span>
                        <strong style="font-size: 16px; color: var(--text-main); margin-left: 6px;">
                            <?= htmlspecialchars($order['order_number']) ?>
                        </strong>
                        <span style="margin: 0 10px; color: var(--border-color);">|</span>
                        <span style="font-size: 13px; color: var(--text-muted);">Placed:</span>
                        <span style="font-size: 13px; font-weight: 500;">
                            <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                        </span>
                    </div>

                    <!-- Update Status Inline Form -->
                    <form action="orders.php" method="POST" style="display: flex; align-items: center; gap: 8px;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <span style="font-size: 13px; font-weight: 600;">Status:</span>
                        
                        <select name="order_status" class="form-control" style="width: auto; padding: 5px 10px; font-size: 13px;">
                            <?php foreach (['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($order['order_status'] === $opt) ? 'selected' : '' ?>>
                                    <?= $opt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">
                            Save
                        </button>
                    </form>
                </div>

                <!-- Customer Details & Order Items -->
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px; background: #f8fafc; padding: 14px 18px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <div>
                            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Customer Information</div>
                            <div style="font-size: 14px; font-weight: 600; margin-top: 4px;"><?= htmlspecialchars($order['customer_name']) ?></div>
                            <div style="font-size: 13px; color: var(--text-muted);">Phone: <?= htmlspecialchars($order['customer_phone']) ?></div>
                            <div style="font-size: 13px; color: var(--text-muted);">Payment: <strong><?= htmlspecialchars($order['payment_method']) ?></strong></div>
                        </div>

                        <div>
                            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Shipping Address</div>
                            <div style="font-size: 13.5px; margin-top: 4px; line-height: 1.4; color: var(--text-main);">
                                <?= nl2br(htmlspecialchars($order['delivery_address'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Items Sub-Table -->
                    <div class="table-responsive">
                        <table class="custom-table" style="box-shadow: none;">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th style="width: 140px;">Unit Price</th>
                                    <th style="width: 120px;">Quantity</th>
                                    <th style="width: 140px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($items_res): ?>
                                    <?php while ($it = mysqli_fetch_assoc($items_res)): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($it['product_name']) ?></strong></td>
                                            <td>₹<?= number_format($it['price'], 2) ?></td>
                                            <td><?= $it['quantity'] ?></td>
                                            <td><strong>₹<?= number_format($it['subtotal'], 2) ?></strong></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" style="text-align: right; background: #fff; font-size: 14px;">Total Order Amount:</th>
                                    <th style="background: #fff; font-size: 16px; color: var(--primary-color);">
                                        ₹<?= number_format($order['total_amount'], 2) ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="card-box" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 54px; margin-bottom: 15px;">📑</div>
        <h3 style="font-size: 20px; margin-bottom: 10px;">No Orders Matching Selected Filter</h3>
        <p style="color: var(--text-muted); margin-bottom: 25px;">Orders submitted by customers will be visible here.</p>
        <a href="orders.php" class="btn btn-primary">View All Orders</a>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


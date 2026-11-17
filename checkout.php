<?php
/**
 * Checkout and Order Processing Module (checkout.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/config/db.php';

// Authentication Check: User must be logged in to checkout
if (empty($_SESSION['user_id'])) {
    $_SESSION['info'] = "Please log in or register an account to complete your checkout.";
    header("Location: login.php");
    exit();
}

// Cart Check: Cart cannot be empty
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty. Please add items before proceeding to checkout.";
    header("Location: products.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch current user details from database
$user_stmt = mysqli_prepare($conn, "SELECT `name`, `email`, `phone` FROM `users` WHERE `id` = ?");
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_data = mysqli_fetch_assoc(mysqli_stmt_get_result($user_stmt));

$customer_name  = $user_data['name'] ?? '';
$customer_phone = $user_data['phone'] ?? '';
$delivery_addr  = '';
$errors = [];

// Fetch cart products from database and calculate total
$cart_items = [];
$total_amount = 0.00;

$ids = array_map('intval', array_keys($_SESSION['cart']));
if (!empty($ids)) {
    $id_list = implode(',', $ids);
    $res = mysqli_query($conn, "SELECT * FROM `products` WHERE `id` IN ($id_list)");
    
    while ($row = mysqli_fetch_assoc($res)) {
        $pid = (int)$row['id'];
        $qty = (int)$_SESSION['cart'][$pid];
        $subtotal = $row['price'] * $qty;
        $total_amount += $subtotal;

        $cart_items[] = [
            'id'       => $pid,
            'name'     => $row['name'],
            'price'    => $row['price'],
            'stock'    => $row['stock'],
            'quantity' => $qty,
            'subtotal' => $subtotal
        ];
    }
}

// Process Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $customer_name  = clean_input($_POST['customer_name'] ?? '');
    $customer_phone = clean_input($_POST['customer_phone'] ?? '');
    $delivery_addr  = clean_input($_POST['delivery_address'] ?? '');
    $payment_method = clean_input($_POST['payment_method'] ?? 'Cash on Delivery');

    // Validation
    if (empty($customer_name)) {
        $errors[] = "Customer Name is required.";
    }
    if (empty($customer_phone) || strlen($customer_phone) < 10) {
        $errors[] = "A valid 10-digit phone number is required.";
    }
    if (empty($delivery_addr) || strlen($delivery_addr) < 15) {
        $errors[] = "Please provide a complete delivery address (street, city, pin code).";
    }

    if (empty($errors)) {
        // Generate a unique order identifier (e.g. ORD-20261117-A1B2)
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $initial_status = 'Pending';

        // Begin MySQL transaction for atomic order saving
        mysqli_begin_transaction($conn);

        try {
            // 1. Insert into orders table
            $order_stmt = mysqli_prepare($conn, "
                INSERT INTO `orders` 
                (`order_number`, `user_id`, `customer_name`, `customer_phone`, `delivery_address`, `total_amount`, `payment_method`, `order_status`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param(
                $order_stmt, 
                "sisssdss", 
                $order_number, 
                $user_id, 
                $customer_name, 
                $customer_phone, 
                $delivery_addr, 
                $total_amount, 
                $payment_method, 
                $initial_status
            );
            mysqli_stmt_execute($order_stmt);
            $order_id = mysqli_insert_id($conn);
            mysqli_stmt_close($order_stmt);

            // 2. Insert each item into order_items table and deduct stock
            $item_stmt = mysqli_prepare($conn, "
                INSERT INTO `order_items` 
                (`order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stock_stmt = mysqli_prepare($conn, "
                UPDATE `products` SET `stock` = GREATEST(0, `stock` - ?) WHERE `id` = ?
            ");

            foreach ($cart_items as $item) {
                mysqli_stmt_bind_param(
                    $item_stmt, 
                    "iisdid", 
                    $order_id, 
                    $item['id'], 
                    $item['name'], 
                    $item['price'], 
                    $item['quantity'], 
                    $item['subtotal']
                );
                mysqli_stmt_execute($item_stmt);

                // Update product stock in inventory
                mysqli_stmt_bind_param($stock_stmt, "ii", $item['quantity'], $item['id']);
                mysqli_stmt_execute($stock_stmt);
            }

            mysqli_stmt_close($item_stmt);
            mysqli_stmt_close($stock_stmt);

            // Commit transaction
            mysqli_commit($conn);

            // 3. Clear shopping cart session after successful order
            $_SESSION['cart'] = [];

            // 4. Set success message and redirect to order history
            $_SESSION['success'] = "🎉 Order placed successfully! Your Order ID is $order_number.";
            header("Location: order_history.php");
            exit();

        } catch (Exception $e) {
            // Rollback on any failure
            mysqli_rollback($conn);
            $errors[] = "Failed to process order: " . $e->getMessage();
        }
    }
}

$page_title = "Checkout";
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">💳 Secure Checkout</h2>
        <p class="section-subtitle">Confirm your shipping details and place your order</p>
    </div>
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

<form action="checkout.php" method="POST">
    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 30px; align-items: start;">
        
        <!-- Left Column: Shipping & Delivery Address Form -->
        <div class="card-box">
            <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                📍 Shipping &amp; Delivery Information
            </h3>

            <div class="form-group">
                <label for="customer_name" class="form-label">Full Name *</label>
                <input type="text" id="customer_name" name="customer_name" class="form-control" value="<?= htmlspecialchars($customer_name) ?>" required>
            </div>

            <div class="form-group">
                <label for="customer_phone" class="form-label">Contact Phone Number *</label>
                <input type="text" id="customer_phone" name="customer_phone" class="form-control" value="<?= htmlspecialchars($customer_phone) ?>" required>
            </div>

            <div class="form-group">
                <label for="delivery_address" class="form-label">Complete Delivery Address (House No, Street, Landmark, City, PIN) *</label>
                <textarea id="delivery_address" name="delivery_address" class="form-control" rows="4" placeholder="Enter complete residential/delivery address..." required><?= htmlspecialchars($delivery_addr) ?></textarea>
            </div>

            <h3 style="font-size: 18px; margin: 24px 0 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                💵 Payment Method
            </h3>

            <div style="background: #f8fafc; border: 1px solid var(--border-color); padding: 14px 18px; border-radius: 6px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 600;">
                    <input type="radio" name="payment_method" value="Cash on Delivery" checked>
                    <span>💵 Cash on Delivery (COD)</span>
                </label>
                <p style="font-size: 12.5px; color: var(--text-muted); margin: 6px 0 0 24px;">
                    Pay in cash when our delivery executive arrives at your doorstep. No online payment required.
                </p>
            </div>
        </div>

        <!-- Right Column: Order Summary & Place Order -->
        <div class="card-box">
            <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                📋 Order Summary (<?= count($cart_items) ?> Items)
            </h3>

            <div style="max-height: 280px; overflow-y: auto; margin-bottom: 16px;">
                <?php foreach ($cart_items as $item): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--border-color); font-size: 14px;">
                        <div>
                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                            <div style="font-size: 12px; color: var(--text-muted);">
                                Qty: <?= $item['quantity'] ?> &times; ₹<?= number_format($item['price'], 2) ?>
                            </div>
                        </div>
                        <strong style="color: var(--text-main);">
                            ₹<?= number_format($item['subtotal'], 2) ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                <span>Subtotal:</span>
                <strong>₹<?= number_format($total_amount, 2) ?></strong>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                <span>Shipping / Delivery:</span>
                <strong style="color: var(--accent-green);">FREE</strong>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 12px 0;">

            <div style="display: flex; justify-content: space-between; margin-bottom: 24px; font-size: 18px;">
                <strong>Total Payable:</strong>
                <strong style="color: var(--primary-color); font-size: 22px;">
                    ₹<?= number_format($total_amount, 2) ?>
                </strong>
            </div>

            <button type="submit" name="place_order" class="btn btn-success btn-block btn-lg" style="text-align: center;">
                ✓ Confirm &amp; Place Order
            </button>

            <div style="text-align: center; margin-top: 12px;">
                <a href="cart.php" style="font-size: 13px; color: var(--text-muted);">&larr; Edit Shopping Cart</a>
            </div>
        </div>

    </div>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


<?php
/**
 * Shopping Cart Module (cart.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/config/db.php';

// Initialize session cart if not exists
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// -------------------------------------------------------------------------
// Action 1: Add Product to Cart
// -------------------------------------------------------------------------
if ($action === 'add') {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $quantity   = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    if ($quantity < 1) $quantity = 1;

    if ($product_id > 0) {
        // Verify product in database
        $stmt = mysqli_prepare($conn, "SELECT `id`, `name`, `stock` FROM `products` WHERE `id` = ?");
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($res);

        if ($product) {
            if ($product['stock'] <= 0) {
                $_SESSION['error'] = "Sorry, " . $product['name'] . " is currently out of stock.";
            } else {
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                $_SESSION['success'] = "Added " . $product['name'] . " to your shopping cart!";
            }
        } else {
            $_SESSION['error'] = "Product not found.";
        }
    }
    header("Location: cart.php");
    exit();
}

// -------------------------------------------------------------------------
// Action 2: Update Cart Quantities
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (!empty($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$pid]);
            } else {
                $_SESSION['cart'][$pid] = $qty;
            }
        }
        $_SESSION['success'] = "Shopping cart quantities updated successfully.";
    }
    header("Location: cart.php");
    exit();
}

// -------------------------------------------------------------------------
// Action 3: Remove Product from Cart
// -------------------------------------------------------------------------
if ($action === 'remove') {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['info'] = "Item removed from your cart.";
    }
    header("Location: cart.php");
    exit();
}

// -------------------------------------------------------------------------
// Action 4: Clear Entire Cart
// -------------------------------------------------------------------------
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    $_SESSION['info'] = "Your shopping cart has been cleared.";
    header("Location: cart.php");
    exit();
}

// -------------------------------------------------------------------------
// Fetch Cart Products Data from MySQL
// -------------------------------------------------------------------------
$cart_items = [];
$total_amount = 0.00;

if (!empty($_SESSION['cart'])) {
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
                'image'    => $row['image'],
                'price'    => $row['price'],
                'stock'    => $row['stock'],
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];
        }
    }
}

$page_title = "Your Shopping Cart";
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">🛒 Shopping Cart</h2>
        <p class="section-subtitle">Review items and manage quantities before checkout</p>
    </div>
</div>

<?php if (!empty($cart_items)): ?>
    <form action="cart.php" method="POST">
        <div class="card-box" style="padding: 0; overflow: hidden;">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Product</th>
                            <th>Description</th>
                            <th style="width: 130px;">Unit Price</th>
                            <th style="width: 130px;">Quantity</th>
                            <th style="width: 130px;">Subtotal</th>
                            <th style="width: 100px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $img_src = !empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])
                                        ? 'uploads/' . htmlspecialchars($item['image'])
                                        : 'assets/css/placeholder.jpg';
                                    ?>
                                    <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 60px; height: 60px; object-fit: contain; border-radius: 4px; background: #f8fafc;">
                                </td>
                                <td>
                                    <strong>
                                        <a href="product_details.php?id=<?= $item['id'] ?>">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                    </strong>
                                    <div style="font-size: 12px; color: var(--text-muted);">
                                        Max available stock: <?= $item['stock'] ?>
                                    </div>
                                </td>
                                <td>
                                    ₹<?= number_format($item['price'], 2) ?>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="quantities[<?= $item['id'] ?>]" 
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" 
                                           max="<?= $item['stock'] ?>" 
                                           class="qty-input">
                                </td>
                                <td>
                                    <strong style="color: var(--primary-color);">
                                        ₹<?= number_format($item['subtotal'], 2) ?>
                                    </strong>
                                </td>
                                <td style="text-align: center;">
                                    <!-- =========================================================================
                                         JAVASCRIPT CONFIRMATION POPUP REQUIREMENT:
                                         Calls confirmRemoveCartItem() before removing product from cart
                                         ========================================================================= -->
                                    <a href="cart.php?action=remove&id=<?= $item['id'] ?>" 
                                       onclick="return confirmRemoveCartItem('<?= htmlspecialchars(addslashes($item['name'])) ?>');"
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Remove Item">
                                        ✕ Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cart Actions & Grand Total -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px; margin-top: 10px;">
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="update_cart" class="btn btn-secondary">
                    🔄 Update Quantities
                </button>
                <a href="cart.php?action=clear" 
                   onclick="return confirm('Are you sure you want to clear your entire cart?');" 
                   class="btn btn-outline-danger">
                    Clear Cart
                </a>
                <a href="products.php" class="btn btn-secondary">
                    &larr; Continue Shopping
                </a>
            </div>

            <!-- Total Summary Card -->
            <div class="card-box" style="min-width: 300px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px;">
                    <span>Total Items:</span>
                    <strong><?= $cart_count ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px;">
                    <span>Delivery Charge:</span>
                    <strong style="color: var(--accent-green);">FREE (COD)</strong>
                </div>
                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 12px 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 18px;">
                    <strong>Grand Total:</strong>
                    <strong style="color: var(--primary-color); font-size: 22px;">₹<?= number_format($total_amount, 2) ?></strong>
                </div>
                <a href="checkout.php" class="btn btn-primary btn-block btn-lg" style="text-align: center;">
                    Proceed to Checkout &rarr;
                </a>
            </div>
        </div>
    </form>
<?php else: ?>
    <div class="card-box" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 54px; margin-bottom: 15px;">🛒</div>
        <h3 style="font-size: 20px; margin-bottom: 10px;">Your Shopping Cart is Empty</h3>
        <p style="color: var(--text-muted); margin-bottom: 25px;">Looks like you haven't added any products to your cart yet.</p>
        <a href="products.php" class="btn btn-primary btn-lg">Explore Products Now</a>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


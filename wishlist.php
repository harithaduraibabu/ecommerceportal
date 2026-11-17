<?php
/**
 * Wishlist Module (wishlist.php)
 * Smart E-Commerce Web Application
 * Only authenticated users can manage their personal wishlist
 */
require_once __DIR__ . '/config/db.php';

// Authentication Check: Wishlist requires user to be logged in
if (empty($_SESSION['user_id'])) {
    $_SESSION['info'] = "Please log in to manage your personal wishlist.";
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$action  = isset($_GET['action']) ? $_GET['action'] : '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// -------------------------------------------------------------------------
// Action 1: Add Product to Wishlist
// -------------------------------------------------------------------------
if ($action === 'add' && $product_id > 0) {
    // Check if product exists in database
    $p_check = mysqli_query($conn, "SELECT `name` FROM `products` WHERE `id` = $product_id");
    if ($p_check && mysqli_num_rows($p_check) > 0) {
        $p_row = mysqli_fetch_assoc($p_check);
        
        // Insert into wishlist (IGNORE avoids duplicate entry error due to UNIQUE key)
        $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO `wishlist` (`user_id`, `product_id`) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['success'] = "Added " . $p_row['name'] . " to your wishlist!";
        } else {
            $_SESSION['info'] = $p_row['name'] . " is already in your wishlist.";
        }
    }
    header("Location: wishlist.php");
    exit();
}

// -------------------------------------------------------------------------
// Action 2: Remove Product from Wishlist
// -------------------------------------------------------------------------
if ($action === 'remove' && $product_id > 0) {
    $stmt = mysqli_prepare($conn, "DELETE FROM `wishlist` WHERE `user_id` = ? AND `product_id` = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);

    $_SESSION['info'] = "Item removed from your wishlist.";
    header("Location: wishlist.php");
    exit();
}

// -------------------------------------------------------------------------
// Action 3: Move Wishlist Item to Shopping Cart
// -------------------------------------------------------------------------
if ($action === 'move_to_cart' && $product_id > 0) {
    // Verify product stock
    $p_stmt = mysqli_prepare($conn, "SELECT `name`, `stock` FROM `products` WHERE `id` = ?");
    mysqli_stmt_bind_param($p_stmt, "i", $product_id);
    mysqli_stmt_execute($p_stmt);
    $res = mysqli_stmt_get_result($p_stmt);
    $prod = mysqli_fetch_assoc($res);

    if ($prod && $prod['stock'] > 0) {
        // Add to cart session
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += 1;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }

        // Delete from wishlist table
        $del = mysqli_prepare($conn, "DELETE FROM `wishlist` WHERE `user_id` = ? AND `product_id` = ?");
        mysqli_stmt_bind_param($del, "ii", $user_id, $product_id);
        mysqli_stmt_execute($del);

        $_SESSION['success'] = "Moved " . $prod['name'] . " to your shopping cart!";
    } else {
        $_SESSION['error'] = "Sorry, that product is currently out of stock.";
    }

    header("Location: cart.php");
    exit();
}

// -------------------------------------------------------------------------
// Query User's Wishlist Items from MySQL
// -------------------------------------------------------------------------
$stmt = mysqli_prepare($conn, "
    SELECT p.*, w.created_at AS added_on 
    FROM `wishlist` w 
    JOIN `products` p ON w.product_id = p.id 
    WHERE w.user_id = ? 
    ORDER BY w.id DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$wishlist_result = mysqli_stmt_get_result($stmt);

$page_title = "My Wishlist";
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">❤️ My Personal Wishlist</h2>
        <p class="section-subtitle">Saved items for later purchase</p>
    </div>
    <a href="products.php" class="btn btn-outline-primary btn-sm">&larr; Continue Shopping</a>
</div>

<?php if ($wishlist_result && mysqli_num_rows($wishlist_result) > 0): ?>
    <div class="card-box" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">Product</th>
                        <th>Details</th>
                        <th style="width: 140px;">Price</th>
                        <th style="width: 140px;">Stock Status</th>
                        <th style="width: 220px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($wishlist_result)): ?>
                        <tr>
                            <td>
                                <?php 
                                $img_src = !empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])
                                    ? 'uploads/' . htmlspecialchars($item['image'])
                                    : 'assets/css/placeholder.jpg';
                                ?>
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 65px; height: 65px; object-fit: contain; border-radius: 4px; background: #f8fafc;">
                            </td>
                            <td>
                                <strong>
                                    <a href="product_details.php?id=<?= $item['id'] ?>">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                </strong>
                                <div style="font-size: 12.5px; color: var(--text-muted);">
                                    Category: <?= htmlspecialchars($item['category']) ?> &bull; Added: <?= date('M d, Y', strtotime($item['added_on'])) ?>
                                </div>
                            </td>
                            <td>
                                <strong style="color: var(--primary-color); font-size: 16px;">
                                    ₹<?= number_format($item['price'], 2) ?>
                                </strong>
                            </td>
                            <td>
                                <span class="stock-status <?= $item['stock'] > 0 ? '' : 'out' ?>">
                                    <?= $item['stock'] > 0 ? 'In Stock (' . $item['stock'] . ')' : 'Out of Stock' ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <?php if ($item['stock'] > 0): ?>
                                        <a href="wishlist.php?action=move_to_cart&id=<?= $item['id'] ?>" class="btn btn-sm btn-primary">
                                            🛒 Move to Cart
                                        </a>
                                    <?php endif; ?>

                                    <!-- =========================================================================
                                         JAVASCRIPT CONFIRMATION POPUP REQUIREMENT:
                                         Calls confirmRemoveWishlistItem() before removing product from wishlist
                                         ========================================================================= -->
                                    <a href="wishlist.php?action=remove&id=<?= $item['id'] ?>" 
                                       onclick="return confirmRemoveWishlistItem('<?= htmlspecialchars(addslashes($item['name'])) ?>');"
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Remove Item">
                                        ✕ Remove
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card-box" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 54px; margin-bottom: 15px;">❤️</div>
        <h3 style="font-size: 20px; margin-bottom: 10px;">Your Wishlist is Empty</h3>
        <p style="color: var(--text-muted); margin-bottom: 25px;">You haven't saved any products to your wishlist yet.</p>
        <a href="products.php" class="btn btn-primary btn-lg">Discover Products</a>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


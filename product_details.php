<?php
/**
 * Product Details Page (product_details.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/config/db.php';

// Validate product ID parameter
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    $_SESSION['error'] = "Invalid product requested.";
    header("Location: products.php");
    exit();
}

// Fetch product details using prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM `products` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);

if (!$product) {
    $_SESSION['error'] = "Product not found.";
    header("Location: products.php");
    exit();
}

$page_title = $product['name'];
include_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="products.php" style="font-size: 14px; font-weight: 600;">&larr; Back to Products</a>
</div>

<div class="product-detail-layout">
    <div class="product-detail-image-box">
        <?php 
        $img_src = !empty($product['image']) && file_exists(__DIR__ . '/uploads/' . $product['image'])
            ? 'uploads/' . htmlspecialchars($product['image'])
            : 'assets/css/placeholder.jpg';
        ?>
        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid">
    </div>

    <div class="product-detail-info">
        <span class="category-badge"><?= htmlspecialchars($product['category']) ?></span>
        <h1><?= htmlspecialchars($product['name']) ?></h1>

        <div class="product-detail-price">
            ₹<?= number_format($product['price'], 2) ?>
        </div>

        <div style="margin-bottom: 20px;">
            <strong>Availability: </strong>
            <?php if ($product['stock'] > 0): ?>
                <span style="color: var(--accent-green); font-weight: 600;">In Stock (<?= $product['stock'] ?> units available)</span>
            <?php else: ?>
                <span style="color: var(--accent-red); font-weight: 600;">Out of Stock</span>
            <?php endif; ?>
        </div>

        <div class="product-detail-desc">
            <h4 style="font-size: 15px; margin-bottom: 8px; color: var(--text-main);">Product Description:</h4>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>

        <?php if ($product['stock'] > 0): ?>
            <form action="cart.php" method="GET" class="product-detail-actions">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">

                <label for="qty" style="font-size: 14px; font-weight: 600;">Quantity:</label>
                <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">

                <button type="submit" class="btn btn-primary btn-lg">
                    🛒 Add to Cart
                </button>

                <a href="wishlist.php?action=add&id=<?= $product['id'] ?>" class="btn btn-secondary btn-lg">
                    ❤️ Add to Wishlist
                </a>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">
                This item is currently out of stock. You can still add it to your wishlist to purchase later.
            </div>
            <a href="wishlist.php?action=add&id=<?= $product['id'] ?>" class="btn btn-secondary">
                ❤️ Add to Wishlist
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


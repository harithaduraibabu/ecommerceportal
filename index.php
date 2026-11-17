<?php
/**
 * Homepage (index.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/config/db.php';

$page_title = "Online Shopping - Home";

// Fetch featured products from database (limit 8)
$query = "SELECT * FROM `products` ORDER BY `id` DESC LIMIT 8";
$result = mysqli_query($conn, $query);

include_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome / Hero Banner -->
<section class="hero-banner">
    <div class="hero-text">
        <span class="tagline">⚡ Premium Electronics &amp; Accessories</span>
        <h1>Smart E-Commerce Portal</h1>
        <p>
            Explore our curated catalog of smart electronics, audio gear, and tech accessories.
            Experience seamless shopping with integrated cart, personal wishlist, and cash-on-delivery checkout.
        </p>
        <div class="hero-actions">
            <a href="products.php" class="btn btn-primary btn-lg">Browse All Products &rarr;</a>
            <a href="#featured" class="btn btn-secondary btn-lg">View Featured Deals</a>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured">
    <div class="section-header">
        <div>
            <h2 class="section-title">🔥 Featured Products</h2>
            <p class="section-subtitle">Handpicked gadgets and electronics from our MySQL inventory</p>
        </div>
        <a href="products.php" class="btn btn-outline-primary btn-sm">View All &rarr;</a>
    </div>

    <!-- =========================================================================
         BOOTSTRAP GRID SYSTEM REQUIREMENT:
         Here we use Bootstrap's responsive grid classes:
         - .row with .g-4 (gutter spacing)
         - .col-12 (1 card per row on mobile phones)
         - .col-sm-6 (2 cards per row on small tablets)
         - .col-md-4 (3 cards per row on medium screens/laptops)
         - .col-lg-3 (4 cards per row on large desktop monitors)
         ========================================================================= -->
    <div class="row g-4">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="product-card-img-wrap">
                            <span class="category-tag"><?= htmlspecialchars($product['category']) ?></span>
                            <?php 
                            $img_src = !empty($product['image']) && file_exists(__DIR__ . '/uploads/' . $product['image'])
                                ? 'uploads/' . htmlspecialchars($product['image'])
                                : 'assets/css/placeholder.jpg';
                            ?>
                            <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-card-img">
                        </div>

                        <div class="product-card-body">
                            <h3 class="product-card-title">
                                <a href="product_details.php?id=<?= $product['id'] ?>">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h3>

                            <p class="product-card-desc">
                                <?= htmlspecialchars(substr($product['description'], 0, 75)) ?>...
                            </p>

                            <div class="product-card-price-row">
                                <span class="product-price">₹<?= number_format($product['price'], 2) ?></span>
                                <span class="stock-status <?= $product['stock'] > 0 ? '' : 'out' ?>">
                                    <?= $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ')' : 'Out of Stock' ?>
                                </span>
                            </div>

                            <div class="product-card-actions">
                                <a href="cart.php?action=add&id=<?= $product['id'] ?>" class="btn btn-primary">
                                    🛒 Add to Cart
                                </a>
                                <a href="wishlist.php?action=add&id=<?= $product['id'] ?>" class="btn btn-secondary btn-wishlist" title="Add to Wishlist">
                                    ❤️
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card-box" style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-muted); font-size: 16px;">No products available right now.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <!-- End Bootstrap Grid -->
</section>

<!-- Project Features Highlights -->
<section style="margin-top: 50px;">
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card-box" style="text-align: center; height: 100%;">
                <div style="font-size: 32px; margin-bottom: 10px;">🚚</div>
                <h4 style="font-size: 16px; margin-bottom: 6px;">Cash on Delivery</h4>
                <p style="font-size: 13px; color: var(--text-muted);">Pay securely at your doorstep upon order arrival without payment gateway complexity.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card-box" style="text-align: center; height: 100%;">
                <div style="font-size: 32px; margin-bottom: 10px;">🔒</div>
                <h4 style="font-size: 16px; margin-bottom: 6px;">Secure Authentication</h4>
                <p style="font-size: 13px; color: var(--text-muted);">Passwords securely hashed using PHP <code>password_hash()</code> with session protection.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card-box" style="text-align: center; height: 100%;">
                <div style="font-size: 32px; margin-bottom: 10px;">📦</div>
                <h4 style="font-size: 16px; margin-bottom: 6px;">Order History Tracking</h4>
                <p style="font-size: 13px; color: var(--text-muted);">Track placed orders and view individual order items stored securely in MySQL database.</p>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


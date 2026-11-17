<?php
/**
 * All Products Catalog (products.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/config/db.php';

$page_title = "Product Catalog";

// Optional category filter and search query
$category_filter = isset($_GET['category']) ? clean_input($_GET['category']) : '';
$search_query    = isset($_GET['search']) ? clean_input($_GET['search']) : '';

// Build dynamic SQL query using safe mysqli escaping
$sql = "SELECT * FROM `products` WHERE 1=1";

if (!empty($category_filter)) {
    $category_escaped = mysqli_real_escape_string($conn, $category_filter);
    $sql .= " AND `category` = '$category_escaped'";
}

if (!empty($search_query)) {
    $search_escaped = mysqli_real_escape_string($conn, $search_query);
    $sql .= " AND (`name` LIKE '%$search_escaped%' OR `description` LIKE '%$search_escaped%')";
}

$sql .= " ORDER BY `id` DESC";
$result = mysqli_query($conn, $sql);

// Fetch distinct categories for filter buttons
$cat_res = mysqli_query($conn, "SELECT DISTINCT `category` FROM `products` ORDER BY `category` ASC");

include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">📦 All Products Catalog</h2>
        <p class="section-subtitle">Showing available inventory in MySQL database</p>
    </div>
</div>

<!-- Search & Category Filter Bar -->
<div class="card-box" style="padding: 16px 20px; margin-bottom: 25px;">
    <form action="products.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 8px; flex: 1; min-width: 260px;">
            <input type="text" name="search" class="form-control" placeholder="Search by product name or keyword..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (!empty($search_query) || !empty($category_filter)): ?>
                <a href="products.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <a href="products.php" class="btn btn-sm <?= empty($category_filter) ? 'btn-primary' : 'btn-secondary' ?>">All Categories</a>
            <?php if ($cat_res): ?>
                <?php while ($cat = mysqli_fetch_assoc($cat_res)): ?>
                    <a href="products.php?category=<?= urlencode($cat['category']) ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>" 
                       class="btn btn-sm <?= ($category_filter === $cat['category']) ? 'btn-primary' : 'btn-secondary' ?>">
                        <?= htmlspecialchars($cat['category']) ?>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- =========================================================================
     BOOTSTRAP GRID SYSTEM:
     Using standard Bootstrap 12-column grid system for responsive product catalog
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
                                <?= $product['stock'] > 0 ? 'Stock: ' . $product['stock'] : 'Out of Stock' ?>
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
            <div class="card-box" style="text-align: center; padding: 50px 20px;">
                <p style="font-size: 18px; color: var(--text-muted); margin-bottom: 12px;">No products found matching your criteria.</p>
                <a href="products.php" class="btn btn-primary">View All Products</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<!-- End Bootstrap Grid -->

<?php include_once __DIR__ . '/includes/footer.php'; ?>


<?php
/**
 * Manage Products Catalog (admin/manage_products.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/../config/db.php';

// Access Control
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = "Access denied. Please login as administrator.";
    header("Location: login.php");
    exit();
}

// Fetch all products ordered by latest ID
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$sql = "SELECT * FROM `products`";

if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE `name` LIKE '%$search_safe%' OR `category` LIKE '%$search_safe%'";
}

$sql .= " ORDER BY `id` DESC";
$result = mysqli_query($conn, $sql);

$page_title = "Manage Products";
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">📦 Product Inventory Management</h2>
        <p class="section-subtitle">View, edit details, or remove products from the active catalog</p>
    </div>
    <a href="add_product.php" class="btn btn-primary btn-sm">➕ Add New Product</a>
</div>

<!-- Search Bar -->
<div class="card-box" style="padding: 14px 20px; margin-bottom: 20px;">
    <form action="manage_products.php" method="GET" style="display: flex; gap: 10px; max-width: 500px;">
        <input type="text" name="search" class="form-control" placeholder="Search by product name or category..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if (!empty($search)): ?>
            <a href="manage_products.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card-box" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 90px;">Image</th>
                    <th>Product Name &amp; Description</th>
                    <th style="width: 130px;">Category</th>
                    <th style="width: 120px;">Price</th>
                    <th style="width: 100px;">Stock</th>
                    <th style="width: 160px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($prod = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?= $prod['id'] ?></td>
                            <td>
                                <?php 
                                $img_src = !empty($prod['image']) && file_exists(__DIR__ . '/../uploads/' . $prod['image'])
                                    ? '../uploads/' . htmlspecialchars($prod['image'])
                                    : '../assets/css/placeholder.jpg';
                                ?>
                                <img src="<?= $img_src ?>" alt="" style="width: 55px; height: 55px; object-fit: contain; border-radius: 4px; background: #f8fafc;">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($prod['name']) ?></strong>
                                <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 3px;">
                                    <?= htmlspecialchars(substr($prod['description'], 0, 85)) ?>...
                                </div>
                            </td>
                            <td>
                                <span class="badge-status badge-processing"><?= htmlspecialchars($prod['category']) ?></span>
                            </td>
                            <td>
                                <strong style="color: var(--primary-color);">₹<?= number_format($prod['price'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="stock-status <?= $prod['stock'] > 0 ? '' : 'out' ?>">
                                    <?= $prod['stock'] ?> units
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <a href="edit_product.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-secondary" title="Edit Product">
                                        ✏️ Edit
                                    </a>

                                    <!-- =========================================================================
                                         JAVASCRIPT CONFIRMATION POPUP REQUIREMENT:
                                         Calls confirmProductDelete() in assets/js/script.js before deleting
                                         ========================================================================= -->
                                    <a href="delete_product.php?id=<?= $prod['id'] ?>" 
                                       onclick="return confirmProductDelete('<?= htmlspecialchars(addslashes($prod['name'])) ?>');"
                                       class="btn btn-sm btn-danger" 
                                       title="Delete Product">
                                        🗑️ Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No products found in the database. <a href="add_product.php">Add a product now</a>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


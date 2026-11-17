<?php
/**
 * Edit Product Module (admin/edit_product.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/../config/db.php';

// Access Control
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = "Access denied. Please login as administrator.";
    header("Location: login.php");
    exit();
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: manage_products.php");
    exit();
}

// Fetch existing product data
$stmt = mysqli_prepare($conn, "SELECT * FROM `products` WHERE `id` = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);

if (!$product) {
    $_SESSION['error'] = "Product not found.";
    header("Location: manage_products.php");
    exit();
}

$errors = [];

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = clean_input($_POST['name'] ?? '');
    $category    = clean_input($_POST['category'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $description = clean_input($_POST['description'] ?? '');

    // Validation
    if (empty($name)) {
        $errors[] = "Product Name is required.";
    }
    if (empty($category)) {
        $errors[] = "Category is required.";
    }
    if ($price <= 0) {
        $errors[] = "Price must be a positive number greater than 0.";
    }
    if ($stock < 0) {
        $errors[] = "Stock cannot be negative.";
    }
    if (empty($description)) {
        $errors[] = "Product description is required.";
    }

    $image_filename = $product['image']; // Default to current image

    // Check if new image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];

            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($file_ext, $allowed_extensions)) {
                $errors[] = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP, GIF.";
            }

            if ($file_size > 3 * 1024 * 1024) {
                $errors[] = "Image file is too large. Maximum allowed size is 3 MB.";
            }

            if (empty($errors)) {
                $new_filename = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $destination  = __DIR__ . '/../uploads/' . $new_filename;

                if (move_uploaded_file($file_tmp, $destination)) {
                    // Remove old image if exists
                    if (!empty($product['image'])) {
                        $old_path = __DIR__ . '/../uploads/' . $product['image'];
                        if (file_exists($old_path) && is_file($old_path)) {
                            @unlink($old_path);
                        }
                    }
                    $image_filename = $new_filename;
                } else {
                    $errors[] = "Failed to upload new image file.";
                }
            }
        } else {
            $errors[] = "Error uploading image file.";
        }
    }

    // Update database record if no errors
    if (empty($errors)) {
        $update_stmt = mysqli_prepare($conn, "
            UPDATE `products` 
            SET `name` = ?, `category` = ?, `price` = ?, `description` = ?, `image` = ?, `stock` = ? 
            WHERE `id` = ?
        ");
        mysqli_stmt_bind_param($update_stmt, "ssdssii", $name, $category, $price, $description, $image_filename, $stock, $product_id);

        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['success'] = "✓ Product '$name' updated successfully!";
            header("Location: manage_products.php");
            exit();
        } else {
            $errors[] = "Database update error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($update_stmt);
    }
}

$page_title = "Edit Product #" . $product['id'];
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">✏️ Edit Product Details</h2>
        <p class="section-subtitle">Modify catalog specifications or replace product image</p>
    </div>
    <a href="manage_products.php" class="btn btn-secondary btn-sm">&larr; Back to Products List</a>
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

<div class="card-box" style="max-width: 800px; margin: 0 auto;">
    <form action="edit_product.php?id=<?= $product_id ?>" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label for="name" class="form-label">Product Name / Title *</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="category" class="form-label">Category *</label>
                <input type="text" id="category" name="category" class="form-control" value="<?= htmlspecialchars($product['category']) ?>" required>
            </div>

            <div class="form-group">
                <label for="price" class="form-label">Price (in ₹ INR) *</label>
                <input type="number" step="0.01" min="1" id="price" name="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>

            <div class="form-group">
                <label for="stock" class="form-label">Stock Quantity *</label>
                <input type="number" min="0" id="stock" name="stock" class="form-control" value="<?= htmlspecialchars($product['stock']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Product Description *</label>
            <textarea id="description" name="description" class="form-control" rows="5" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <!-- Current Image Thumbnail & Upload Replacement Option -->
        <div class="form-group" style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 18px; border-radius: 6px;">
            <label class="form-label" style="font-weight: 700;">Product Image</label>
            
            <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 12px;">
                <div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Current Image:</div>
                    <?php 
                    $curr_img = !empty($product['image']) && file_exists(__DIR__ . '/../uploads/' . $product['image'])
                        ? '../uploads/' . htmlspecialchars($product['image'])
                        : '../assets/css/placeholder.jpg';
                    ?>
                    <img src="<?= $curr_img ?>" alt="" style="width: 80px; height: 80px; object-fit: contain; border: 1px solid var(--border-color); border-radius: 6px; background: #fff;">
                </div>

                <div style="flex: 1;">
                    <label for="image" class="form-label" style="font-size: 13px;">Replace Image (Optional):</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                        Leave empty to keep the current image. Supported formats: JPG, PNG, WEBP (Max: 3MB).
                    </p>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary btn-lg">
                💾 Save Changes
            </button>
            <a href="manage_products.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


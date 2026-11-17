<?php
/**
 * Add Product with Image Upload (admin/add_product.php)
 * Smart E-Commerce Web Application
 */
require_once __DIR__ . '/../config/db.php';

// Access Control
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['error'] = "Access denied. Please login as administrator.";
    header("Location: login.php");
    exit();
}

$name        = "";
$category    = "";
$price       = "";
$stock       = "10";
$description = "";
$errors      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = clean_input($_POST['name'] ?? '');
    $category    = clean_input($_POST['category'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $description = clean_input($_POST['description'] ?? '');

    // Form Validation
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

    // -------------------------------------------------------------------------
    // Product Image Upload & Validation
    // -------------------------------------------------------------------------
    $image_filename = null;

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Please select a product image to upload.";
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "An error occurred during image upload (Code: " . $_FILES['image']['error'] . ").";
    } else {
        $file_tmp  = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];

        // Get extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        // 1. Validate Extension
        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP, GIF.";
        }

        // 2. Validate File Size (Max 3MB)
        if ($file_size > 3 * 1024 * 1024) {
            $errors[] = "Image file is too large. Maximum allowed size is 3 MB.";
        }

        // 3. Move file to uploads directory if valid
        if (empty($errors)) {
            // Generate clean, collision-free filename
            $image_filename = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $destination    = __DIR__ . '/../uploads/' . $image_filename;

            if (!move_uploaded_file($file_tmp, $destination)) {
                $errors[] = "Failed to move uploaded image to target folder. Check permissions on 'uploads' directory.";
            }
        }
    }

    // Save to MySQL Database
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO `products` (`name`, `category`, `price`, `description`, `image`, `stock`) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "ssdssi", $name, $category, $price, $description, $image_filename, $stock);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "✓ Product '$name' added successfully with uploaded image!";
            header("Location: manage_products.php");
            exit();
        } else {
            $errors[] = "Database insert error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = "Add New Product";
include_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h2 class="section-title">➕ Add New Product</h2>
        <p class="section-subtitle">Upload product image and save catalog details to MySQL database</p>
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
    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label for="name" class="form-label">Product Name / Title *</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Wireless Bluetooth Noise-Cancelling Headphones" value="<?= htmlspecialchars($name) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="category" class="form-label">Category *</label>
                <input type="text" id="category" name="category" class="form-control" placeholder="e.g. Audio, Electronics, Wearables" value="<?= htmlspecialchars($category) ?>" required>
            </div>

            <div class="form-group">
                <label for="price" class="form-label">Price (in ₹ INR) *</label>
                <input type="number" step="0.01" min="1" id="price" name="price" class="form-control" placeholder="e.g. 2999.00" value="<?= htmlspecialchars($price) ?>" required>
            </div>

            <div class="form-group">
                <label for="stock" class="form-label">Stock Quantity *</label>
                <input type="number" min="0" id="stock" name="stock" class="form-control" placeholder="10" value="<?= htmlspecialchars($stock) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Product Description *</label>
            <textarea id="description" name="description" class="form-control" rows="5" placeholder="Enter key features, specs, and details of the product..." required><?= htmlspecialchars($description) ?></textarea>
        </div>

        <!-- =========================================================================
             IMAGE UPLOAD REQUIREMENT:
             HTML file input with validation attributes and PHP backend handling
             ========================================================================= -->
        <div class="form-group" style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 18px; border-radius: 6px;">
            <label for="image" class="form-label" style="font-size: 14px; font-weight: 700;">
                📸 Upload Product Image *
            </label>
            <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" required>
            <p style="font-size: 12px; color: var(--text-muted); margin-top: 6px;">
                Supported file formats: <strong>JPG, JPEG, PNG, WEBP, GIF</strong> (Maximum size: 3 MB).
                Image will be automatically placed in the <code>uploads/</code> directory and linked in MySQL.
            </p>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary btn-lg">
                💾 Save Product to Inventory
            </button>
            <a href="manage_products.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>


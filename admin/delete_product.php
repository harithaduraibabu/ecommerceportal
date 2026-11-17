<?php
/**
 * Delete Product Handler (admin/delete_product.php)
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

if ($product_id > 0) {
    // 1. Fetch image path so we can clean up the file
    $stmt = mysqli_prepare($conn, "SELECT `name`, `image` FROM `products` WHERE `id` = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $prod = mysqli_fetch_assoc($res);

    if ($prod) {
        $prod_name = $prod['name'];
        $image_name = $prod['image'];

        // 2. Delete product from MySQL
        $del_stmt = mysqli_prepare($conn, "DELETE FROM `products` WHERE `id` = ?");
        mysqli_stmt_bind_param($del_stmt, "i", $product_id);
        
        if (mysqli_stmt_execute($del_stmt)) {
            // 3. Remove physical image file from uploads folder if it exists
            if (!empty($image_name)) {
                $image_path = __DIR__ . '/../uploads/' . $image_name;
                if (file_exists($image_path) && is_file($image_path)) {
                    @unlink($image_path);
                }
            }
            $_SESSION['success'] = "✓ Product '$prod_name' was deleted successfully.";
        } else {
            $_SESSION['error'] = "Could not delete product due to a database error.";
        }
        mysqli_stmt_close($del_stmt);
    } else {
        $_SESSION['error'] = "Product not found.";
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Invalid product ID.";
}

header("Location: manage_products.php");
exit();
?>


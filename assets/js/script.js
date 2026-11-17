/**
 * ============================================================================
 * Smart E-Commerce Web Application - JavaScript
 * ============================================================================
 * Primary purpose: Display confirmation dialogs before critical user actions
 * (e.g., deleting a product, removing items from cart or wishlist).
 */

/**
 * 1. Confirm before deleting a product (Used in: admin/manage_products.php)
 * Returns true if user clicks "OK", false if user clicks "Cancel".
 */
function confirmProductDelete(productName) {
    return confirm("Are you sure you want to delete the product: \"" + productName + "\"?\nThis action cannot be undone.");
}

/**
 * 2. Confirm before removing an item from the shopping cart (Used in: cart.php)
 * Prevents accidental item removal during shopping.
 */
function confirmRemoveCartItem(itemName) {
    return confirm("Remove \"" + itemName + "\" from your shopping cart?");
}

/**
 * 3. Confirm before removing an item from the wishlist (Used in: wishlist.php)
 */
function confirmRemoveWishlistItem(itemName) {
    return confirm("Remove \"" + itemName + "\" from your wishlist?");
}

/**
 * 4. Auto-hide alert messages after 5 seconds (Optional UX enhancement)
 */
document.addEventListener("DOMContentLoaded", function() {
    var alerts = document.querySelectorAll(".alert-dismissible");
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(function(alertBox) {
                alertBox.style.transition = "opacity 0.6s ease";
                alertBox.style.opacity = "0";
                setTimeout(function() {
                    alertBox.remove();
                }, 600);
            });
        }, 4000);
    }
});


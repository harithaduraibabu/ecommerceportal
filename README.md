# Smart E-Commerce Web Application

**Resume Description:**
> "Built a full-stack e-commerce application featuring user authentication, product management, shopping cart, wishlist, checkout, order history, image upload, and MySQL-backed order processing through a responsive web interface."

---

## 1. Project Overview

- **Project Name:** Smart E-Commerce Web Application
- **Domain:** Web Development / Full-Stack Application
- **Primary Technologies:** Core PHP, MySQL (MySQLi), HTML5, CSS3
- **Minimal Technologies:** Bootstrap (Grid only), JavaScript (`confirm()` dialogs only)
- **Database:** `ecommerceportal`
- **Server:** Apache & MySQL (XAMPP)

---

## 2. Default Demo Credentials

| Role | Email | Password | Access Link |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@ecommerce.com` | `admin123` | [Admin Login](http://localhost/e-commerce%20portal/admin/login.php) |
| **Customer** | `rahul@gmail.com` | `user123` | [Customer Login](http://localhost/e-commerce%20portal/login.php) |

---

## 3. How to Run the Project on XAMPP

1. Open **XAMPP Control Panel** and ensure **Apache** and **MySQL** are running.
2. The project files reside inside your XAMPP web root:
   - `C:\xampp\htdocs\ecommerce portal\`
3. Open your web browser and navigate to:
   - **Customer Website:** `http://localhost/e-commerce%20portal/` or `http://localhost/ecommerce%20portal/`
   - **Admin Portal:** `http://localhost/e-commerce%20portal/admin/login.php`
   - **phpMyAdmin:** `http://localhost/phpmyadmin/`
4. The database `ecommerceportal` is already configured with all tables (`users`, `products`, `wishlist`, `orders`, `order_items`) and initial demo products. If ever needed to re-import, run `database.sql` into phpMyAdmin.

---

## 4. Interview Preparation: Technology Usage

### A. Exactly Where and How Bootstrap Is Used
* **Where:** `index.php` and `products.php` (linked via `assets/css/bootstrap.min.css`).
* **Purpose:** Minimal usage strictly for the **12-column responsive grid layout** of product cards.
* **Identifiable Code Snippet:**
  ```html
  <div class="row g-4">
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="product-card">
              <!-- Product image, title, price, Add to Cart & Wishlist -->
          </div>
      </div>
  </div>
  ```
* **Interview Explanation:**
  > *"I used Bootstrap's responsive grid system (`.row` with `.col-12 .col-sm-6 .col-md-4 .col-lg-3`) so that our product catalog dynamically adapts to all screen sizes without complex media queries: 1 card per row on mobile phones, 2 on small tablets, 3 on laptops, and 4 on desktop monitors."*

---

### B. Exactly Where and How JavaScript Is Used
* **Where:** `assets/js/script.js` (invoked on delete and remove buttons).
* **Purpose:** Vanilla JavaScript `confirm()` dialogs to prevent accidental deletion of inventory or removal of shopping cart and wishlist items.
* **Identifiable Code Snippets:**
  1. **In `assets/js/script.js`:**
     ```javascript
     function confirmProductDelete(productName) {
         return confirm("Are you sure you want to delete the product: \"" + productName + "\"?\nThis action cannot be undone.");
     }

     function confirmRemoveCartItem(itemName) {
         return confirm("Remove \"" + itemName + "\" from your shopping cart?");
     }

     function confirmRemoveWishlistItem(itemName) {
         return confirm("Remove \"" + itemName + "\" from your wishlist?");
     }
     ```
  2. **In `admin/manage_products.php`:**
     ```php
     <a href="delete_product.php?id=<?= $prod['id'] ?>" 
        onclick="return confirmProductDelete('<?= htmlspecialchars(addslashes($prod['name'])) ?>');" 
        class="btn btn-sm btn-danger">
        🗑️ Delete
     </a>
     ```
  3. **In `cart.php` and `wishlist.php`:**
     ```php
     <a href="cart.php?action=remove&id=<?= $item['id'] ?>" 
        onclick="return confirmRemoveCartItem('<?= htmlspecialchars(addslashes($item['name'])) ?>');">
        ✕ Remove
     </a>
     ```
* **Interview Explanation:**
  > *"I used vanilla JavaScript with the built-in `confirm()` method hooked to `onclick` events on critical action buttons. If the user clicks 'OK', the function returns `true` and the deletion proceeds; if the user clicks 'Cancel', it returns `false` and cancels the HTTP navigation, preventing accidental data loss."*

---

## 5. Security & Best Practices Implemented

1. **Password Hashing:** Passwords are cryptographically hashed using PHP `password_hash($pass, PASSWORD_DEFAULT)` and verified with `password_verify()`.
2. **SQL Injection Protection:** Database queries use **MySQLi prepared statements** (`mysqli_prepare()`, `mysqli_stmt_bind_param()`, `mysqli_stmt_execute()`).
3. **Cross-Site Scripting (XSS) Prevention:** Output rendering is escaped with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
4. **Session Authentication & Role Authorization:** Protected customer pages (`wishlist.php`, `checkout.php`, `order_history.php`) require `$_SESSION['user_id']`. Admin pages require `$_SESSION['admin_logged_in'] === true`.
5. **Atomic Transactions:** Order placement in `checkout.php` executes within a MySQL transaction (`mysqli_begin_transaction()`, `mysqli_commit()`, `mysqli_rollback()`) ensuring order headers, items, and inventory stock updates succeed together.
6. **File Upload Validation:** Product images are checked for allowed extensions (`jpg, jpeg, png, webp, gif`), max file size limit (3 MB), and saved with unique timestamps.

---

## 6. Project Directory Structure

```
ecommerce portal/
│
├── index.php                 # Customer Homepage with Hero Banner & Featured Grid
├── products.php              # Full Catalog with Search & Category Filters
├── product_details.php       # Product Specifications & Add to Cart
├── cart.php                  # Shopping Cart (Add, Update Qty, Subtotal, Remove)
├── wishlist.php              # Customer Wishlist (Add, Move to Cart, Remove)
├── checkout.php              # Order Placement with Address & Cash on Delivery
├── order_history.php         # Customer Previous Orders & Items Breakdown
├── register.php              # User Registration with Password Hashing
├── login.php                 # User Login & Session Establishment
├── logout.php                # Session Destruction
├── database.sql              # Complete Database Schema & Seed Data Script
├── README.md                 # Project Documentation & Interview Guide
│
├── config/
│   └── db.php                # MySQLi Database Connection (Multi-Port 3307/3306)
│
├── includes/
│   ├── header.php            # HTML5 Head, Navigation Include, Flash Alerts
│   ├── navbar.php            # Sticky Responsive Navbar with Cart/Wishlist Badges
│   └── footer.php            # Footer, Quick Links & Script Inclusions
│
├── admin/
│   ├── login.php             # Admin Portal Authentication
│   ├── dashboard.php         # Admin Metrics, Stats & Recent Orders
│   ├── add_product.php       # Product Creation with File Upload Handling
│   ├── manage_products.php   # Catalog Table with Edit & JS Confirm Delete
│   ├── edit_product.php      # Product Modification & Optional Image Replacement
│   ├── delete_product.php    # Record Deletion & Image Cleanup
│   ├── orders.php            # Order Management & Status Transition (Pending -> Delivered)
│   ├── logout.php            # Admin Logout
│   └── includes/
│       ├── header.php        # Admin Header Component
│       ├── navbar.php        # Admin Navigation Component
│       └── footer.php        # Admin Footer Component
│
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css # Standalone Bootstrap Grid (Offline Ready)
│   │   └── style.css         # Modern, Clean Custom Stylesheet
│   └── js/
│       └── script.js         # Basic Action Confirmation Popups (confirm())
│
└── uploads/                  # Product Image Storage Directory
    ├── headphones.jpg
    ├── smartwatch.jpg
    ├── keyboard.jpg
    ├── mouse.jpg
    ├── speaker.jpg
    ├── webcam.jpg
    ├── charger.jpg
    └── hub.jpg
```


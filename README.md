# Campus Cart
> Simple, minimal, and fully functional college marketplace web application built with PHP and SQLite.

## Overview
**Campus Cart** is a lightweight peer-to-peer marketplace designed for college campuses. Students can buy and sell used textbooks, calculators, electronics, drafting tools, and lecture notes directly with other campus peers.

Built with a classic academic portal UI aesthetic (slate/blue color palette, solid borders, system typography) and a clean modular architecture featuring completely separate HTML/PHP, CSS, and JS files for each page.

---

## Project Structure

```
campus-cart/
├── config/
│   └── db.php                  # SQLite PDO connection & auto-schema creator (database.sqlite)
├── public/
│   ├── includes/
│   │   ├── navbar.php          # Reusable navigation bar partial
│   │   └── footer.php          # Reusable footer & campus safety guidelines partial
│   ├── assets/
│   │   ├── css/
│   │   │   ├── global.css      # Shared base styles, resets, buttons, forms, alerts
│   │   │   ├── index.css       # Styles for Home page & Hero Carousel
│   │   │   ├── auth.css        # Styles for login.php and register.php
│   │   │   ├── sell.css        # Styles for sell.php
│   │   │   ├── cart.css        # Styles for cart.php & Payment Modal
│   │   │   └── profile.css     # Styles for profile.php
│   │   └── js/
│   │       ├── carousel.js     # Auto-scroll carousel logic for index.php
│   │       ├── cart.js         # Cart interaction logic & payment method switcher
│   │       └── main.js         # Image preview, alerts dismissal, smooth scroll
│   ├── index.php               # Landing Home Page (links global.css + index.css + carousel.js)
│   ├── login.php               # Student Login Page (links global.css + auth.css)
│   ├── register.php            # Student Registration Page (links global.css + auth.css)
│   ├── sell.php                # Item Listing Page (links global.css + sell.css)
│   ├── cart.php                # Shopping Cart & Checkout (links global.css + cart.css + cart.js)
│   ├── profile.php             # Profile Dashboard & Order History (links global.css + profile.css)
│   └── logout.php              # Session termination script
└── README.md                   # Setup guide and documentation
```

---

## Quick XAMPP Local Setup Guide (Windows / macOS / Linux)

### Step 1: Copy to XAMPP `htdocs`
Copy or move the entire `campus-cart` folder into your XAMPP web root directory:
- **Windows**: `C:\xampp\htdocs\campus-cart`
- **macOS**: `/Applications/XAMPP/xamppfiles/htdocs/campus-cart`
- **Linux**: `/opt/lampp/htdocs/campus-cart`

### Step 2: Start Apache in XAMPP
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
*(Note: MySQL is NOT required because Campus Cart uses SQLite PDO, which is built directly into standard PHP).*

### Step 3: Ensure Write Permissions for SQLite
SQLite needs to write the `database.sqlite` file inside `campus-cart/config/`.
- On Windows: Permissions are typically enabled by default in `C:\xampp\htdocs\`.
- On macOS / Linux, run:
  ```bash
  chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/campus-cart/config
  ```

### Step 4: Open in Web Browser
Open your browser and navigate to:
```
http://localhost/campus-cart/public/
```
*(or `http://localhost/campus-cart/public/index.php`)*

---

## Automatic Database Setup
No manual SQL imports or phpMyAdmin setups are needed! 
When you load the app for the first time, `config/db.php` automatically:
1. Creates `config/database.sqlite`.
2. Creates the required SQLite tables:
   - `users` (id, name, email, password, created_at)
   - `products` (id, user_id, title, category, description, price, image_url, created_at)
   - `cart` (id, user_id, product_id, created_at)
   - `orders` (id, user_id, total_amount, payment_method, created_at)
3. Seeds starter listings (Textbooks, Casio Calculator, Arduino Starter Kit, Notes, Drafting Kit) and sample student accounts for instant testing.

---

## Demo Accounts (Pre-Seeded)

| Name | Student Email | Password |
|------|---------------|----------|
| Alex Morgan | `alex@campus.edu` | `password123` |
| Samantha Roy | `samantha@campus.edu` | `password123` |
| David Kim | `david@campus.edu` | `password123` |

You can also register a brand new account anytime via `register.php`.

---

## Features & Implementation Highlights

1. **Authentication (`login.php` & `register.php`)**:
   - Secure password hashing with PHP's `password_hash()` (Bcrypt) and `password_verify()`.
   - Native PHP session management with protection against empty/duplicate emails.

2. **Hero Carousel (`index.php` & `assets/js/carousel.js`)**:
   - Seamless 3-second auto-scrolling hero track showcasing campus announcements and categories.

3. **Category Filtering & Search (`index.php`)**:
   - Filter items instantly by Electronics, Books, Stationery, Notes.
   - Text search across titles and descriptions.

4. **Sell Item (`sell.php`)**:
   - Add new marketplace listings with direct image URL preview, category select, and price validation.

5. **Shopping Cart & Checkout Modal (`cart.php` & `assets/js/cart.js`)**:
   - Add to cart from home page.
   - View itemized list, calculated totals, and free campus pickup fee.
   - Remove items from cart.
   - **Proceed to Buy** opens the payment modal with support for **UPI**, **Debit/Credit Card**, and **Cash on Delivery (COD) / Campus Meetup**.
   - Clears cart and creates order record.

6. **Student Profile (`profile.php`)**:
   - Displays student name, email, and live statistics.
   - Manage listed items with instant **Delete** capability.
   - Full order history with order ID, date, payment method, and amount.

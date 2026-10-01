# Brew Haven Coffee Shop

An in-progress PHP 8 / MySQL café storefront built on the existing project files. The current working slice includes the storefront, database schema, account registration/login, customer cart, cash-on-delivery checkout, menu filters, reservations, contact messages and newsletter subscriptions.

## Run with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. For a brand-new database, open phpMyAdmin at `http://localhost/phpmyadmin` and import `database/schema.sql`. For the original Brew Haven database, back it up and import `database/upgrade_legacy.sql` once instead; that migration adds missing columns and tables without dropping the existing customer/order data. Do not run the one-time migration a second time.
3. Keep the project in `C:\xampp\htdocs\brew-haven-coffee-shop` and visit `http://localhost/brew-haven-coffee-shop/`.
4. XAMPP's default local database settings are used by default (`localhost`, database `coffee_shop`, user `root`, blank password). Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the Apache environment when using different credentials.

The schema inserts seven categories, a few example drinks, three gallery images, and basic store settings. Menu images reference files already in `assets/images/menu`. No default administrator account is seeded: create an administrator with a password hash rather than shipping a shared default credential. For example, in a trusted PHP shell, generate a hash with `password_hash()` and insert the resulting hash into `admins.password` using a prepared statement.

## Engineering choices

- Bootstrap 5 remains the single UI framework. Adding Tailwind alongside it would duplicate utilities and increase CSS/build complexity without a project need.
- Passwords are hashed; database writes use PDO prepared statements; user-controlled output uses HTML escaping; state-changing forms use session CSRF tokens.
- Checkout supports Cash on Delivery only. The card selector is disabled and the endpoint rejects non-COD values. Do not accept or store card data; enable an online method only after integrating a real provider with server-side verification and webhooks.
- Delivery is currently a fixed ৳60 and tax is ৳0 in the checkout logic. Configure these business rules before taking real orders.
- The contact page stores messages; it does not send email. Reservation requests remain Pending until staff confirm them.

## Current project boundaries

The brief describes a full café management system. This update establishes the customer-facing foundation plus an administrator sign-in, overview and product catalogue CRUD; the remaining admin CRUD (orders, reservations, customers, reviews, gallery, offers, subscribers, messages, admins and settings), password reset delivery, email verification, profile-picture uploads, wishlist, full order history UI, coupon rules, pagination, a complete REST API, and provider-backed payments still need implementation. Password resets and email verification should be connected to a real mail delivery service before they are exposed; fake reset links are unsafe. Replace the starter policy text and add verified store contact/address/map details before launch.

## Manual smoke check

After importing the schema, open Home and Menu; register a customer with a valid email and a password of at least 10 characters; log in; add a product; update/remove it in Cart; place a Cash on Delivery order; confirm the order in `orders`, line items in `order_items`, payment intent in `payments`, and cleared cart in phpMyAdmin. Submit a reservation, contact form and newsletter email, then confirm rows appear in their respective tables. Try a duplicate newsletter subscription and a checkout with an empty cart. These checks require a running local XAMPP database.

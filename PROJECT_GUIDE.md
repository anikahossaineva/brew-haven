# Brew Haven Coffee Shop — Project Study Guide

This guide explains the project as it exists in this folder. It is written to help you explain the architecture in a class presentation or interview. Read the request flows first, then use the file map to navigate the source.

## 1. The project in one sentence

Brew Haven is a server-rendered PHP café storefront. Apache serves PHP files, PHP reads/writes MySQL through PDO, PHP combines reusable HTML fragments into a response, and browser JavaScript handles a few asynchronous actions such as the cart and newsletter form.

The browser never connects directly to MySQL. It requests a URL from Apache; Apache runs PHP; PHP talks to MySQL; then PHP returns HTML or JSON to the browser.

## 2. What runs when you open the website

For a local XAMPP URL such as `http://localhost/brew-haven-coffee-shop/`:

1. The browser sends an HTTP `GET` request.
2. Apache looks in `C:\xampp\htdocs\brew-haven-coffee-shop` and uses `index.php` as the directory's default page.
3. `index.php` includes `config/database.php`. That file starts the session through `config/security.php` and creates a PDO connection called `$conn`.
4. `index.php` includes the shared header and navigation, then each home section. Included PHP files run in the same request and can use `$conn`.
5. A section may query MySQL, for example featured products or categories.
6. PHP sends the completed HTML to Apache, which sends it to the browser.
7. The browser loads CSS, images, Bootstrap, Font Awesome and JavaScript referenced by that HTML.

`include` is PHP composition: it inserts and executes another PHP file at that point. It is not a browser redirect and it does not make a separate HTTP request.

## 3. Request map: which file calls which

```text
Browser GET /
└── Apache → index.php
    ├── config/database.php
    │   └── config/security.php (session + helper functions)
    ├── includes/header.php (head tags, CSS, opens body)
    ├── includes/navbar.php (navigation + signed-in cart count)
    ├── includes/home/*.php (home sections; some query the database)
    ├── includes/footer.php (newsletter form and policy links)
    └── includes/scripts.php (Bootstrap JS + local JS)

Browser POST from the cart's Add button
└── assets/js/cart.js
    └── fetch POST → api/add_to_cart.php
        ├── config/database.php / security.php
        ├── validates request, CSRF token, session and product
        ├── INSERT or UPDATE in cart
        └── returns JSON → JavaScript updates badge / toast

Browser POST from Checkout
└── checkout.php form
    └── api/checkout.php
        ├── verifies session, CSRF and COD method
        ├── starts a MySQL transaction
        ├── reads cart and stock; creates order and order_items
        ├── reduces stock, creates payment record, clears cart
        ├── commits transaction
        └── redirects to order-success.php
```

Other route groups:

| Browser page | PHP entry file | Shared/database dependencies | Main action |
|---|---|---|---|
| Home | `index.php` | header, navbar, home sections, footer, scripts | Displays promotional content and dynamic sections |
| Menu | `menu.php` | database, header/navbar/footer/scripts | Reads categories/products; filters search/category/sort using query-string values |
| Product detail | `product.php?id=ID` | database, header/navbar/footer/scripts | Loads one product by numeric ID |
| Cart | `cart.php` | database, header/navbar/footer/scripts, `cart.js` | Reads the signed-in customer's cart rows |
| Checkout | `checkout.php` | database, session, shared layout | Shows cart total and sends a POST to the checkout endpoint |
| Register / login | `register.php`, `login.php` | database/security, shared layout | Validates credentials and stores login state in session |
| Reservation | `reservation.php` | database/security, shared layout | Validates and saves a reservation request |
| Contact | `contact.php` | database/security, shared layout | Validates and saves a contact message |
| Gallery | `gallery.php` | database, shared layout | Reads active gallery records |
| Admin login | `admin/login.php` | database/security, shared layout | Checks the separate `admins` table and sets `admin_id` in session |
| Admin overview | `admin/index.php` | database/security | Reads counts, latest orders and reservations |
| Admin products | `admin/products.php` | database/security | Admin-only product create, update and delete |
| JSON cart/newsletter actions | `api/*.php` | database/security | Return JSON rather than a full HTML page |

## 4. The main ideas you need to understand

### PHP variables and includes

`$conn` is the PDO database connection. Since `index.php` includes `config/database.php` before including home sections, the sections can use `$conn` directly. PHP includes inherit the current variable scope. If a page forgets to create `$conn` before including a section that queries it, that section will fail.

The project uses `include` or `require`:

- `include` emits a warning if a file cannot be loaded and may continue.
- `require` stops the request if a required file is missing.
- `*_once` avoids loading the same file repeatedly.

### PDO and prepared statements

`config/database.php` creates PDO with a MySQL DSN containing the host, database name and `utf8mb4` character set. The options make SQL errors throw exceptions, return rows as associative arrays by default, and disable emulated prepares.

Example from registration:

```php
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
```

The `?` is a parameter position. PDO sends the SQL template and the value separately, so an email containing SQL characters is treated as data. Do not build SQL by concatenating user input. Dynamic sort columns cannot be bound as parameters; `menu.php` handles that with a whitelist (`$sortOptions`) and only inserts one of those hard-coded SQL fragments.

### Sessions and login

The browser stores a session cookie. That cookie is an identifier; the actual `user_id`, name and role values stay on the server in `$_SESSION`.

`config/security.php` starts the session with `HttpOnly` and `SameSite=Lax` cookie attributes; `Secure` is enabled when HTTPS is active. `login.php` verifies the submitted password against the saved hash, regenerates the session ID to reduce session-fixation risk, and stores the user ID/name/role. Protected pages check `isset($_SESSION['user_id'])`. Logout clears the session and is a POST form protected by CSRF.

Admin authentication uses `admins` and stores `admin_id`/`admin_name`. It is a separate login from the customer account.

### Escaping and CSRF

`e($value)` in `config/security.php` is shorthand for `htmlspecialchars` with quote and UTF-8 handling. Use it when putting text from the database or a user into HTML text/attributes. It helps prevent cross-site scripting (XSS). It is not SQL protection; PDO prepared statements handle that separate risk.

CSRF means Cross-Site Request Forgery. `csrf_token()` creates a random session token. Forms send it in a hidden field, or JavaScript reads the `<meta name="csrf-token">` created by `includes/header.php`. The endpoint checks it with `csrf_valid()` before changing state. A GET request should not delete or create data.

## 5. Important files, explained by logical blocks

### `index.php`

This is the homepage controller/template. It:

1. Connects to the database.
2. Includes the page head and navigation.
3. Includes hero, features, why-us, discover, category, popular product, offer, testimonial and gallery sections.
4. Outputs a reservation call-to-action.
5. Includes the shared footer and scripts.

Because the sections are included in order, the HTML appears in that order. `includes/home/story.php`, `includes/home/about.php` and `includes/home/newsletter.php` exist but are not included by this page. The newsletter currently lives in the shared footer instead.

### `config/database.php`

Reads `DB_HOST`, `DB_NAME`, `DB_USER` and `DB_PASSWORD` from the environment, with XAMPP defaults (`localhost`, `coffee_shop`, `root`, blank password). The `try` block builds `$conn`. If connection fails, details go to the server error log and the visitor sees a generic unavailable message. This avoids displaying database credentials/stack traces in the page.

### `config/security.php`

Starts the session once, configures cookie flags, and defines three helpers:

- `csrf_token()` returns a persistent per-session random token.
- `csrf_valid($token)` checks the submitted token using constant-time `hash_equals`.
- `e($value)` encodes a string for HTML output.

### Shared layout: `includes/header.php`, `navbar.php`, `footer.php`, `scripts.php`

- `header.php` emits `<!doctype html>`, `<head>`, meta tags, CDN styles and custom CSS. It opens `<body>` and sets `$assetPrefix` to `../` for admin pages so CSS paths work from `/admin/`.
- `navbar.php` outputs the site navigation. If logged in, it runs a parameterized `SUM(quantity)` query for the cart badge and shows a user menu. Otherwise it shows Login/Register.
- `footer.php` contains common links and the newsletter form. Its hidden input contains the CSRF token.
- `scripts.php` loads Bootstrap JavaScript and local `script.js` and `cart.js`, then closes the document.

### Home sections in `includes/home/`

Each section is a partial PHP/HTML template, included by `index.php`:

- `hero.php`: headline, hero image and buttons. The current buttons use `href="#"`, so they do not yet navigate to Menu/Cart.
- `features.php`: queries up to three products and displays product cards.
- `why-us.php`: static selling points.
- `discover.php`: links to the “Mood Match” experience.
- `categories.php`: reads category names and chooses a category-specific emoji from a normalized-name map. Unknown names use a generic food icon.
- `popular.php`: displays featured items (`is_available = 1` and `is_featured = 1`).
- `special.php`: displays active offers within their date range.
- `testimonials.php`: displays only approved reviews.
- `gallery.php`: displays up to four active gallery records.
- `story.php`, `about.php`, `newsletter.php`: older/unused partials at the moment.

These files often assume `$conn`, `e()` and the session were already initialized by the parent page. That is why the order of includes matters.

### `menu.php`

Reads `category`, `q` and `sort` from the URL, e.g.:

```text
menu.php?category=2&q=latte&sort=price-low
```

`category` is validated as an integer. Search is length-limited and bound through SQL parameters. `$sortOptions` is a whitelist because SQL identifiers/order clauses cannot be safely replaced with a normal `?` placeholder. It selects matching products and renders one Bootstrap card per product. The Add button's `data-id` gives JavaScript the product ID.

### `register.php` and `login.php`

Both process a form only when the HTTP method is POST and the CSRF token matches. Registration trims and validates the fields, checks email format/length, compares confirmation with `hash_equals`, then stores `password_hash($password, PASSWORD_DEFAULT)` rather than the raw password. Login selects one user by email and checks with `password_verify`. A generic “email or password is incorrect” message avoids telling an attacker whether an account exists.

### `assets/js/cart.js`

Waits for `DOMContentLoaded`, finds buttons by CSS class and listens for clicks. Add/quantity/remove requests use `fetch()` and send form-encoded values plus the CSRF token. `data-id` is how the button tells PHP which product/cart row was clicked. A successful add shows a toast and updates `.cart-count`.

**Current code issue:** `csrfToken` is declared inside the `DOMContentLoaded` callback, but `updateCart()` is declared outside it and refers to `csrfToken`. JavaScript block scope means `updateCart()` cannot see that variable; increase/decrease can throw a `ReferenceError`. This is an example of a bug to describe honestly and fix by moving the token to outer scope or passing it as an argument.

Another current behavior: if an Add request fails for any reason, the JS redirects to login—even if the actual response is “out of stock.” A better UI would display the endpoint's message and redirect only for HTTP 401.

### API endpoints under `api/`

An API endpoint is a PHP file whose response is usually JSON, not a full HTML page. JavaScript can read the response without reloading everything.

- `add_to_cart.php`: accepts `product_id`; requires POST, valid CSRF, logged-in user, available product and stock; inserts or increments a cart row; responds with `success`, `message` and `cart_count`.
- `update_cart.php`: accepts `cart_id` and `action` (`increase` or `decrease`); checks that the row belongs to the signed-in user; updates quantity and returns totals.
- `remove_cart.php`: deletes only the requested cart row belonging to the current user.
- `get_cart_count.php`: returns the current user's total item count.
- `newsletter.php`: validates the email, uses `INSERT IGNORE` against the newsletter email unique key, and returns a message for new/duplicate subscription.
- `checkout.php`: HTML form target; performs the order transaction and then redirects, so it is not a JSON API despite living in `api/`.

Example JSON from add-to-cart:

```json
{"success":true,"message":"Added to your cart.","cart_count":2}
```

### Cart and checkout

`cart.php` does not trust the browser to supply prices. It loads cart rows by the session's `user_id` and joins to `products` to get current names/prices. It calculates display subtotals in PHP. Quantity buttons update via the API.

`checkout.php` is the review page: it reads current cart items and shows a total. The form posts a CSRF token and payment method to `api/checkout.php`.

`api/checkout.php` re-reads everything from the database, because browser totals can be edited. It starts a transaction, locks/read-checks cart stock, calculates prices server-side, loads the customer's saved address, inserts an order, inserts immutable order-item snapshots, decrements stock, writes a pending COD payment record, removes cart rows and commits. On any thrown error it rolls back. A transaction makes the set of database changes all succeed or all fail, avoiding (for example) an order header without its line items.

Only Cash on Delivery is enabled. Card payment is not implemented; never collect card details in this project until a real provider flow and server-side webhook verification exist.

`order-success.php` only shows a recent order if its ID is stored in the session and belongs to the signed-in user. This prevents someone from changing an order ID in the URL to see another customer's order.

### Reservations, contact and newsletter

- `reservation.php` validates name, phone, email, guest count, a future date/time and request length, then inserts a row with `Pending` status. “Pending” means it is a request, not a confirmed booking.
- `contact.php` validates the form and stores a row in `contact_messages`; it does not send an email.
- The footer form submits asynchronously. `assets/js/script.js` uses `FormData` and `fetch()` to call `api/newsletter.php`, then sets a text-only status message.

### Admin files

- `admin/login.php` verifies an admin password hash from `admins`, regenerates the session ID and sets `admin_id`/`admin_name`.
- `admin/index.php` checks `admin_id`, queries counts/revenue and shows recent orders/reservations.
- `admin/products.php` checks `admin_id`; POST requests require CSRF; it creates/updates/deletes product rows using prepared statements. Image selection is currently limited to three existing files, not uploads.
- `admin/logout.php` requires a valid POST/CSRF token and clears admin session fields.

There is no public admin registration page. A trusted administrator account must be created manually with a PHP-generated password hash. Never insert a plain-text password.

### Other pages

- `about.php`, `gallery.php`, `privacy.php`, `terms.php`, `404.php` are page templates using shared layout. The policy pages are starter text, not jurisdiction-specific legal documents.
- `product.php` reads `?id=...` and loads the matching product. **Current code issue:** it includes the HTML header before redirecting for a missing ID, and its Add to Cart button does not have the `.add-cart-btn` class/`data-id` expected by `cart.js`; that button therefore is not wired to the cart API. Its price still uses `$`, unlike other newer pages.
- `discover.php` shows mood cards and uses a small inline script to set a hidden mood value. The other selectors/form do not submit to a recommendation backend yet; the quiz is currently a front-end prototype.
- `dashboard.php` requires a customer session and shows order/cart/review counts and recent orders. Full order-history, profile editing, reservations list and wishlist features are not implemented yet.
- `privacy.php`/`terms.php` and the directory names do not enforce server routing. Apache maps a requested `.php` path to that file.

## 6. Database map

The SQL is in `database/schema.sql` (new installation) and `database/upgrade_legacy.sql` (one-time migration from the original tables). Do not import both blindly. Existing tables are not changed by `CREATE TABLE IF NOT EXISTS`, which is why the migration is needed for an old database.

Main relationships:

```text
users 1 ── many cart rows ── 1 products ── many product_images
users 1 ── many orders 1 ── many order_items ── optional product reference
orders 1 ── many payments
users 1 ── many reservations
users 1 ── many reviews ── optional products
categories 1 ── many products
admins 1 ── many activity_logs
```

Tables in the database plan:

| Table | Purpose |
|---|---|
| `users` | Customer identity, password hash, address and role |
| `admins` | Separate administrator credentials |
| `categories` | Menu group labels, slugs and display order |
| `products` | Menu names, prices, stock, image and visibility/featured flags |
| `product_images` | Extra images per product |
| `cart` | One user/product/quantity row per cart item in the current PHP code |
| `cart_items` | Present in the fresh schema, but currently unused; the current code stores lines directly in `cart` |
| `orders` | Order header, status, totals, address and payment method |
| `order_items` | Product-name/price/quantity snapshot for each order line |
| `payments` | Payment provider reference, amount and status (COD currently) |
| `reservations` | Requested table booking and its status |
| `reviews` | Customer rating/text and admin approval state |
| `gallery` | Site gallery image metadata |
| `offers` | Date-limited promotional content |
| `contact_messages` | Submitted contact form messages |
| `newsletter` | Unique subscriber emails |
| `settings` | Store configuration key/value pairs |
| `password_resets` | Hashed reset tokens and expiry (email delivery not implemented) |
| `activity_logs` | Admin action history (not yet written by admin actions) |

`PRIMARY KEY` uniquely identifies a row. `FOREIGN KEY` protects relationships. `UNIQUE` prevents duplicate emails/slugs/cart product rows where used. Indexes speed common lookups. `AUTO_INCREMENT` creates numeric IDs. Decimal is used for money instead of floating point columns.

## 7. Frontend assets

- `assets/css/style.css`: custom site styles. The major sections are hero, navbar/logo, featured cards, about/categories, forms, cart/dashboard cards, discover and mood cards. Bootstrap classes supply much of the responsive grid/layout.
- `assets/js/script.js`: handles the footer newsletter submission.
- `assets/js/cart.js`: handles cart AJAX buttons and toast notifications.
- `assets/images/`: static logo, hero, about and product images. Product rows store an image filename; product pages construct an `assets/images/menu/...` URL.

CDN dependencies are loaded in `includes/header.php`: Google Fonts, Bootstrap CSS and Font Awesome. Bootstrap JS is loaded in `includes/scripts.php`. The project does not currently build/minify assets locally.

## 8. How to answer common interview questions

**Q: Walk me through a request.**  
“Apache receives the URL and runs its PHP entry file. The entry file creates the PDO connection and session, includes shared layout/sections, queries MySQL with prepared statements, and returns HTML. Browser JavaScript can later call a PHP API endpoint with `fetch()` for smaller updates.”

**Q: How does Add to Cart work?**  
“The product button has a product ID in a `data-id` attribute. `cart.js` sends that ID and the CSRF token to `api/add_to_cart.php`. The endpoint checks the request method, token, session, product availability and stock, then inserts or increments a row in `cart` for that user and returns JSON. JavaScript updates the cart badge.”

**Q: How do you stop SQL injection?**  
“Values are passed into PDO prepared statements, not concatenated into SQL. For SQL fragments such as sort direction, I select from a hard-coded whitelist, because identifiers cannot be passed as normal prepared-statement values.”

**Q: Where are passwords kept?**  
“Registration saves `password_hash()` output. Login fetches that hash and uses `password_verify()`. Plain-text passwords are never saved.”

**Q: Why a transaction at checkout?**  
“The order header, line items, inventory decrement, payment record and cart clear are one logical operation. A transaction lets the database commit all of them together or roll them all back if something fails.”

**Q: What does AJAX mean here?**  
“The browser uses `fetch()` to POST to a PHP endpoint and receives JSON, so it can update the cart count or newsletter message without submitting a full HTML page.”

**Q: What's the difference between authentication and authorization?**  
“Authentication establishes who signed in. Authorization decides what they can do. This app authenticates customers and admins separately; protected customer pages check `user_id`, and admin pages check `admin_id`.”

**Q: What would you improve before production?**  
“Fix the cart JavaScript token scope and product-detail Add button; add automated tests and request throttling; complete admin authorization/CRUD; implement email-based reset/verification; use a real payment provider with webhook verification; move delivery/tax into validated settings; configure HTTPS/secrets; and replace sample policy/contact details with verified business information.”

## 9. A practical study plan

1. Trace Home: `index.php` → `config/database.php` → `includes/header.php` → `includes/navbar.php` → a database-backed home section.
2. Trace a plain page like `about.php` and identify what the shared layout adds.
3. Trace registration, then explain why the stored value is a hash.
4. Trace login and explain the session cookie versus `$_SESSION` data.
5. Trace Add to Cart from the button's `data-id` to JSON response.
6. Trace checkout and explain each transaction step and rollback.
7. Draw the `users → cart → products` and `users → orders → order_items` relationships from the tables.
8. Open one CSS file and one JS file and explain the selector/event that links the browser UI to its action.

For a literal line-by-line walkthrough, study one file at a time in this order: `index.php`, `config/security.php`, `config/database.php`, `includes/header.php`, `includes/navbar.php`, `menu.php`, `assets/js/cart.js`, `api/add_to_cart.php`, `cart.php`, `checkout.php`, `api/checkout.php`. Those files cover the central architecture end to end.

## 10. Current limitations to state honestly

This is a student-project foundation, not every feature in the original brief. Card payments, reset/verification email delivery, wishlist, profile uploads, full customer order history, complete REST API and most admin CRUD are not implemented. `discover.php` is partly a UI prototype. Keep the current checkout on COD, and do not claim these unfinished flows are production-ready.

-- One-time upgrade for the original Brew Haven tables (id columns are INT, not BIGINT).
-- Back up coffee_shop before importing this file in phpMyAdmin.
USE coffee_shop;

ALTER TABLE categories
  ADD COLUMN slug VARCHAR(100) NULL,
  ADD COLUMN description VARCHAR(500) NULL,
  ADD COLUMN image VARCHAR(255) NULL,
  ADD COLUMN sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;
UPDATE categories SET slug = CONCAT('category-', id) WHERE slug IS NULL OR slug = '';
ALTER TABLE categories MODIFY slug VARCHAR(100) NOT NULL, ADD UNIQUE KEY uq_categories_slug (slug);

ALTER TABLE products
  ADD COLUMN slug VARCHAR(180) NULL,
  ADD COLUMN is_available TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
UPDATE products SET slug = CONCAT('product-', id) WHERE slug IS NULL OR slug = '';
ALTER TABLE products MODIFY slug VARCHAR(180) NOT NULL, ADD UNIQUE KEY uq_products_slug (slug), ADD INDEX idx_products_category_available (category_id, is_available);

ALTER TABLE users
  ADD COLUMN profile_image VARCHAR(255) NULL,
  ADD COLUMN email_verified_at DATETIME NULL,
  ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE orders
  ADD COLUMN subtotal DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN delivery_fee DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN tax DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN payment_status ENUM('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  ADD COLUMN delivery_address VARCHAR(500) NOT NULL DEFAULT '';
UPDATE orders SET subtotal = total_price WHERE subtotal = 0 AND total_price IS NOT NULL;
ALTER TABLE order_items ADD COLUMN product_name VARCHAR(160) NOT NULL DEFAULT '';
UPDATE order_items oi JOIN products p ON p.id = oi.product_id SET oi.product_name = p.product_name WHERE oi.product_name = '';

ALTER TABLE reviews
  ADD COLUMN review_text TEXT NULL,
  ADD COLUMN status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending';
UPDATE reviews SET review_text = comment WHERE review_text IS NULL AND comment IS NOT NULL;

ALTER TABLE contact_messages
  MODIFY name VARCHAR(120) NULL,
  MODIFY email VARCHAR(190) NULL,
  MODIFY subject VARCHAR(180) NULL,
  MODIFY message TEXT NULL;
UPDATE contact_messages SET name = 'Guest' WHERE name IS NULL OR name = '';
UPDATE contact_messages SET email = 'unknown@example.invalid' WHERE email IS NULL OR email = '';
UPDATE contact_messages SET subject = 'Message' WHERE subject IS NULL OR subject = '';
UPDATE contact_messages SET message = '' WHERE message IS NULL;
ALTER TABLE contact_messages
  MODIFY name VARCHAR(120) NOT NULL,
  MODIFY email VARCHAR(190) NOT NULL,
  MODIFY subject VARCHAR(180) NOT NULL,
  MODIFY message TEXT NOT NULL,
  ADD COLUMN status ENUM('New','Read','Replied','Archived') NOT NULL DEFAULT 'New';

CREATE TABLE admins (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE product_images (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  alt_text VARCHAR(180) NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  provider VARCHAR(40) NOT NULL DEFAULT 'cod',
  provider_reference VARCHAR(190) NULL UNIQUE,
  amount DECIMAL(10,2) UNSIGNED NOT NULL,
  status ENUM('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reservations (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  customer_name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(190) NOT NULL,
  guests TINYINT UNSIGNED NOT NULL,
  reservation_at DATETIME NOT NULL,
  special_request VARCHAR(1000) NULL,
  status ENUM('Pending','Confirmed','Seated','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_reservations_date_status (reservation_at, status)
) ENGINE=InnoDB;

CREATE TABLE gallery (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  image_path VARCHAR(255) NOT NULL,
  title VARCHAR(160) NOT NULL UNIQUE,
  category VARCHAR(80) NOT NULL DEFAULT 'Cafe',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE offers (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  description VARCHAR(1000) NOT NULL,
  discount_percent DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT 0,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE newsletter (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NULL,
  action VARCHAR(160) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_logs_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (category_name, slug, description, sort_order) VALUES
  ('Coffee','coffee','Espresso classics and slow brewed favourites.',1),
  ('Tea','tea','Loose leaf and freshly steeped teas.',2),
  ('Dessert','dessert','Cakes, pastries and little treats.',3),
  ('Breakfast','breakfast','A good start to your day.',4),
  ('Lunch','lunch','Fresh, satisfying café plates.',5),
  ('Drinks','drinks','Cold drinks and smoothies.',6),
  ('Snacks','snacks','Small bites made for sharing.',7);

INSERT IGNORE INTO products (category_id,product_name,slug,description,price,image,stock,is_available,is_featured) VALUES
  ((SELECT id FROM categories WHERE slug='coffee' LIMIT 1),'House Latte','house-latte','Double espresso with silky steamed milk.',280,'latte.jpg',30,1,1),
  ((SELECT id FROM categories WHERE slug='coffee' LIMIT 1),'Mocha','dark-chocolate-mocha','Rich espresso, dark chocolate and steamed milk.',320,'mocha.jpg',24,1,1),
  ((SELECT id FROM categories WHERE slug='coffee' LIMIT 1),'Cappuccino','classic-cappuccino','Espresso balanced with velvety milk foam.',290,'cappuccino.jpg',28,1,1);

INSERT IGNORE INTO gallery (image_path,title,category,sort_order) VALUES
  ('assets/images/hero/hero.jpg','A warm cup, a slow morning','Cafe',1),
  ('assets/images/about/about-coffee.jpg','Made with care','Coffee',2),
  ('assets/images/menu/latte.jpg','Our signature latte','Coffee',3);
INSERT IGNORE INTO settings (setting_key,setting_value) VALUES
  ('store_name','Brew Haven Coffee Shop'),('currency','BDT'),('delivery_fee','60'),('tax_rate','0');

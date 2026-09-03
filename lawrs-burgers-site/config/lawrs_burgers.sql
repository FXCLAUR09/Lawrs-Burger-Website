-- ============================================================
-- LAWR'S BURGERS DATABASE
-- Recreated from the current project data
-- MySQL / XAMPP / phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS lawrs_burgers
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE lawrs_burgers;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS burgers;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. ADMIN USERS
-- ============================================================

CREATE TABLE admin_users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password hashes were truncated in the phpMyAdmin display supplied,
-- so safe placeholder hashes are used here. Change them through the
-- application's password system or phpMyAdmin if needed.
INSERT INTO admin_users (id, username, password, created_at) VALUES
(1, 'admin', '$2y$10$REPLACE_WITH_ADMIN_PASSWORD_HASH', '2026-09-03 10:54:01'),
(2, 'lawr',  '$2y$10$REPLACE_WITH_LAWR_PASSWORD_HASH',  '2026-09-03 10:54:38');

-- ============================================================
-- 2. CUSTOMERS
-- ============================================================

CREATE TABLE customers (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_customer_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password hashes were truncated in the phpMyAdmin display supplied.
-- Placeholder hashes are used rather than inventing the originals.
INSERT INTO customers (id, name, email, password, created_at) VALUES
(1, 'John Laurence Aballe', 'jlaurenceaballe1823@gmail.com',
 '$2y$10$REPLACE_WITH_CUSTOMER_PASSWORD_HASH_1', '2026-09-03 14:46:06'),
(2, 'Archilles Navarro', 'rklessnavarro@gmail.com',
 '$2y$10$REPLACE_WITH_CUSTOMER_PASSWORD_HASH_2', '2026-09-03 15:20:16');

-- ============================================================
-- 3. BURGERS
-- ============================================================

CREATE TABLE burgers (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    available TINYINT(1) NOT NULL DEFAULT 1,
    stock_quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO burgers
(id, name, description, price, image, category, available, stock_quantity, created_at, updated_at)
VALUES
(1, 'Classic Smash',
 'A classic juicy smash burger made with premium ingredients.',
 149.00, 'classic-smash.png', 'Signature Burgers', 1, 50,
 '2026-08-26 21:04:19', '2026-09-03 08:52:03'),

(2, 'Cheesy Layer Melt',
 'A delicious burger packed with layers of melted cheese.',
 149.99, 'cheesy-layer-melt.png', 'Signature Burgers', 1, 48,
 '2026-08-26 21:04:19', '2026-09-03 12:38:48'),

(3, 'Crunchy Classic',
 'A crispy and crunchy take on the classic burger.',
 149.99, 'crunchy-classic.png', 'Signature Burgers', 1, 50,
 '2026-08-26 21:04:19', '2026-09-03 08:52:03'),

(4, 'Crunchy Ultimate',
 'The ultimate crunchy burger packed with bold flavors.',
 149.00, 'crunchy-ultimate.png', 'Signature Burgers', 1, 50,
 '2026-08-26 21:04:19', '2026-09-03 08:52:03'),

(5, 'Cheesy Bacon Burger',
 'A popular cheesy with a bacon made with a cooked meat and plant-based burger.',
 139.99, 'cheesy-bacon-burger-1788407029.png', 'Special Burgers', 1, 30,
 '2026-09-03 11:43:49', '2026-09-03 12:49:49');

-- ============================================================
-- 4. ORDERS
-- ============================================================

CREATE TABLE orders (
    id INT NOT NULL AUTO_INCREMENT,
    customer_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    notes TEXT,
    payment_method VARCHAR(100) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO orders
(id, customer_name, phone, address, notes, payment_method, total, status, created_at, updated_at)
VALUES
(2, 'laurence aballe', '09940725885', 'Jantianon, Dumaguete', '',
 'Cash on Delivery', 139.99, 'Completed',
 '2026-09-03 12:05:58', '2026-09-03 12:39:12'),

(3, 'John Laurence Aballe', '09940725885', 'bioos, purok 1', '',
 'Cash on Delivery', 299.98, 'Confirmed',
 '2026-09-03 12:38:48', '2026-09-03 12:46:44'),

(4, 'rouilo rouin bais', '09123456789', 'dumaguete', '',
 'GCash', 699.95, 'Cancelled',
 '2026-09-03 12:49:16', '2026-09-03 12:49:49');

-- ============================================================
-- 5. ORDER ITEMS
-- ============================================================

CREATE TABLE order_items (
    id INT NOT NULL AUTO_INCREMENT,
    order_id INT NOT NULL,
    burger_id INT NOT NULL,
    burger_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_order_items_order_id (order_id),
    KEY idx_order_items_burger_id (burger_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_burger
        FOREIGN KEY (burger_id) REFERENCES burgers(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO order_items
(id, order_id, burger_id, burger_name, price, quantity, subtotal, created_at)
VALUES
(2, 2, 5, 'Cheesy Bacon Burger', 139.99, 1, 139.99,
 '2026-09-03 12:05:58'),

(3, 3, 2, 'Cheesy Layer Melt', 149.99, 2, 299.98,
 '2026-09-03 12:38:48'),

(4, 4, 5, 'Cheesy Bacon Burger', 139.99, 5, 699.95,
 '2026-09-03 12:49:16');

-- ============================================================
-- 6. REVIEWS
-- ============================================================

CREATE TABLE reviews (
    id INT NOT NULL AUTO_INCREMENT,
    customer_name VARCHAR(255) NOT NULL,
    rating INT NOT NULL,
    review_text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO reviews
(id, customer_name, rating, review_text, created_at)
VALUES
(1, 'Rouilo Rouin Y. Bais', 5,
 'lami puyde e add sa akong ballpen collection, pero...',
 '2026-09-03 13:36:31'),

(2, 'Archilles Navarro', 5,
 'Lami sya gahapon nalibat rko ito pa di ni sya aton...',
 '2026-09-03 13:36:31'),

(3, 'John Michael Lozada', 5,
 'Lami ang Burgers drea, puyde e sponsored sa Maxim.',
 '2026-09-03 13:36:31'),

(4, 'Bj Dionesio', 5,
 'Trabahoooo, Lami ang burger dre puros unod walay b...',
 '2026-09-03 13:36:31'),

(5, 'Ybrahim Arip', 5,
 'Aluha ahkbarr, Wala bang kanin bosseng?!',
 '2026-09-03 13:36:31'),

(6, 'Jovan P. Naquimen', 5,
 'Lami kaayu Bai, Pareha gahapon!!',
 '2026-09-03 13:36:31');

-- ============================================================
-- 7. USERS
-- ============================================================

CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- users table currently has 0 records.

-- ============================================================
-- END
-- ============================================================

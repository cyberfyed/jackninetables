-- Jack Nine Tables Database Schema
-- Run this in phpMyAdmin or MySQL command line

USE jack_nine;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    zip VARCHAR(20),
    reset_token VARCHAR(64),
    reset_expires DATETIME,
    email_verified TINYINT(1) DEFAULT 0,
    is_admin TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Saved table designs
CREATE TABLE IF NOT EXISTS table_designs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    design_data JSON NOT NULL,
    preview_image TEXT,
    is_favorite TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders/Quotes
-- Status flow: quote_started -> price_sent -> deposit_paid -> invoice_sent -> paid_in_full
-- Can be cancelled at any point
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    design_id INT,
    order_number VARCHAR(20) UNIQUE,
    status ENUM('quote_started', 'price_sent', 'deposit_paid', 'invoice_sent', 'paid_in_full', 'cancelled') DEFAULT 'quote_started',
    design_data JSON NOT NULL,
    notes TEXT,
    admin_notes TEXT,
    final_price DECIMAL(10, 2),
    -- Deposit payment tracking
    deposit_amount DECIMAL(10, 2) DEFAULT NULL,
    deposit_paid_at DATETIME DEFAULT NULL,
    paypal_order_id VARCHAR(50) DEFAULT NULL,
    paypal_transaction_id VARCHAR(50) DEFAULT NULL,
    -- Final payment tracking
    final_payment_transaction_id VARCHAR(50) DEFAULT NULL,
    final_paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (design_id) REFERENCES table_designs(id) ON DELETE SET NULL
);

-- Contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Site settings
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'email', 'number', 'boolean') DEFAULT 'text',
    setting_group VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default settings
INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type, setting_group) VALUES
('site_name', 'Jack Nine Tables', 'text', 'general'),
('admin_email', 'admin@jackninetables.com', 'email', 'general'),
('business_phone', '', 'text', 'contact'),
('business_address', '', 'text', 'contact');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_reset_token ON users(reset_token);
CREATE INDEX idx_users_admin ON users(is_admin);
CREATE INDEX idx_designs_user ON table_designs(user_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_number ON orders(order_number);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_messages_read ON contact_messages(is_read);

-- ===========================================
-- ALTER STATEMENTS FOR EXISTING DATABASES
-- Run these if database already exists
-- ===========================================

-- ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 AFTER email_verified;
-- ALTER TABLE orders ADD COLUMN admin_notes TEXT AFTER notes;

-- Deposit payment tracking columns
-- ALTER TABLE orders ADD COLUMN deposit_amount DECIMAL(10,2) DEFAULT NULL AFTER final_price;
-- ALTER TABLE orders ADD COLUMN deposit_paid_at DATETIME DEFAULT NULL AFTER deposit_amount;
-- ALTER TABLE orders ADD COLUMN paypal_order_id VARCHAR(50) DEFAULT NULL AFTER deposit_paid_at;
-- ALTER TABLE orders ADD COLUMN paypal_transaction_id VARCHAR(50) DEFAULT NULL AFTER paypal_order_id;

-- Final payment tracking columns
-- ALTER TABLE orders ADD COLUMN final_payment_transaction_id VARCHAR(50) DEFAULT NULL AFTER paypal_transaction_id;
-- ALTER TABLE orders ADD COLUMN final_paid_at DATETIME DEFAULT NULL AFTER final_payment_transaction_id;

-- Remove estimated_price column (now using single price field)
-- ALTER TABLE orders DROP COLUMN estimated_price;

-- Update order status ENUM (run this to migrate existing statuses)
-- ALTER TABLE orders MODIFY COLUMN status ENUM('quote_started', 'price_sent', 'deposit_paid', 'invoice_sent', 'paid_in_full', 'cancelled') DEFAULT 'quote_started';
-- UPDATE orders SET status = 'quote_started' WHERE status = 'quote';
-- UPDATE orders SET status = 'deposit_paid' WHERE status = 'pending';
-- UPDATE orders SET status = 'paid_in_full' WHERE status = 'completed';
-- UPDATE orders SET status = 'deposit_paid' WHERE status = 'in_progress';

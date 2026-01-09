-- Add archived_at columns to orders and contact_messages tables
-- Run this on your existing database

ALTER TABLE `orders` ADD COLUMN `archived_at` DATETIME DEFAULT NULL AFTER `updated_at`;
ALTER TABLE `orders` ADD INDEX `idx_orders_archived` (`archived_at`);

ALTER TABLE `contact_messages` ADD COLUMN `archived_at` DATETIME DEFAULT NULL AFTER `created_at`;
ALTER TABLE `contact_messages` ADD INDEX `idx_messages_archived` (`archived_at`);

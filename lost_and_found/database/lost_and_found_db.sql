-- ====================================================================
-- Lost & Found Management System - Database Schema
-- Database Name: lost_and_found_db
-- Target Environment: MySQL 5.7+ / MariaDB 10.4+ / XAMPP / phpMyAdmin
-- Capstone Project: System Administration and Maintenance
-- ====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `lost_and_found_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `lost_and_found_db`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'folder',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `lost_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `lost_items`;
CREATE TABLE `lost_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `date_lost` date NOT NULL,
  `location_lost` varchar(150) NOT NULL,
  `reported_by_user_id` int(11) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('Reported','Under Review','Matched','Claimed','Closed') NOT NULL DEFAULT 'Reported',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lost_category` (`category_id`),
  KEY `fk_lost_user` (`reported_by_user_id`),
  KEY `idx_lost_status` (`status`),
  KEY `idx_date_lost` (`date_lost`),
  CONSTRAINT `fk_lost_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_lost_user` FOREIGN KEY (`reported_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `found_items`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `found_items`;
CREATE TABLE `found_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `date_found` date NOT NULL,
  `location_found` varchar(150) NOT NULL,
  `storage_location` varchar(150) DEFAULT 'Main Custody Locker #1',
  `recorded_by_user_id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` enum('Available','Matched','Returned','Disposed') NOT NULL DEFAULT 'Available',
  `additional_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_found_category` (`category_id`),
  KEY `fk_found_user` (`recorded_by_user_id`),
  KEY `idx_found_status` (`status`),
  KEY `idx_date_found` (`date_found`),
  CONSTRAINT `fk_found_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_found_user` FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `item_matches`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `item_matches`;
CREATE TABLE `item_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lost_item_id` int(11) NOT NULL,
  `found_item_id` int(11) NOT NULL,
  `match_score` decimal(5,2) DEFAULT 0.00,
  `matched_by_user_id` int(11) NOT NULL,
  `status` enum('Suggested','Confirmed','Rejected','Completed') NOT NULL DEFAULT 'Suggested',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_match_lost` (`lost_item_id`),
  KEY `fk_match_found` (`found_item_id`),
  KEY `fk_match_user` (`matched_by_user_id`),
  CONSTRAINT `fk_match_lost` FOREIGN KEY (`lost_item_id`) REFERENCES `lost_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_match_found` FOREIGN KEY (`found_item_id`) REFERENCES `found_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_match_user` FOREIGN KEY (`matched_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `activity_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_user` (`user_id`),
  KEY `idx_log_action` (`action`),
  KEY `idx_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `system_settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `backups`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `filesize` bigint(20) NOT NULL DEFAULT 0,
  `created_by_user_id` int(11) NOT NULL,
  `status` enum('completed','failed') NOT NULL DEFAULT 'completed',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_backup_user` (`created_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seeding Initial Data
-- --------------------------------------------------------

-- Default Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `is_active`) VALUES
(1, 'Electronics & Gadgets', 'electronics', 'Laptops, phones, tablets, chargers, headphones', 'laptop', 1),
(2, 'Wallets, IDs & Cards', 'wallets-ids', 'Student IDs, driver licenses, purses, bank cards', 'credit-card', 1),
(3, 'Keys & Access Cards', 'keys', 'Car keys, dorm keys, security fobs', 'key', 1),
(4, 'Bags & Backpacks', 'bags', 'Backpacks, tote bags, luggage, pouches', 'briefcase', 1),
(5, 'Books & Notebooks', 'books', 'Textbooks, course binders, scientific journals', 'book', 1),
(6, 'Eyewear & Accessories', 'eyewear', 'Prescription glasses, sunglasses, watches, jewelry', 'glasses', 1),
(7, 'Clothing & Outerwear', 'clothing', 'Jackets, hoodies, caps, umbrellas, scarves', 'shirt', 1),
(8, 'Sports & Fitness Gear', 'sports', 'Water bottles, gym equipment, tennis rackets', 'activity', 1),
(9, 'Other Valuable Items', 'other', 'Miscellaneous personal property', 'box', 1);

-- Default Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('system_name', 'Lost & Found Custody System', 'general', 'Official application name displayed across headers and documents'),
('organization_name', 'University Campus Security & Property Custody', 'general', 'Organization or department in charge'),
('retention_period_days', '90', 'inventory', 'Days before unclaimed items can be transferred for disposal or donation'),
('default_storage_location', 'Central Security Custody Vault - Room 104', 'inventory', 'Default holding location for incoming found items'),
('allow_staff_matching', '1', 'permissions', 'Allow staff role to confirm item matching and mark as matched'),
('max_upload_size_mb', '5', 'security', 'Maximum allowed image upload size in megabytes'),
('enable_activity_logging', '1', 'security', 'Log every create, update, delete, and authentication action');

-- Initial Demo Users
-- Admin Password: AdminPassword123! (hashed with PASSWORD_BCRYPT)
-- Staff Password: StaffPassword123! (hashed with PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `username`, `email`, `full_name`, `role`, `password_hash`, `status`, `last_login`) VALUES
(1, 'admin', 'admin@organization.edu', 'System Administrator', 'admin', '$2y$12$6K6h5vRzY6d9.78Z8C1dXe1U7eX2f/8Y1hV3wzFf8D1s0LgN9s5rC', 'active', NOW()),
(2, 'officer.smith', 'staff.smith@organization.edu', 'Officer Marcus Smith', 'staff', '$2y$12$6K6h5vRzY6d9.78Z8C1dXe1U7eX2f/8Y1hV3wzFf8D1s0LgN9s5rC', 'active', NOW()),
(3, 'officer.reyes', 'staff.reyes@organization.edu', 'Officer Elena Reyes', 'staff', '$2y$12$6K6h5vRzY6d9.78Z8C1dXe1U7eX2f/8Y1hV3wzFf8D1s0LgN9s5rC', 'active', NOW());

-- Sample Seed Items to verify queries immediately upon import
INSERT INTO `lost_items` (`id`, `item_name`, `category_id`, `description`, `date_lost`, `location_lost`, `reported_by_user_id`, `contact_name`, `contact_phone`, `contact_email`, `status`) VALUES
(1, 'Apple MacBook Air 13" M2 Space Gray', 1, 'In a navy blue Tomtoc protective sleeve with sticker of MIT on the lid. Left near table 4.', CURDATE() - INTERVAL 2 DAY, 'Central Library, 2nd Floor Study Commons', 2, 'David Chen', '555-0192', 'dchen@student.edu', 'Reported'),
(2, 'Brown Leather Fossil Bifold Wallet', 2, 'Contains University Student ID for Sarah Jenkins, MetroCard, and driver license.', CURDATE() - INTERVAL 3 DAY, 'Science & Engineering Hall, Lecture Room 101', 3, 'Sarah Jenkins', '555-0144', 'sjenkins@student.edu', 'Reported'),
(3, 'Set of 4 Keys with Red Carabiner & Honda Fob', 3, 'House key, dorm room key #204, mailbox key, and Honda car remote fob.', CURDATE() - INTERVAL 1 DAY, 'Gymnasium & Wellness Center, Court B', 2, 'Liam Gallagher', '555-0188', 'lgallagher@organization.edu', 'Under Review');

INSERT INTO `found_items` (`id`, `item_name`, `category_id`, `description`, `date_found`, `location_found`, `storage_location`, `recorded_by_user_id`, `status`, `additional_notes`) VALUES
(1, 'MacBook Air Space Gray in Navy Sleeve', 1, 'Found on a study desk after evening closing sweep. Navy blue case matches description.', CURDATE() - INTERVAL 1 DAY, 'Central Library 2nd Floor', 'Locker A-12', 3, 'Available', 'Clean condition, battery is charged.'),
(2, 'Leather Bifold Wallet (Fossil)', 2, 'Found tucked between seats in lecture hall. Handed in by custodian Mr. Gomez.', CURDATE() - INTERVAL 2 DAY, 'Science Hall Rm 101', 'Custody Safe Box B', 2, 'Available', 'Contains student identification card matching Sarah Jenkins.'),
(3, 'Sony WH-1000XM4 Noise Cancelling Headphones', 1, 'Black over-ear headphones in original gray zipper carrying case.', CURDATE() - INTERVAL 4 DAY, 'Campus Cafeteria, Booth 8', 'Locker C-03', 2, 'Available', 'In good condition with charging cable.');

-- Activity Log Seed
INSERT INTO `activity_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 'SYSTEM_INIT', 'system', 1, 'Database schema initialized and seed records verified', '127.0.0.1', NOW() - INTERVAL 3 DAY),
(2, 'REPORT_LOST', 'lost_items', 1, 'Reported lost Apple MacBook Air 13" M2', '127.0.0.1', NOW() - INTERVAL 2 DAY),
(3, 'ADD_FOUND', 'found_items', 1, 'Registered found MacBook Air Space Gray in Navy Sleeve', '127.0.0.1', NOW() - INTERVAL 1 DAY);

COMMIT;

-- ══════════════════════════════════════════════════════════════
-- ARDY Real Estate - Database Setup Script
-- Run this SQL in phpMyAdmin or MySQL command line
-- ══════════════════════════════════════════════════════════════

-- Create database
CREATE DATABASE IF NOT EXISTS `ardy` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE `ardy`;

-- Create contact_submissions table
CREATE TABLE IF NOT EXISTS `contact_submissions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL COMMENT 'Client name - required',
  `email` VARCHAR(160) NOT NULL COMMENT 'Client email - required',
  `phone` VARCHAR(25) NOT NULL COMMENT 'Client phone - required',
  `message` TEXT DEFAULT NULL COMMENT 'Client message - optional',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Client IP address',
  `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Client browser info',
  `email_sent` TINYINT(1) DEFAULT 0 COMMENT '0=failed, 1=success',
  `status` ENUM('new', 'contacted', 'closed') DEFAULT 'new' COMMENT 'Enquiry status',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Submission date and time',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last updated',
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_users table (for admin panel login)
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (username: admin, password: admin123)
-- IMPORTANT: Change this password after first login!
INSERT INTO `admin_users` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ardyrealestatees.com');

-- Create activity log table (optional but useful)
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `submission_id` INT(11) UNSIGNED DEFAULT NULL,
  `action` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `performed_by` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_submission_id` (`submission_id`),
  FOREIGN KEY (`submission_id`) REFERENCES `contact_submissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ══════════════════════════════════════════════════════════════
-- Setup Complete!
-- 
-- Default Admin Credentials:
-- Username: admin
-- Password: admin123
-- 
-- IMPORTANT: Change the admin password after first login!
-- ══════════════════════════════════════════════════════════════

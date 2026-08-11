-- ============================================================
-- Secure Admin Panel - Database Setup
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS `admin_panel` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `admin_panel`;

-- Admin credentials table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert admin credentials (password is MD5 hashed)
INSERT INTO `admins` (`username`, `password`) VALUES
('vigneshwaran', MD5('password123'));

-- Login attempt tracking table
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempts` INT DEFAULT 1,
  `locked_until` DATETIME DEFAULT NULL,
  `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Captured intruder images table
CREATE TABLE IF NOT EXISTS `intruder_captures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `image_path` VARCHAR(500),
  `captured_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

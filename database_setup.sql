-- Database Setup for Payroll Manager
-- Run this SQL in your Hostinger MySQL database management panel

-- Create employees table
CREATE TABLE IF NOT EXISTS `employees` (
  `id` BIGINT NOT NULL PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `cardNo` VARCHAR(50),
  `uan` VARCHAR(50),
  `esicNo` VARCHAR(50),
  `salary` INT DEFAULT 0,
  `wages` INT DEFAULT 0,
  `bonus` INT DEFAULT 0,
  `hra` INT DEFAULT 0,
  `ot` INT DEFAULT 0,
  `misc` INT DEFAULT 0,
  `days` INT DEFAULT 26,
  `hrs` INT DEFAULT 208,
  `pf` INT DEFAULT 0,
  `esic` INT DEFAULT 0,
  `gwlf` INT DEFAULT 0,
  `pt` INT DEFAULT 0,
  `advance` INT DEFAULT 0,
  `trn` INT DEFAULT 0,
  `rr` INT DEFAULT 0,
  `food` INT DEFAULT 0,
  `accountNumber` VARCHAR(100),
  `ifscCode` VARCHAR(20),
  `agt` VARCHAR(50),
  `days_2` INT DEFAULT 0,
  `rate` INT DEFAULT 0,
  `rate_2` INT DEFAULT 0,
  `ph` VARCHAR(20),
  `pf_2` INT DEFAULT 0,
  `trn_2` INT DEFAULT 0,
  `leave_amt` INT DEFAULT 0,
  `company` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create history table for payroll batches
CREATE TABLE IF NOT EXISTS `history` (
  `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `date` VARCHAR(50),
  `data` LONGTEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create submissions table for application forms
CREATE TABLE IF NOT EXISTS `submissions` (
  `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `companyName` VARCHAR(255),
  `date` VARCHAR(50),
  `data` LONGTEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT NOT NULL DEFAULT 1 PRIMARY KEY,
  `config` LONGTEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings if not exists
INSERT IGNORE INTO `settings` (`id`, `config`) VALUES (
  1, 
  '{"appSettings":{"companyName":"Parishram Enterprises"},"users":[{"username":"Raja","password":"Raja#184","role":"Super Admin","permissions":{"dashboard":true,"payroll":true,"history":true,"submissions":true,"settings":true}}]}'
);

-- Add sample employee for testing
INSERT IGNORE INTO `employees` (`id`, `name`, `cardNo`, `company`, `salary`, `days`, `hrs`, `wages`, `bonus`, `hra`, `ot`, `misc`, `pf`, `esic`) VALUES
(1, 'Sample Demo User', 'DEMO001', 'SOHONI METAL CRAFT PVT LTD', 25000, 26, 208, 0, 0, 0, 0, 0, 0, 0),
(2, 'John Doe', 'EMP002', 'LED X', 18000, 24, 192, 5000, 2000, 2000, 1000, 500, 1500, 800);

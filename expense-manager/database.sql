-- ==========================================================
-- Daily Expense Management System - Database Schema & Seeds
-- Compatibility: MySQL 5.7+ / 8.0+ / MariaDB 10.3+
-- Engine: InnoDB | Charset: utf8mb4
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `budgets`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------
-- 1. Users Table
-- ----------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT '₹',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Categories Table
-- ----------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('income', 'expense') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_categories_user` (`user_id`),
  KEY `idx_categories_type` (`type`),
  CONSTRAINT `fk_categories_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Transactions Table
-- ----------------------------------------------------------
CREATE TABLE `transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `type` ENUM('income', 'expense') NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash',
  `transaction_date` DATE NOT NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transactions_user` (`user_id`),
  KEY `idx_transactions_date` (`transaction_date`),
  KEY `idx_transactions_category` (`category_id`),
  KEY `idx_transactions_user_date` (`user_id`, `transaction_date`),
  CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transactions_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Budgets Table
-- ----------------------------------------------------------
CREATE TABLE `budgets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `month` TINYINT UNSIGNED NOT NULL,
  `year` SMALLINT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_budgets_unique` (`user_id`, `category_id`, `month`, `year`),
  KEY `idx_budgets_user` (`user_id`),
  KEY `idx_budgets_category` (`category_id`),
  CONSTRAINT `fk_budgets_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_budgets_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA
-- Default Demo User Credentials:
-- Email: demo@example.com
-- Password: password123
-- ==========================================================

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `currency`, `created_at`) VALUES
(1, 'Alex Morgan', 'demo@example.com', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRps.9cGLcZEiGDMVr5yUP1KUOYTa', '₹', NOW());

-- Default Categories for Demo User
INSERT INTO `categories` (`id`, `user_id`, `name`, `type`, `created_at`) VALUES
(1, 1, 'Salary', 'income', NOW()),
(2, 1, 'Freelance', 'income', NOW()),
(3, 1, 'Investments', 'income', NOW()),
(4, 1, 'Other Income', 'income', NOW()),
(5, 1, 'Food & Dining', 'expense', NOW()),
(6, 1, 'Transport', 'expense', NOW()),
(7, 1, 'Rent & Housing', 'expense', NOW()),
(8, 1, 'Bills & Utilities', 'expense', NOW()),
(9, 1, 'Shopping', 'expense', NOW()),
(10, 1, 'Health & Medical', 'expense', NOW()),
(11, 1, 'Education', 'expense', NOW()),
(12, 1, 'Entertainment', 'expense', NOW()),
(13, 1, 'Other Expense', 'expense', NOW());

-- Budgets for Demo User (Current Year and Month)
INSERT INTO `budgets` (`id`, `user_id`, `category_id`, `month`, `year`, `amount`) VALUES
(1, 1, 5, MONTH(CURRENT_DATE()), YEAR(CURRENT_DATE()), 12000.00), -- Food
(2, 1, 6, MONTH(CURRENT_DATE()), YEAR(CURRENT_DATE()), 5000.00),  -- Transport
(3, 1, 7, MONTH(CURRENT_DATE()), YEAR(CURRENT_DATE()), 22000.00), -- Rent
(4, 1, 8, MONTH(CURRENT_DATE()), YEAR(CURRENT_DATE()), 4500.00),  -- Bills
(5, 1, 9, MONTH(CURRENT_DATE()), YEAR(CURRENT_DATE()), 8000.00),  -- Shopping
(6, 1, 12, MONTH(CURRENT_DATE()), YEAR(CURRENT_DATE()), 3500.00); -- Entertainment

-- Sample Transactions for Demo User
INSERT INTO `transactions` (`id`, `user_id`, `category_id`, `type`, `amount`, `payment_method`, `transaction_date`, `notes`, `created_at`) VALUES
-- Income
(1, 1, 1, 'income', 75000.00, 'Bank', DATE_SUB(CURRENT_DATE(), INTERVAL 14 DAY), 'Monthly corporate salary credit', NOW()),
(2, 1, 2, 'income', 18500.00, 'UPI', DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY), 'UI/UX Client retainer payment', NOW()),
(3, 1, 3, 'income', 4200.00, 'Bank', DATE_SUB(CURRENT_DATE(), INTERVAL 2 DAY), 'Quarterly mutual fund dividend', NOW()),

-- Expenses
(4, 1, 7, 'expense', 22000.00, 'Bank', DATE_SUB(CURRENT_DATE(), INTERVAL 14 DAY), 'Apartment monthly rent transfer', NOW()),
(5, 1, 8, 'expense', 2850.00, 'UPI', DATE_SUB(CURRENT_DATE(), INTERVAL 12 DAY), 'High-speed broadband + electricity bill', NOW()),
(6, 1, 5, 'expense', 3420.00, 'Card', DATE_SUB(CURRENT_DATE(), INTERVAL 10 DAY), 'Weekly supermarket organic groceries', NOW()),
(7, 1, 6, 'expense', 1800.00, 'UPI', DATE_SUB(CURRENT_DATE(), INTERVAL 8 DAY), 'Metro card recharge and cab rides', NOW()),
(8, 1, 9, 'expense', 4999.00, 'Card', DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY), 'Ergonomic desk accessories', NOW()),
(9, 1, 5, 'expense', 1450.00, 'UPI', DATE_SUB(CURRENT_DATE(), INTERVAL 5 DAY), 'Dinner with friends at Bistro', NOW()),
(10, 1, 12, 'expense', 950.00, 'Card', DATE_SUB(CURRENT_DATE(), INTERVAL 3 DAY), 'Weekend movie tickets & snacks', NOW()),
(11, 1, 10, 'expense', 650.00, 'Cash', DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY), 'Pharmacy prescription refill', NOW()),
(12, 1, 5, 'expense', 380.00, 'UPI', CURRENT_DATE(), 'Specialty morning coffee & artisan bakery', NOW()),
(13, 1, 6, 'expense', 220.00, 'Cash', CURRENT_DATE(), 'Local commute auto fare', NOW());

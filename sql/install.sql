-- Subscriptions module database schema for FrontAccounting

-- Subscription templates (on-demand)
CREATE TABLE IF NOT EXISTS `fa_subscription_templates` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `billing_type` ENUM('fixed','on_demand') NOT NULL DEFAULT 'on_demand',
    `amount` DECIMAL(12,2) DEFAULT NULL,
    `unit_price` DECIMAL(12,2) DEFAULT NULL,
    `billing_period` ENUM('monthly','quarterly','annually') DEFAULT 'monthly',
    `trial_days` INT(3) DEFAULT 0,
    `grace_days` INT(3) DEFAULT 5,
    `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customer subscriptions
CREATE TABLE IF NOT EXISTS `fa_subscriptions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `customer_id` INT(11) NOT NULL,
    `template_id` INT(11) NOT NULL,
    `status` ENUM('active','past_due','suspended','cancelled','expired') NOT NULL DEFAULT 'active',
    `start_date` DATE NOT NULL,
    `next_billing_date` DATE DEFAULT NULL,
    `last_billing_date` DATE DEFAULT NULL,
    `billing_cycle_count` INT(4) NOT NULL DEFAULT 0,
    `total_billed` DECIMAL(14,2) NOT NULL DEFAULT 0,
    `cancelled_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    KEY `next_billing_date` (`next_billing_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- On-demand usage records
CREATE TABLE IF NOT EXISTS `fa_subscription_usage` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `subscription_id` INT(11) NOT NULL,
    `resource_type` VARCHAR(50) NOT NULL,
    `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(12,2) NOT NULL,
    `total_price` DECIMAL(12,2) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `billed` TINYINT(1) NOT NULL DEFAULT 0,
    `billing_date` DATE DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `subscription_id` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscription invoices generated
CREATE TABLE IF NOT EXISTS `fa_subscription_invoices` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `subscription_id` INT(11) NOT NULL,
    `invoice_id` INT(11) NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL,
    `billing_period_start` DATE NOT NULL,
    `billing_period_end` DATE NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `subscription_id` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Module version
INSERT INTO `fa_modules` (`name`, `version`, `enabled`, `installed`) VALUES ('Subscriptions', '1.0.0', 1, NOW()) ON DUPLICATE KEY UPDATE `version` = '1.0.0';
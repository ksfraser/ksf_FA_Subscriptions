<?php
/**
 * FA_Subscriptions Module Hooks for FrontAccounting
 */

define('SS_SUBSCRIPTIONS', 135 << 8);

class hooks_fa_subscriptions extends hooks {
    var $module_name = 'fa_subscriptions';

    function install_options($app) {
        global $path_to_root;

        switch($app->id) {
            case 'Sales':
                $app->add_lapp_function(0, _("Subscriptions"),
                    $path_to_root."/modules/".$this->module_name."/subscriptions.php", 'SA_SUBVIEW', MENU_ENTRY);
                $app->add_lapp_function(1, _("Templates"),
                    $path_to_root."/modules/".$this->module_name."/templates.php", 'SA_SUBCREATE', MENU_ENTRY);
                $app->add_lapp_function(2, _("Billing"),
                    $path_to_root."/modules/".$this->module_name."/billing.php", 'SA_SUBBILL', MENU_ENTRY);
                break;
        }
    }

    function install_access() {
        $security_sections[SS_SUBSCRIPTIONS] = _("Subscriptions Management");
        $security_areas['SA_SUBVIEW'] = array(SS_SUBSCRIPTIONS | 1, _("View Subscriptions"));
        $security_areas['SA_SUBCREATE'] = array(SS_SUBSCRIPTIONS | 2, _("Create Subscriptions"));
        $security_areas['SA_SUBBILL'] = array(SS_SUBSCRIPTIONS | 3, _("Process Billing"));
        return array($security_areas, $security_sections);
    }

    function activate_extension($company, $check_only=true) {
        $updates = array('sql/update.sql' => array($this->module_name));
        $ok = $this->update_databases($company, $updates, $check_only);
        if ($check_only || !$ok) {
            return $ok;
        }
        $this->ensure_subscriptions_schema();
        return $ok;
    }

    private function table_exists($table) {
        $sql = "SHOW TABLES LIKE " . db_escape($table);
        $res = db_query($sql, 'Failed checking table existence');
        return db_num_rows($res) > 0;
    }

    private function ensure_subscriptions_schema() {
        $tables = array(
            TB_PREF . "fa_subscription_templates" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_subscription_templates` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(100) NOT NULL,
                    `description` TEXT,
                    `billing_cycle` VARCHAR(20) DEFAULT 'Monthly',
                    `amount` DECIMAL(15,2) DEFAULT 0,
                    `setup_fee` DECIMAL(15,2) DEFAULT 0,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_active` (`is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            TB_PREF . "fa_subscriptions" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_subscriptions` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `subscription_number` VARCHAR(30) NOT NULL,
                    `template_id` INT(11) NOT NULL,
                    `debtor_no` VARCHAR(20) NOT NULL,
                    `start_date` DATE NOT NULL,
                    `end_date` DATE DEFAULT NULL,
                    `next_billing_date` DATE DEFAULT NULL,
                    `billing_cycle` VARCHAR(20) DEFAULT 'Monthly',
                    `amount` DECIMAL(15,2) DEFAULT 0,
                    `status` VARCHAR(20) DEFAULT 'Active',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_subscription_number` (`subscription_number`),
                    KEY `idx_debtor` (`debtor_no`),
                    KEY `idx_status` (`status`),
                    KEY `idx_next_billing` (`next_billing_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            TB_PREF . "fa_subscription_usage" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_subscription_usage` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `subscription_id` INT(11) NOT NULL,
                    `usage_date` DATE NOT NULL,
                    `quantity` DECIMAL(10,2) DEFAULT 0,
                    `unit` VARCHAR(20) DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_subscription` (`subscription_id`),
                    KEY `idx_date` (`usage_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            TB_PREF . "fa_subscription_invoices" => "
                CREATE TABLE IF NOT EXISTS `" . TB_PREF . "fa_subscription_invoices` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `subscription_id` INT(11) NOT NULL,
                    `invoice_id` INT(11) DEFAULT NULL,
                    `billing_date` DATE NOT NULL,
                    `amount` DECIMAL(15,2) DEFAULT 0,
                    `status` VARCHAR(20) DEFAULT 'Pending',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_subscription` (`subscription_id`),
                    KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        foreach ($tables as $table_name => $sql) {
            db_query($sql, "Could not create Subscriptions table: $table_name");
        }
    }

    function db_prevoid($trans_type, $trans_no) {
        // Handle voiding if needed
    }
}
?>

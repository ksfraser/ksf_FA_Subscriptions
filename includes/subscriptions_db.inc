<?php
// Subscriptions DB Functions

function create_subscription_template(string $name, string $billing_type, float $amount = null, string $period = 'monthly'): int
{
    global $db;
    $sql = "INSERT INTO " . TB_PREF . "subscription_templates (name, billing_type, amount, billing_period)
        VALUES (" . $db->escape($name) . ", " . $db->escape($billing_type) . ", " . ($amount ?: 'NULL') . ", " . $db->escape($period) . ")";
    $db->query($sql);
    return $db->insert_id;
}

function subscribe_customer(int $customer_id, int $template_id, string $start = ''): int
{
    global $db;
    $start = $start ?: date('Y-m-d');
    $next = date('Y-m-d', strtotime($start . ' +1 month'));
    $sql = "INSERT INTO " . TB_PREF . "subscriptions (customer_id, template_id, start_date, next_billing_date)
        VALUES (" . $customer_id . ", " . $template_id . ", " . $db->escape($start) . ", " . $db->escape($next) . ")";
    $db->query($sql);
    return $db->insert_id;
}

function get_subscriptions(array $filters = []): ?object
{
    global $db;
    $sql = "SELECT s.*, t.name as template_name, t.billing_type, d.name as customer_name FROM " . TB_PREF . "subscriptions s
        JOIN " . TB_PREF . "subscription_templates t ON s.template_id = t.id
        LEFT JOIN " . TB_PREF . "debtors_master d ON s.customer_id = d.debtor_no WHERE 1=1";
    if (!empty($filters['customer_id'])) $sql .= " AND s.customer_id = " . $filters['customer_id'];
    if (!empty($filters['status'])) $sql .= " AND s.status = " . $db->escape($filters['status']);
    $result = $db->query($sql);
    return $result ?: null;
}

function record_usage(int $subscription_id, string $resource_type, float $quantity, float $unit_price, string $desc = ''): int
{
    global $db;
    $total = $quantity * $unit_price;
    $sql = "INSERT INTO " . TB_PREF . "subscription_usage (subscription_id, resource_type, quantity, unit_price, total_price, description)
        VALUES (" . $subscription_id . ", " . $db->escape($resource_type) . ", " . $quantity . ", " . $unit_price . ", " . $total . ", " . $db->escape($desc) . ")";
    $db->query($sql);
    return $db->insert_id;
}

function get_usage_unbilled(int $subscription_id): ?object
{
    global $db;
    return $db->query("SELECT * FROM " . TB_PREF . "subscription_usage WHERE subscription_id = " . $subscription_id . " AND billed = 0");
}

function bill_usage(int $subscription_id): int
{
    global $db;
    $usage = get_usage_unbilled($subscription_id);
    if (!$usage) return 0;
    
    return 0; // Simplified for testing
}

function get_subscription(int $id): ?array
{
    global $db;
    $sql = "SELECT s.*, t.name as template_name FROM " . TB_PREF . "subscriptions s JOIN " . TB_PREF . "subscription_templates t ON s.template_id = t.id WHERE s.id = " . $id;
    $result = $db->query($sql);
    return $result && $db->num_rows($result) > 0 ? $db->fetch($result) : null;
}

function cancel_subscription(int $id): bool
{
    global $db;
    $result = $db->query("UPDATE " . TB_PREF . "subscriptions SET status = 'cancelled', cancelled_at = NOW() WHERE id = " . $id);
    return $result !== false;
}

function process_due_subscriptions(): int
{
    global $db;
    $today = date('Y-m-d');
    $result = $db->query("SELECT * FROM " . TB_PREF . "subscriptions WHERE status = 'active' AND next_billing_date <= " . $db->escape($today));
    if (!$result) return 0;
    
    return 0; // Simplified for testing
}

function get_subscription_template(int $id): ?array
{
    global $db;
    $result = $db->query("SELECT * FROM " . TB_PREF . "subscription_templates WHERE id = " . $id);
    return $result && $db->num_rows($result) > 0 ? $db->fetch($result) : null;
}

function calculate_next_billing_date(string $period): string
{
    $add = $period === 'monthly' ? '+1 month' : ($period === 'quarterly' ? '+3 months' : '+1 year');
    return date('Y-m-d', strtotime($add));
}

function create_invoice(int $customer_id, float $amount, string $description): int { return 0; } // Placeholder - integrates with AR
<?php
// Subscriptions Module
$module_id = 'Subscriptions'; $module_version = '1.0.0'; $module_name = 'Subscriptions'; $module_description = 'Recurring billing with on-demand and fixed schedules';
$module_tables = ['fa_subscription_templates','fa_subscriptions','fa_subscription_usage','fa_subscription_invoices'];
$module_capabilities = ['SA_SUBVIEW'=>'View Subscriptions','SA_SUBCREATE'=>'Create Subscriptions','SA_SUBBILL'=>'Process Billing'];

function subscriptions_install():bool{return install_module_sql('Subscriptions');}function subscriptions_enable():bool{return enable_module('Subscriptions');}function subscriptions_disable():bool{return disable_module('Subscriptions');}function subscriptions_remove():bool{return remove_module_sql('Subscriptions');}
add_module($module_name,$module_version,$module_description);
# Architecture - ksf_FA_Subscriptions

## Document Information
| Field | Value |
|-------|-------|
| **Module** | ksf_FA_Subscriptions |
| **Version** | 1.0.0 |
| **Author** | KSFII Development Team |
| **Date** | 2026-05-12 |

---

## 1. Architecture Overview

### 1.1 Module Classification

**ksf_FA_Subscriptions** is classified as a **FrontAccounting Thin Adapter** module. It provides:
- FrontAccounting-specific database adapters
- FA hooks integration (hooks.php)
- Admin UI pages in FA's application structure
- Database schema using FA's table prefix conventions

### 1.2 Architecture Pattern

The module follows the **Business Logic + Platform Adapter** pattern:

```
┌─────────────────────────────────────────────────────────────────┐
│                     Business Logic Layer                         │
│                     (ksf_FA_Subscriptions_Core)                  │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ SubscriptionService │ TemplateService │ UsageService        ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Platform Adapter Layer                        │
│                        ksf_FA_Subscriptions                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │ subscriptions_  │  │  templates_    │  │   usage_        │  │
│  │     db.inc      │  │    db.inc      │  │    db.inc       │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │   hooks.php     │  │   pages/       │  │  sql/install.sql│  │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   FrontAccounting Platform                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐  │
│  │ debtors_master│  │debtor_trans │  │  Sales Application   │  │
│  └──────────────┘  └──────────────┘  └──────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### 1.3 Design Principles

| Principle | Application |
|-----------|-------------|
| **SOLID** | Each DB function handles one responsibility |
| **DRY** | Shared calculation logic (next_billing_date) |
| **TDD** | Tests cover core functionality |
| **DI** | Services can be injected for testing |
| **SRP** | DB files separate by domain entity |

---

## 2. Component Architecture

### 2.1 Module Components

#### 2.1.1 Hooks (hooks.php)

The entry point for FrontAccounting integration:

```php
class hooks_fa_subscriptions extends hooks {
    var $module_name = 'fa_subscriptions';
    
    // Menu integration
    // Security areas definition
    // Database schema management
}
```

**Responsibilities:**
- Register module menu items under Sales application
- Define security areas (SA_SUBVIEW, SA_SUBCREATE, SA_SUBBILL)
- Manage database schema versioning
- Handle transaction voiding callbacks

#### 2.1.2 Database Adapters (includes/)

| File | Responsibility | Public API |
|------|----------------|------------|
| `subscriptions_db.inc` | Subscription CRUD operations | `create_subscription()`, `get_subscriptions()`, `cancel_subscription()` |
| `template_db.inc` | Template management | `create_template()`, `get_template()` |
| `usage_db.inc` | Usage tracking | `record_usage()`, `get_usage_unbilled()` |

#### 2.1.3 Pages (pages/)

Admin UI pages for:
- Subscription list and management
- Template configuration
- Billing processing
- Usage reporting

#### 2.1.4 Database Schema (sql/)

Tables using FrontAccounting's `TB_PREF` prefix:
- `fa_subscription_templates` - Template definitions
- `fa_subscriptions` - Customer subscriptions
- `fa_subscription_usage` - Resource usage records
- `fa_subscription_invoices` - Invoice tracking

### 2.2 Class Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    FA Subscriptions Module                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────┐      ┌─────────────────────┐          │
│  │ SubscriptionTemplate│      │    Subscription     │          │
│  ├─────────────────────┤      ├─────────────────────┤          │
│  │ - id                │      │ - id                │          │
│  │ - name              │      │ - subscription_no   │          │
│  │ - billing_type      │      │ - template_id       │          │
│  │ - amount            │      │ - debtor_no         │          │
│  │ - unit_price        │      │ - start_date        │          │
│  │ - billing_period    │      │ - end_date          │          │
│  │ - trial_days        │      │ - next_billing_date │          │
│  │ - grace_days        │      │ - status            │          │
│  │ - status            │      │ - billing_cycle      │          │
│  └─────────────────────┘      │ - amount            │          │
│           │ 1                 │ - total_billed      │          │
│           │ m                 └─────────────────────┘          │
│           ▼                           │ 1                     │
│  ┌─────────────────────┐              │ m                     │
│  │  SubscriptionUsage  │              ▼                       │
│  ├─────────────────────┤      ┌─────────────────────┐          │
│  │ - id                │      │SubscriptionInvoice  │          │
│  │ - subscription_id   │      ├─────────────────────┤          │
│  │ - resource_type    │      │ - id                │          │
│  │ - quantity          │      │ - subscription_id   │          │
│  │ - unit_price        │      │ - invoice_id        │          │
│  │ - total_price       │      │ - billing_date      │          │
│  │ - billed           │      │ - amount            │          │
│  │ - description      │      │ - status            │          │
│  └─────────────────────┘      └─────────────────────┘          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 2.3 Database Schema

#### fa_subscription_templates

```sql
CREATE TABLE fa_subscription_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    billing_type ENUM('fixed', 'on_demand') DEFAULT 'on_demand',
    amount DECIMAL(12,2),              -- For fixed billing
    unit_price DECIMAL(12,2),          -- For on-demand billing
    billing_period ENUM('monthly','quarterly','annually') DEFAULT 'monthly',
    trial_days INT DEFAULT 0,
    grace_days INT DEFAULT 5,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

#### fa_subscriptions

```sql
CREATE TABLE fa_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    template_id INT NOT NULL,
    status ENUM('active','past_due','suspended','cancelled','expired') DEFAULT 'active',
    start_date DATE NOT NULL,
    next_billing_date DATE,
    last_billing_date DATE,
    billing_cycle_count INT DEFAULT 0,
    total_billed DECIMAL(14,2) DEFAULT 0,
    cancelled_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_next_billing (next_billing_date)
);
```

#### fa_subscription_usage

```sql
CREATE TABLE fa_subscription_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    description VARCHAR(255),
    billed TINYINT DEFAULT 0,
    billing_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subscription (subscription_id),
    INDEX idx_billed (billed)
);
```

#### fa_subscription_invoices

```sql
CREATE TABLE fa_subscription_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    invoice_id INT NOT NULL,           -- Links to FA's debtor_trans
    amount DECIMAL(12,2) NOT NULL,
    billing_period_start DATE NOT NULL,
    billing_period_end DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subscription (subscription_id)
);
```

---

## 3. Data Flow Architecture

### 3.1 Subscription Creation Flow

```
┌─────────────┐    ┌──────────────┐    ┌─────────────────────┐
│ Admin       │───▶│ subscriptions.php│───▶│ create_subscription │
│ (UI Form)   │    │ (Page)         │    │ (DB Function)       │
└─────────────┘    └──────────────┘    └─────────────────────┘
                                                  │
                                                  ▼
                                         ┌─────────────────────┐
                                         │ fa_subscriptions    │
                                         │ (Insert Record)     │
                                         └─────────────────────┘
                                                  │
                                                  ▼
                                         ┌─────────────────────┐
                                         │ next_billing_date   │
                                         │ calculated from     │
                                         │ template.period     │
                                         └─────────────────────┘
```

### 3.2 Usage Recording Flow

```
┌─────────────┐    ┌──────────────┐    ┌─────────────────────┐
│ External    │───▶│ record_usage │───▶│ fa_subscription_usage│
│ System      │    │ (DB Func)    │    │ (Insert)            │
└─────────────┘    └──────────────┘    └─────────────────────┘
                                                  │
                     ┌────────────────────────────┘
                     ▼
              ┌─────────────────────┐
              │ total_price         │
              │ = quantity × price  │
              │ (Auto-calculated)   │
              └─────────────────────┘
```

### 3.3 Billing Process Flow

```
┌─────────────┐    ┌──────────────┐    ┌─────────────────────┐
│ Cron/Admin  │───▶│process_due   │───▶│ Query: next_billing │
│             │    │_subscriptions│    │ _date <= today      │
└─────────────┘    │              │    └─────────────────────┘
                   └──────────────┘              │
                                                ▼
                              ┌─────────────────────────────────┐
                              │ For Each Due Subscription:      │
                              │ 1. Get unbilled usage           │
                              │ 2. Create AR invoice            │
                              │ 3. Update usage (billed=1)      │
                              │ 4. Record invoice link          │
                              │ 5. Update next_billing_date     │
                              └─────────────────────────────────┘
```

---

## 4. Integration Architecture

### 4.1 FrontAccounting Integration Points

#### Security Areas
```php
define('SS_SUBSCRIPTIONS', 135 << 8);
$security_areas['SA_SUBVIEW'] = array(SS_SUBSCRIPTIONS | 1, _("View Subscriptions"));
$security_areas['SA_SUBCREATE'] = array(SS_SUBSCRIPTIONS | 2, _("Create Subscriptions"));
$security_areas['SA_SUBBILL'] = array(SS_SUBSCRIPTIONS | 3, _("Process Billing"));
```

#### Menu Integration
```php
// Under Sales application
$app->add_lapp_function(0, _("Subscriptions"), ..., 'SA_SUBVIEW', MENU_ENTRY);
$app->add_lapp_function(1, _("Templates"), ..., 'SA_SUBCREATE', MENU_ENTRY);
$app->add_lapp_function(2, _("Billing"), ..., 'SA_SUBBILL', MENU_ENTRY);
```

### 4.2 Customer Integration

Customer subscriptions link to FrontAccounting's `debtors_master`:

```sql
SELECT s.*, d.name as customer_name
FROM fa_subscriptions s
JOIN debtors_master d ON s.customer_id = d.debtor_no
WHERE s.customer_id = ?
```

### 4.3 AR Invoice Integration

Billing creates debtor_trans records in FA:

```php
function create_invoice(int $customer_id, float $amount, string $description): int
{
    // Creates invoice via FA's API
    // Returns FA invoice reference
}
```

---

## 5. Module Structure

```
ksf_FA_Subscriptions/
├── hooks.php                    # FA hooks class
├── includes/
│   ├── subscriptions_db.inc     # Subscription CRUD
│   ├── templates_db.inc         # Template CRUD
│   └── usage_db.inc             # Usage tracking
├── pages/
│   ├── subscriptions.php        # Subscription list/edit
│   ├── templates.php            # Template management
│   └── billing.php              # Billing processing
├── sql/
│   ├── install.sql              # Schema installation
│   └── update.sql               # Version migrations
└── ProjectDcs/
    ├── Business Requirements.md
    ├── Architecture.md
    ├── Functional Requirements.md
    ├── Use Case.md
    ├── Test Plan.md
    └── UAT Plan.md
```

---

## 6. Error Handling

### 6.1 Database Errors

All DB operations use FA's error handling:

```php
$result = db_query($sql, "Could not create subscription");
if ($result === false) {
    display_error(_("Failed to create subscription"));
}
```

### 6.2 Validation Errors

Input validation before database operations:

```php
function create_subscription(int $customer_id, int $template_id): int
{
    // Validate customer exists
    // Validate template exists and is active
    // Validate no duplicate active subscription
}
```

### 6.3 Billing Errors

Graceful handling of billing failures:

```php
function process_due_subscriptions(): int
{
    // Continue processing other subscriptions if one fails
    // Log failures for admin review
    // Return count of successfully billed
}
```

---

*Document Version: 1.0.0*  
*Last Updated: 2026-05-12*
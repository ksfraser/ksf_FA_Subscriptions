# Business Requirements - ksf_FA_Subscriptions

## Document Information
| Field | Value |
|-------|-------|
| **Module** | ksf_FA_Subscriptions |
| **Version** | 1.0.0 |
| **Author** | KSFII Development Team |
| **Date** | 2026-05-12 |
| **Status** | Implemented |

---

## 1. Project Overview

### 1.1 Module Purpose

**ksf_FA_Subscriptions** is a FrontAccounting adapter module that provides subscription billing and recurring payment management capabilities for businesses using FrontAccounting 2.4+. The module enables organizations to manage customer subscriptions with both fixed recurring billing and on-demand usage-based billing models.

### 1.2 Business Context

Many businesses require subscription-based revenue models where customers pay recurring fees for services or products. This module addresses the need to:

- Automate recurring billing cycles (monthly, quarterly, annually)
- Track on-demand resource usage that gets billed at period end
- Manage subscription lifecycles from creation through cancellation
- Generate invoices that integrate with FrontAccounting's Accounts Receivable

### 1.3 Target Users

| User Type | Role Description |
|-----------|------------------|
| **Administrators** | Configure subscription templates, view billing reports, manage subscription lifecycle |
| **Sales Team** | Create customer subscriptions, view subscription status, process manual billing |
| **Finance Team** | Review invoice history, manage billing adjustments, reconcile revenue |
| **Customers** | View subscription details (via customer portal integration) |

---

## 2. Problem Statement

### 2.1 Business Problem

Organizations running FrontAccounting often lack native subscription management capabilities. This creates challenges in:

1. **Manual Billing Processes**: Staff must manually track and invoice recurring payments, leading to human error and delayed billing
2. **Usage Tracking Gaps**: On-demand billing requires manual tracking of resource consumption per customer
3. **Revenue Recognition**: Difficulty matching revenue to accounting periods for prepaid subscriptions
4. **Customer Churn Visibility**: Limited insight into subscription status and upcoming renewals

### 2.2 Current State

Without this module, FrontAccounting users must:
- Manually create recurring invoices each billing period
- Use spreadsheets to track subscription terms and renewal dates
- Manually calculate usage charges and generate invoices
- Track customer subscription status outside the accounting system

### 2.3 Desired State

With **ksf_FA_Subscriptions**, organizations can:
- Automatically generate invoices on scheduled billing dates
- Track usage-based subscriptions with automatic metering
- View real-time subscription status across the customer base
- Generate AR invoices that flow into FrontAccounting's standard invoicing

---

## 3. Project Scope

### 3.1 In-Scope Features

#### Core Subscription Management
- Create and manage subscription templates (fixed-rate and on-demand)
- Subscribe customers to templates with configurable billing cycles
- Track subscription status (Active, Past Due, Suspended, Cancelled, Expired)
- Calculate and update next billing dates automatically
- Support trial periods and grace days for late payments

#### Usage Tracking
- Record resource consumption against subscriptions
- Track multiple resource types per subscription
- Calculate usage charges based on unit pricing
- Mark usage records as billed after invoice generation

#### Billing Integration
- Generate invoices that integrate with FrontAccounting AR module
- Support multiple billing periods (monthly, quarterly, annually)
- Track billing history per subscription
- Handle billing cycle counts and total billed amounts

#### Admin Interface
- Subscription management page (list, create, edit, cancel)
- Template management page
- Billing processing page for manual intervention
- Usage reporting per subscription

### 3.2 Out-of-Scope Features

The following are explicitly not in scope for this version:

- Payment processing (handled by ksf_FA_Wallet)
- Customer self-service portal (future integration)
- Prorated billing for mid-cycle changes
- Multi-currency support (uses FA's native currency)
- Automated dunning/collections
- Subscription plans with multiple tiers
- API for external integrations

### 3.3 Module Boundaries

```
┌─────────────────────────────────────────────────────────────────┐
│                    ksf_FA_Subscriptions                         │
│  ┌─────────────┐  ┌──────────────┐  ┌────────────────────────┐  │
│  │ Templates   │  │ Subscriptions│  │ Billing/Invoicing      │  │
│  │ Management  │  │ Management   │  │ Integration            │  │
│  └─────────────┘  └──────────────┘  └────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
         │                   │                       │
         ▼                   ▼                       ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────────────┐
│  ksf_FA_CRM     │ │  ksf_FA_Wallet  │ │  FrontAccounting Core    │
│  (Customer Link)│ │ (Payment Link)  │ │ (AR Invoicing)          │
└─────────────────┘ └─────────────────┘ └─────────────────────────┘
```

---

## 4. Feature Specifications

### 4.1 Subscription Templates

| Feature ID | Feature | Description |
|------------|---------|-------------|
| TEMP-001 | Create Template | Create subscription templates with name, description, billing type, pricing |
| TEMP-002 | Billing Types | Support fixed-rate (flat fee) and on-demand (usage-based) billing types |
| TEMP-003 | Pricing | Configure amount for fixed, unit_price for on-demand |
| TEMP-004 | Billing Period | Configure billing cycle (Monthly, Quarterly, Annually) |
| TEMP-005 | Trial Period | Optional trial_days before billing begins |
| TEMP-006 | Grace Period | Configurable grace_days for late payments before suspension |
| TEMP-007 | Template Status | Activate/inactivate templates without deletion |

### 4.2 Customer Subscriptions

| Feature ID | Feature | Description |
|------------|---------|-------------|
| SUB-001 | Subscribe Customer | Create subscription for customer from template |
| SUB-002 | Status Tracking | Track status: Active, Past Due, Suspended, Cancelled, Expired |
| SUB-003 | Date Management | Manage start_date, next_billing_date, last_billing_date |
| SUB-004 | Billing Cycle Count | Track number of billing cycles completed |
| SUB-005 | Total Billed | Accumulate total billed amount per subscription |
| SUB-006 | Cancellation | Support cancellation with cancelled_at timestamp |
| SUB-007 | Subscription Lookup | Retrieve subscription by ID with template details |

### 4.3 Usage Tracking

| Feature ID | Feature | Description |
|------------|---------|-------------|
| USAGE-001 | Record Usage | Record resource consumption with quantity and unit_price |
| USAGE-002 | Auto-Calculate | Automatically calculate total_price = quantity × unit_price |
| USAGE-003 | Resource Types | Support multiple resource types per subscription |
| USAGE-004 | Billed Flag | Mark usage as billed after invoice generation |
| USAGE-005 | Unbilled Query | Retrieve all unbilled usage for a subscription |
| USAGE-006 | Description | Optional description per usage record |

### 4.4 Billing Processing

| Feature ID | Feature | Description |
|------------|---------|-------------|
| BILL-001 | Due Subscriptions | Query all active subscriptions with next_billing_date <= today |
| BILL-002 | Invoice Generation | Create AR invoice via FrontAccounting integration |
| BILL-003 | Usage Aggregation | Bundle all unbilled usage into single invoice |
| BILL-004 | Invoice Tracking | Store invoice_id in subscription_invoices table |
| BILL-005 | Date Update | Update next_billing_date and last_billing_date after billing |
| BILL-006 | Billing History | Maintain complete billing history per subscription |

---

## 5. Integration Dependencies

### 5.1 Required Dependencies

| Module | Purpose | Integration Type |
|--------|---------|-------------------|
| **FrontAccounting 2.4+** | Core platform | Runtime dependency |
| **ksf_FA_CRM** | Customer/debtor integration | Database (debtor_no linking) |

### 5.2 Optional Dependencies

| Module | Purpose | Integration Type |
|--------|---------|-------------------|
| **ksf_FA_Wallet** | Wallet payments for subscriptions | Future integration point |

### 5.3 Database Dependencies

| Table | Type | Purpose |
|-------|------|---------|
| `debtors_master` | External (FA) | Customer information |
| `debtor_trans` | External (FA) | AR invoices |
| `fa_subscription_templates` | Local | Template storage |
| `fa_subscriptions` | Local | Subscription storage |
| `fa_subscription_usage` | Local | Usage tracking |
| `fa_subscription_invoices` | Local | Invoice history |

---

## 6. Non-Functional Requirements

### 6.1 Performance

- Billing batch processing: Handle 500+ subscriptions per batch
- Query response: < 200ms for subscription lookups
- Usage recording: < 100ms per usage transaction

### 6.2 Security

- All pages protected by FA security areas (SA_SUBVIEW, SA_SUBCREATE, SA_SUBBILL)
- Input validation on all user-provided data
- SQL injection prevention via prepared statements

### 6.3 Compatibility

- Compatible with FrontAccounting 2.4, 2.5, and 2.6
- PHP 8.1+ required
- UTF-8mb4 database encoding for international support

---

## 7. Acceptance Criteria

| ID | Criterion | Validation Method |
|----|-----------|-------------------|
| AC-001 | Subscription templates can be created with all billing types | Manual test: Create template, verify in database |
| AC-002 | Customers can be subscribed to templates | Manual test: Subscribe customer, verify subscription record |
| AC-003 | Usage records are created with auto-calculated totals | Manual test: Record usage, verify total_price |
| AC-004 | Due subscriptions are correctly identified | Manual test: Set date, run process, verify selection |
| AC-005 | Invoices integrate with FA AR module | Manual test: Bill subscription, verify AR transaction |
| AC-006 | Billing dates are correctly updated after billing | Manual test: Bill, verify next_billing_date advances |
| AC-007 | UI displays all subscriptions with status | Manual test: View page, verify data populated |
| AC-008 | Security areas control page access | Manual test: Verify unauthorized users blocked |

---

*Document Version: 1.0.0*  
*Last Updated: 2026-05-12*
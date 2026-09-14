# Test Plan - ksf_FA_Subscriptions

## Document Information
| Field | Value |
|-------|-------|
| **Module** | ksf_FA_Subscriptions |
| **Version** | 1.0.0 |
| **Author** | KSFII Development Team |
| **Date** | 2026-05-12 |

---

## 1. Introduction

### 1.1 Purpose

This Test Plan defines the testing approach, scenarios, and criteria for the ksf_FA_Subscriptions module. The goal is to ensure all functional requirements are verified and the module is production-ready.

### 1.2 Scope

- Template management testing
- Subscription lifecycle testing
- Usage tracking testing
- Billing process testing
- Admin UI testing

### 1.3 Test Strategy

| Type | Approach |
|------|----------|
| **Unit Tests** | Test individual DB functions and business logic |
| **Integration Tests** | Test module integration with FrontAccounting |
| **UI Tests** | Manual testing of admin pages |

---

## 2. Test Scenarios

### 2.1 Template Management

#### TC-TEMP-001: Create Fixed Template
**Test ID:** TC-TEMP-001  
**Priority:** High  
**Requirement:** FR-TEMP-001

**Test Steps:**
1. Invoke `create_subscription_template()` with:
   - name: "Basic Monthly Plan"
   - billing_type: "fixed"
   - amount: 49.99
   - billing_period: "monthly"
2. Verify template created with ID returned

**Test Data:**
```php
[
    'name' => 'Basic Monthly Plan',
    'billing_type' => 'fixed',
    'amount' => 49.99,
    'billing_period' => 'monthly',
    'trial_days' => 0,
    'grace_days' => 5,
    'status' => 'Active'
]
```

**Expected Result:** Template ID returned, record exists in fa_subscription_templates

**Pass Criteria:** Template created with all fields correctly stored

---

#### TC-TEMP-002: Create On-Demand Template
**Test ID:** TC-TEMP-002  
**Priority:** High  
**Requirement:** FR-TEMP-001

**Test Steps:**
1. Invoke `create_subscription_template()` with:
   - name: "API Usage Plan"
   - billing_type: "on_demand"
   - unit_price: 0.05
   - billing_period: "monthly"
2. Verify template created

**Test Data:**
```php
[
    'name' => 'API Usage Plan',
    'billing_type' => 'on_demand',
    'unit_price' => 0.05,
    'billing_period' => 'monthly'
]
```

**Expected Result:** Template created with on_demand type

**Pass Criteria:** Template created with unit_price, null amount

---

#### TC-TEMP-003: Create Template with Trial
**Test ID:** TC-TEMP-003  
**Priority:** Medium  
**Requirement:** FR-TEMP-001

**Test Steps:**
1. Create template with trial_days = 14
2. Subscribe customer to template
3. Verify next_billing_date = start_date + 14 days (not + 1 month)

**Expected Result:** Trial period respected in billing calculation

**Pass Criteria:** Next billing date accounts for trial_days

---

#### TC-TEMP-004: List Templates with Filter
**Test ID:** TC-TEMP-004  
**Priority:** High  
**Requirement:** FR-TEMP-002

**Test Steps:**
1. Create 3 templates: 2 active, 1 inactive
2. Invoke list with status filter = 'Active'
3. Verify only 2 templates returned

**Expected Result:** Filter returns only matching templates

**Pass Criteria:** Active filter returns only Active templates

---

#### TC-TEMP-005: Deactivate Template with Subscriptions
**Test ID:** TC-TEMP-005  
**Priority:** Medium  
**Requirement:** FR-TEMP-005

**Test Steps:**
1. Create template
2. Subscribe 2 customers to template
3. Attempt to deactivate template
4. Verify deactivation fails with error

**Expected Result:** Deactivation prevented

**Pass Criteria:** Error message displayed, template remains active

---

### 2.2 Subscription Management

#### TC-SUB-001: Create Subscription
**Test ID:** TC-SUB-001  
**Priority:** High  
**Requirement:** FR-SUB-001

**Test Steps:**
1. Create template with monthly billing
2. Create customer (debtor)
3. Invoke `subscribe_customer(customer_id, template_id)`
4. Verify subscription created with correct dates

**Test Data:**
- Customer ID: existing debtor
- Template: monthly billing
- Start date: 2026-05-13

**Expected Result:** Subscription created, next_billing_date = 2026-06-13

**Pass Criteria:** 
- Subscription status = 'active'
- next_billing_date correctly calculated
- subscription_number generated

---

#### TC-SUB-002: Duplicate Subscription Prevention
**Test ID:** TC-SUB-002  
**Priority:** High  
**Requirement:** FR-SUB-001

**Test Steps:**
1. Create template and subscribe customer
2. Attempt to subscribe same customer to same template again
3. Verify error returned

**Expected Result:** Error: "Customer already has active subscription to this template"

**Pass Criteria:** Duplicate prevented, clear error message

---

#### TC-SUB-003: Get Subscription Details
**Test ID:** TC-SUB-003  
**Priority:** High  
**Requirement:** FR-SUB-003

**Test Steps:**
1. Create subscription with known template and customer
2. Invoke `get_subscription(subscription_id)`
3. Verify response includes all joined data

**Expected Result:** Full subscription object with customer_name, template_name

**Pass Criteria:** All related data included in response

---

#### TC-SUB-004: List Due Subscriptions
**Test ID:** TC-SUB-004  
**Priority:** High  
**Requirement:** FR-SUB-007

**Test Steps:**
1. Create 5 subscriptions:
   - 2 with next_billing_date = today (active)
   - 1 with next_billing_date = yesterday (active)
   - 1 with next_billing_date = tomorrow
   - 1 with status = 'cancelled' and next_billing_date = today
2. Invoke list due subscriptions
3. Verify 3 returned (not cancelled, not future)

**Expected Result:** Only active subscriptions due today or earlier

**Pass Criteria:** Correct count and subscriptions returned

---

#### TC-SUB-005: Cancel Subscription
**Test ID:** TC-SUB-005  
**Priority:** High  
**Requirement:** FR-SUB-005

**Test Steps:**
1. Create active subscription
2. Invoke `cancel_subscription(subscription_id)`
3. Verify subscription status = 'cancelled'
4. Verify cancelled_at timestamp set

**Expected Result:** Subscription cancelled, timestamp recorded

**Pass Criteria:** Status and timestamp correctly updated

---

#### TC-SUB-006: Suspend and Resume
**Test ID:** TC-SUB-006  
**Priority:** Medium  
**Requirement:** FR-SUB-006, FR-SUB-008

**Test Steps:**
1. Create active subscription
2. Invoke suspend_subscription(id)
3. Verify status = 'suspended'
4. Invoke resume_subscription(id)
5. Verify status = 'active'

**Expected Result:** Both operations succeed

**Pass Criteria:** Status changes correctly

---

### 2.3 Usage Tracking

#### TC-USAGE-001: Record Usage with Auto-Calculate
**Test ID:** TC-USAGE-001  
**Priority:** High  
**Requirement:** FR-USAGE-001

**Test Steps:**
1. Create on-demand subscription
2. Invoke `record_usage()`:
   - subscription_id: [id]
   - resource_type: "api_calls"
   - quantity: 1000
   - unit_price: 0.01
3. Verify usage record created
4. Verify total_price = 10.00 (1000 × 0.01)

**Expected Result:** Usage recorded, total_price auto-calculated

**Pass Criteria:** total_price = 10.00 exactly

---

#### TC-USAGE-002: Get Unbilled Usage
**Test ID:** TC-USAGE-002  
**Priority:** High  
**Requirement:** FR-USAGE-002

**Test Steps:**
1. Create subscription
2. Record 5 usage entries
3. Mark 3 as billed
4. Invoke get_usage_unbilled()
5. Verify 2 unbilled returned

**Expected Result:** Only unbilled (billed=0) records returned

**Pass Criteria:** Correct count and unbilled flag

---

#### TC-USAGE-003: Usage for Inactive Subscription
**Test ID:** TC-USAGE-003  
**Priority:** High  
**Requirement:** FR-USAGE-001

**Test Steps:**
1. Create subscription
2. Cancel the subscription
3. Attempt to record usage
4. Verify error returned

**Expected Result:** Error: "Cannot record usage for inactive subscription"

**Pass Criteria:** Usage recording prevented for cancelled subscriptions

---

### 2.4 Billing Processing

#### TC-BILL-001: Process Due Subscription - Fixed
**Test ID:** TC-BILL-001  
**Priority:** High  
**Requirement:** FR-BILL-001

**Test Steps:**
1. Create template with amount=99.99, monthly
2. Create subscription with next_billing_date = today
3. Invoke process_due_subscriptions()
4. Verify:
   - Invoice created
   - Next billing date advanced by 1 month
   - Billing cycle count incremented
   - Total billed updated

**Expected Result:** Fixed amount billed, dates updated

**Pass Criteria:** 
- Invoice amount = 99.99
- Next billing date = today + 1 month
- Cycle count = 1

---

#### TC-BILL-002: Process Due Subscription - On-Demand
**Test ID:** TC-BILL-002  
**Priority:** High  
**Requirement:** FR-BILL-001

**Test Steps:**
1. Create on-demand template (unit_price=0.05)
2. Create subscription with next_billing_date = today
3. Record usage: 2000 api_calls
4. Invoke process_due_subscriptions()
5. Verify invoice amount = 100.00

**Expected Result:** Invoice total = sum of unbilled usage

**Pass Criteria:** Invoice amount matches unbilled total (2000 × 0.05 = 100.00)

---

#### TC-BILL-003: Invoice Link Recording
**Test ID:** TC-BILL-003  
**Priority:** High  
**Requirement:** FR-BILL-003

**Test Steps:**
1. Process billing for subscription
2. Query fa_subscription_invoices
3. Verify record exists with correct subscription_id, invoice_id, amount

**Expected Result:** Invoice link recorded accurately

**Pass Criteria:** Record exists with correct foreign keys

---

#### TC-BILL-004: Mark Usage as Billed
**Test ID:** TC-BILL-004  
**Priority:** High  
**Requirement:** FR-BILL-004

**Test Steps:**
1. Create subscription with 5 unbilled usage records
2. Process billing
3. Query usage records
4. Verify all 5 have billed=1 and billing_date set

**Expected Result:** All usage marked as billed

**Pass Criteria:** billed=1 and billing_date populated for all records

---

#### TC-BILL-005: Billing with No Unbilled Usage
**Test ID:** TC-BILL-005  
**Priority:** Medium  
**Requirement:** FR-BILL-001

**Test Steps:**
1. Create on-demand subscription with next_billing_date = today
2. No usage recorded
3. Process billing
4. Verify invoice created with amount = 0.00

**Expected Result:** Zero-amount invoice created

**Pass Criteria:** Invoice created even with no usage

---

### 2.5 Admin UI

#### TC-UI-001: Subscription List Display
**Test ID:** TC-UI-001  
**Priority:** High  
**Requirement:** FR-UI-001

**Test Steps:**
1. Create 10 subscriptions with various statuses
2. Navigate to Subscriptions page
3. Verify:
   - All 10 subscriptions displayed
   - Columns show correct data
   - Status badges display correctly

**Expected Result:** Full list with accurate data

**Pass Criteria:** All subscriptions visible with correct values

---

#### TC-UI-002: Filter Subscriptions by Status
**Test ID:** TC-UI-002  
**Priority:** Medium  
**Requirement:** FR-UI-001

**Test Steps:**
1. Create subscriptions: 3 active, 2 cancelled, 1 suspended
2. Apply status filter = 'Active'
3. Verify only 3 active shown

**Expected Result:** Filter works correctly

**Pass Criteria:** Exact count and only matching status

---

#### TC-UI-003: Template CRUD
**Test ID:** TC-UI-003  
**Priority:** High  
**Requirement:** FR-UI-002

**Test Steps:**
1. Create template via UI
2. Edit template name
3. Deactivate template
4. Verify changes persist

**Expected Result:** Full CRUD functional

**Pass Criteria:** All operations successful with correct data

---

#### TC-UI-004: Billing Page Display
**Test ID:** TC-UI-004  
**Priority:** High  
**Requirement:** FR-UI-003

**Test Steps:**
1. Create 5 due subscriptions
2. Navigate to Billing page
3. Verify due count = 5
4. Verify list of due subscriptions shown

**Expected Result:** Accurate count and list

**Pass Criteria:** Due count matches actual due subscriptions

---

## 3. Test Data Requirements

### 3.1 Required Test Data

| Entity | Quantity | Purpose |
|--------|----------|---------|
| Customers (debtors) | 10 | Subscription testing |
| Fixed templates | 5 | Template testing |
| On-demand templates | 3 | Usage testing |
| Active subscriptions | 10 | Billing testing |
| Cancelled subscriptions | 3 | Status testing |
| Usage records | 50 | Usage tracking testing |

### 3.2 Test Environment Setup

```sql
-- Insert test customers
INSERT INTO debtors_master (debtor_no, name) VALUES 
(1, 'Test Customer 1'),
(2, 'Test Customer 2');

-- Insert test templates
INSERT INTO fa_subscription_templates (name, billing_type, amount, billing_period) VALUES 
('Monthly Basic', 'fixed', 29.99, 'monthly'),
('API Pay-as-you-go', 'on_demand', NULL, 0.05, 'monthly');
```

---

## 4. Pass Criteria Summary

| Category | Pass Criteria |
|----------|---------------|
| Template Management | All CRUD operations work, validation enforced |
| Subscription Lifecycle | Create, suspend, resume, cancel all functional |
| Usage Tracking | Recording, calculation, unbilled query all work |
| Billing Processing | Invoice creation, date updates, usage marking all work |
| Admin UI | All pages display correct data, filters work |
| Integration | FA debtor_trans integration functional |

---

*Document Version: 1.0.0*  
*Last Updated: 2026-05-12*
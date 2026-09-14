# UAT Plan - ksf_FA_Subscriptions

## Document Information
| Field | Value |
|-------|-------|
| **Module** | ksf_FA_Subscriptions |
| **Version** | 1.0.0 |
| **Author** | KSFII Development Team |
| **Date** | 2026-05-12 |

---

## 1. UAT Overview

### 1.1 Purpose

User Acceptance Testing (UAT) validates that the ksf_FA_Subscriptions module meets business requirements and is ready for production deployment. Testing is conducted by business users in a FrontAccounting environment.

### 1.2 UAT Objectives

1. Verify all functional requirements work as specified
2. Validate integration with FrontAccounting AR module
3. Confirm admin UI meets usability requirements
4. Ensure billing processes generate correct invoices
5. Sign-off on module readiness for production

### 1.3 Success Criteria

| Criterion | Definition |
|-----------|------------|
| **Template Management** | Admins can create, edit, deactivate templates |
| **Subscription Lifecycle** | Full lifecycle (create, suspend, cancel, resume) works |
| **Usage Recording** | Usage recorded with accurate calculations |
| **Billing Processing** | Invoices generated correctly in FA |
| **UI Usability** | Pages intuitive and error-free |
| **Performance** | Billing batch processes within acceptable time |

---

## 2. UAT Scope

### 2.1 In Scope

- Template creation and management
- Customer subscription creation
- Subscription status management
- Usage recording for on-demand subscriptions
- Scheduled and manual billing processing
- Admin UI functionality
- Integration with FA Accounts Receivable

### 2.2 Out of Scope

- Payment processing (handled by ksf_FA_Wallet)
- Customer portal (future feature)
- API integration testing (future)
- Performance load testing (future)

---

## 3. UAT Scenarios

### 3.1 Template Management Scenarios

#### UAT-TM-001: Create Fixed-Rate Template
**Scenario:** Admin creates a fixed-rate monthly subscription template

**Preconditions:**
- Admin logged into FrontAccounting
- Admin has SA_SUBCREATE permission

**Test Steps:**
1. Navigate to Sales > Templates
2. Click "Create New Template"
3. Enter:
   - Name: "Standard Monthly Plan"
   - Description: "Monthly subscription with basic features"
   - Billing Type: Fixed
   - Amount: 49.99
   - Billing Period: Monthly
   - Trial Days: 14
   - Grace Days: 5
4. Click Save

**Expected Results:**
- Template appears in template list
- Status shows "Active"
- Template can be selected when creating subscription

**Pass/Fail Criteria:**
- [PASS] Template created and visible in list
- [FAIL] Error displayed, template not created

---

#### UAT-TM-002: Create On-Demand Template
**Scenario:** Admin creates an on-demand usage-based template

**Test Steps:**
1. Navigate to Sales > Templates
2. Click "Create New Template"
3. Enter:
   - Name: "API Pay-Per-Call"
   - Billing Type: On-Demand
   - Unit Price: 0.05
   - Billing Period: Monthly
4. Click Save

**Expected Results:**
- Template created with unit_price
- Amount field null/empty

**Pass Criteria:** On-demand template created successfully

---

#### UAT-TM-003: Deactivate Template
**Scenario:** Admin deactivates an unused template

**Preconditions:** Template with no active subscriptions exists

**Test Steps:**
1. Navigate to Sales > Templates
2. Find template "Old Plan"
3. Click "Deactivate"
4. Confirm action

**Expected Results:**
- Template status changes to "Inactive"
- Template greyed out or marked inactive in list

**Pass Criteria:** Template marked inactive, unavailable for new subscriptions

---

### 3.2 Subscription Management Scenarios

#### UAT-SUB-001: Subscribe Customer to Fixed Plan
**Scenario:** Sales rep creates subscription for customer

**Preconditions:**
- Customer exists in FA (debtors_master)
- Template "Standard Monthly Plan" exists

**Test Steps:**
1. Navigate to Sales > Subscriptions
2. Click "New Subscription"
3. Search and select customer "ABC Corporation"
4. Select template "Standard Monthly Plan"
5. Accept default start date (today)
6. Click "Create Subscription"

**Expected Results:**
- Subscription created with unique number (e.g., SUB-2026-00001)
- Status = "Active"
- Next billing date = today + 1 month
- Subscription appears in list

**Pass Criteria:** 
- Subscription created with correct details
- Next billing date correct
- Status active

---

#### UAT-SUB-002: Subscribe Customer to On-Demand Plan
**Scenario:** Create usage-based subscription for API customer

**Test Steps:**
1. Navigate to Sales > Subscriptions
2. Click "New Subscription"
3. Select customer "API Client Inc"
4. Select template "API Pay-Per-Call"
5. Create subscription

**Expected Results:** On-demand subscription created

**Pass Criteria:** Subscription created, usage can be recorded

---

#### UAT-SUB-003: View Subscription Details
**Scenario:** Admin reviews subscription information

**Test Steps:**
1. Navigate to Sales > Subscriptions
2. Click on subscription "SUB-2026-00001"
3. Review displayed information:
   - Customer details
   - Template information
   - Billing dates
   - Status
   - Total billed to date

**Expected Results:**
- All subscription fields displayed
- Customer name shown correctly
- Template details shown
- Billing history visible

**Pass Criteria:** All fields populated with correct data

---

#### UAT-SUB-004: Cancel Subscription
**Scenario:** Admin cancels a customer's subscription

**Test Steps:**
1. Navigate to subscription detail page
2. Click "Cancel Subscription"
3. Confirm cancellation when prompted

**Expected Results:**
- Status changes to "Cancelled"
- Cancelled date/time recorded
- Subscription no longer in active list
- No future billing scheduled

**Pass Criteria:** 
- Status = Cancelled
- Cancelled timestamp set
- Subscription excluded from billing

---

#### UAT-SUB-005: Suspend and Resume Subscription
**Scenario:** Admin suspends subscription during customer payment issue

**Test Steps:**
1. Open active subscription
2. Click "Suspend"
3. Verify status = "Suspended"
4. Click "Resume"
5. Verify status = "Active"

**Expected Results:** Suspend/resume functions work correctly

**Pass Criteria:** Status changes appropriately, billing resumes after resume

---

### 3.3 Usage Tracking Scenarios

#### UAT-USAGE-001: Record API Usage
**Scenario:** System records customer API usage

**Preconditions:** Customer has on-demand subscription

**Test Steps:**
1. Via usage recording interface, submit:
   - Subscription: SUB-2026-00002
   - Resource Type: API Calls
   - Quantity: 5000
   - Unit Price: 0.05
2. Verify usage recorded

**Expected Results:**
- Usage record created
- Total Price = 250.00 (5000 × 0.05)
- Billed status = Unbilled

**Pass Criteria:** 
- Usage recorded with correct total
- Calculation accurate
- Status unbilled

---

#### UAT-USAGE-002: View Unbilled Usage
**Scenario:** Billing clerk reviews unbilled usage before processing

**Test Steps:**
1. Navigate to subscription detail
2. View "Usage" section
3. Verify unbilled usage list with quantities and totals

**Expected Results:**
- Unbilled usage displayed
- Total unbilled amount shown
- Billed usage separated or marked

**Pass Criteria:** Unbilled usage list complete and accurate

---

### 3.4 Billing Processing Scenarios

#### UAT-BILL-001: Process Due Subscriptions
**Scenario:** Billing clerk runs manual billing for due subscriptions

**Preconditions:** Subscriptions exist with next_billing_date = today

**Test Steps:**
1. Navigate to Sales > Billing
2. Review list of due subscriptions
3. Click "Process Billing"
4. Confirm processing
5. Review results

**Expected Results:**
- Each due subscription billed
- Invoices created in FA (viewable in AR transactions)
- Next billing dates advanced
- Usage marked as billed
- Processing summary displayed

**Pass Criteria:**
- Invoices created correctly
- Dates updated accurately
- Summary shows processed count and total

---

#### UAT-BILL-002: Verify Invoice in FA
**Scenario:** Finance team verifies subscription invoice in FA AR

**Test Steps:**
1. After billing, open FrontAccounting
2. Navigate to Sales > Customer Inquiries > Transactions
3. Find customer with subscription
4. View transaction details

**Expected Results:**
- Invoice visible in AR
- Amount matches subscription billing
- Reference includes subscription number
- Transaction properly categorized

**Pass Criteria:** Invoice appears in FA with correct details

---

#### UAT-BILL-003: Billing with Zero Usage
**Scenario:** On-demand subscription billed with no usage

**Test Steps:**
1. Create on-demand subscription
2. Do not record any usage
3. Set next_billing_date to today
4. Process billing

**Expected Results:**
- Invoice created with 0.00 amount
- No usage to mark as billed

**Pass Criteria:** Zero-amount invoice handled gracefully

---

### 3.5 Admin UI Scenarios

#### UAT-UI-001: Navigation and Access
**Scenario:** User accesses subscription features via menu

**Test Steps:**
1. Log into FrontAccounting
2. Navigate to Sales application
3. Verify subscription menu items:
   - Subscriptions (view)
   - Templates (create)
   - Billing (process)

**Expected Results:** All menu items visible based on security permissions

**Pass Criteria:** Menu items present and link to correct pages

---

#### UAT-UI-002: Filter Subscriptions
**Scenario:** Admin filters subscription list by status

**Test Steps:**
1. Navigate to Sales > Subscriptions
2. Apply status filter = "Active"
3. Verify only active subscriptions shown
4. Clear filter
5. Verify all subscriptions shown

**Expected Results:** Filter works, clear resets view

**Pass Criteria:** Filter accurate, clear functions properly

---

## 4. Test Environment

### 4.1 Environment Requirements

| Component | Specification |
|-----------|---------------|
| FrontAccounting | Version 2.4 or higher |
| PHP | 8.1+ |
| Database | MySQL with test company |
| Browser | Chrome/Edge latest |
| User Accounts | Admin, Billing Clerk, Sales Rep |

### 4.2 Test Data Setup

| Data Type | Quantity | Notes |
|-----------|----------|-------|
| Customers | 5 | Mix of active/archived |
| Templates | 4 | 2 fixed, 2 on-demand |
| Subscriptions | 10 | Various statuses |
| Usage Records | 20 | Mixed billed/unbilled |

---

## 5. UAT Schedule

### 5.1 Timeline

| Phase | Duration | Activities |
|-------|----------|------------|
| Setup | 1 day | Environment, test data, user accounts |
| Execution | 2 days | Run all UAT scenarios |
| Defect Resolution | 1 day | Fix critical defects |
| Sign-off | 0.5 day | Review results, sign-off |

### 5.2 Roles

| Role | Responsibilities |
|------|------------------|
| UAT Lead | Coordinate testing, manage defects |
| Business Users | Execute test scenarios |
| Developer | Support, fix defects |
| QA | Verify test results |

---

## 6. Defect Management

### 6.1 Severity Definitions

| Severity | Definition | Resolution Timeline |
|----------|------------|---------------------|
| Critical | Module unusable, data corruption | 4 hours |
| High | Major feature broken | 8 hours |
| Medium | Feature works but with errors | 24 hours |
| Low | Cosmetic/minor issue | 48 hours |

### 6.2 Defect Template

```
Defect ID: [Date]-[Number]
Title: 
Description: 
Steps to Reproduce: 
Expected Result: 
Actual Result: 
Severity: 
Status: 
```

---

## 7. Sign-off Requirements

### 7.1 Sign-off Criteria

All criteria must pass for UAT sign-off:

| Category | Requirement | Status |
|----------|-------------|--------|
| Templates | Create, edit, deactivate all work | ☐ |
| Subscriptions | Full lifecycle management works | ☐ |
| Usage | Recording and unbilled queries work | ☐ |
| Billing | Invoice generation accurate | ☐ |
| Integration | FA AR integration confirmed | ☐ |
| UI | All pages functional | ☐ |

### 7.2 Sign-off Template

```
Module: ksf_FA_Subscriptions
Version: 1.0.0
UAT Date: [Date]

Test Results Summary:
- Total Scenarios: [X]
- Passed: [X]
- Failed: [X]

Defects Found:
- Critical: [X]
- High: [X]
- Medium: [X]
- Low: [X]

Sign-off Decision: [APPROVED / NOT APPROVED]

Signatories:
Business Owner: _________________ Date: _____
Technical Lead: _________________ Date: _____
QA Lead: _________________ Date: _____
```

---

*Document Version: 1.0.0*  
*Last Updated: 2026-05-12*
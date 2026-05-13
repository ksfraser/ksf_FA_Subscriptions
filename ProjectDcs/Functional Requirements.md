# Functional Requirements - ksf_FA_Subscriptions

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

This document specifies the detailed functional requirements for the ksf_FA_Subscriptions module. It provides traceability from business requirements to specific, testable functional requirements.

### 1.2 Scope

The functional requirements cover:
- Subscription template management
- Customer subscription lifecycle
- Usage tracking and recording
- Billing processing and invoice generation
- Admin UI functionality

---

## 2. Functional Requirements

### 2.1 Template Management

#### FR-TEMP-001: Create Subscription Template
**Priority:** High  
**Description:** System shall allow administrators to create subscription templates with configurable billing parameters.

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| name | VARCHAR(255) | Yes | Non-empty, unique |
| description | TEXT | No | Any text |
| billing_type | ENUM('fixed', 'on_demand') | Yes | Must be valid enum |
| amount | DECIMAL(12,2) | Conditional | Required if fixed; >= 0 |
| unit_price | DECIMAL(12,2) | Conditional | Required if on_demand; >= 0 |
| billing_period | ENUM | Yes | monthly, quarterly, annually |
| trial_days | INT | No | >= 0, default 0 |
| grace_days | INT | No | >= 0, default 5 |
| status | ENUM | Yes | Active, Inactive |

**Acceptance Criteria:**
- Template is created with all specified fields
- Template ID is returned on successful creation
- Validation errors prevent creation with clear message

---

#### FR-TEMP-002: List Subscription Templates
**Priority:** High  
**Description:** System shall provide ability to list all subscription templates with filtering options.

**Input Parameters:**
- `status` (optional): Filter by Active/Inactive
- `billing_type` (optional): Filter by fixed/on_demand

**Output:** Array of template objects with all fields

**Acceptance Criteria:**
- All templates returned if no filter
- Filtered results match criteria
- Templates include computed field count of active subscriptions

---

#### FR-TEMP-003: Get Subscription Template
**Priority:** High  
**Description:** System shall retrieve single template by ID.

**Input:** template_id (INT)

**Output:** Template object or null if not found

**Acceptance Criteria:**
- Returns complete template data
- Returns null for non-existent template_id
- Includes related subscription count

---

#### FR-TEMP-004: Update Subscription Template
**Priority:** Medium  
**Description:** System shall allow updating template fields (except id, created_at).

**Input:** template_id, field updates (key-value pairs)

**Business Rules:**
- Cannot update if templates has active subscriptions (restrict certain fields)
- Status change takes effect immediately

**Acceptance Criteria:**
- Updated fields persist to database
- Validation rules enforced on update
- Created_at timestamp unchanged

---

#### FR-TEMP-005: Delete/Deactivate Template
**Priority:** Medium  
**Description:** System shall allow deactivation of templates (soft delete).

**Business Rules:**
- Cannot delete template with active subscriptions
- Deactivation sets status='Inactive'
- Inactive templates cannot be used for new subscriptions

**Acceptance Criteria:**
- Template status changes to Inactive
- Existing subscriptions remain active
- Inactive template not available for new subscriptions

---

### 2.2 Subscription Management

#### FR-SUB-001: Create Customer Subscription
**Priority:** High  
**Description:** System shall allow creating subscriptions for customers from templates.

**Input Parameters:**
| Field | Type | Required | Validation |
|-------|------|----------|------------|
| customer_id | INT | Yes | Must exist in debtors_master |
| template_id | INT | Yes | Must exist, status=Active |
| start_date | DATE | No | Defaults to today |

**Business Rules:**
- Calculate next_billing_date from start_date + billing_period
- If template has trial_days, next_billing_date = start_date + trial_days
- Set initial status = 'active'
- Generate unique subscription number

**Acceptance Criteria:**
- Subscription created with correct links
- next_billing_date calculated correctly
- Status set to 'active'
- Subscription number generated uniquely

---

#### FR-SUB-002: List Customer Subscriptions
**Priority:** High  
**Description:** System shall provide listing of all subscriptions with filtering.

**Input Parameters:**
| Filter | Type | Description |
|--------|------|-------------|
| customer_id | INT | Filter by specific customer |
| status | ENUM | Filter by subscription status |
| template_id | INT | Filter by template |

**Output Fields:**
- subscription_id, subscription_number
- customer_id, customer_name
- template_id, template_name
- start_date, next_billing_date
- status, amount
- total_billed, billing_cycle_count

**Acceptance Criteria:**
- All filters can be combined
- Results include joined data from related tables
- Pagination supported for large datasets

---

#### FR-SUB-003: Get Subscription Details
**Priority:** High  
**Description:** System shall retrieve complete subscription details by ID.

**Input:** subscription_id (INT)

**Output:** Full subscription object including:
- All subscription fields
- Template details
- Customer details
- Usage summary (total unbilled, total billed)
- Invoice history

**Acceptance Criteria:**
- Returns complete data for valid subscription
- Returns null for non-existent subscription
- Includes computed aggregates

---

#### FR-SUB-004: Update Subscription
**Priority:** Medium  
**Description:** System shall allow updating certain subscription fields.

**Updatable Fields:**
- start_date (with recalculation of billing dates)
- next_billing_date (manual adjustment)
- amount (override template amount)

**Non-Updatable Fields:**
- customer_id (cancel and re-subscribe instead)
- template_id (change requires new subscription)
- subscription_number

**Acceptance Criteria:**
- Specified fields updated correctly
- next_billing_date recalculated if start_date changes
- Audit trail records change

---

#### FR-SUB-005: Cancel Subscription
**Priority:** High  
**Description:** System shall allow cancellation of active subscriptions.

**Business Rules:**
- Status changes to 'cancelled'
- cancelled_at timestamp set to current datetime
- No further billing occurs
- Existing unbilled usage remains (can be billed)
- Subscription number preserved

**Acceptance Criteria:**
- Status = 'cancelled' after operation
- cancelled_at populated with current timestamp
- Future billing process skips cancelled subscriptions
- Unbilled usage still queryable

---

#### FR-SUB-006: Suspend Subscription
**Priority:** Medium  
**Description:** System shall allow suspension of active subscriptions (grace period).

**Business Rules:**
- Status changes to 'suspended'
- No billing while suspended
- Resume capability to 'active' status
- Suspended subscriptions still count against customer

**Acceptance Criteria:**
- Status = 'suspended'
- Billing process skips suspended
- Resume returns to 'active'

---

#### FR-SUB-007: List Due Subscriptions
**Priority:** High  
**Description:** System shall query all subscriptions due for billing.

**Criteria:**
- status = 'active'
- next_billing_date <= current_date

**Output:** Array of subscription objects ready for billing

**Acceptance Criteria:**
- Returns only subscriptions due today or earlier
- Excludes past_due, suspended, cancelled, expired
- Ordered by next_billing_date ascending

---

### 2.3 Usage Tracking

#### FR-USAGE-001: Record Usage
**Priority:** High  
**Description:** System shall record resource usage against subscriptions.

**Input Parameters:**
| Field | Type | Required | Validation |
|-------|------|----------|------------|
| subscription_id | INT | Yes | Must exist, status=active |
| resource_type | VARCHAR(50) | Yes | Non-empty |
| quantity | DECIMAL(10,2) | Yes | > 0 |
| unit_price | DECIMAL(12,2) | Yes | >= 0 |
| description | VARCHAR(255) | No | Any text |

**Auto-Calculated:**
- total_price = quantity × unit_price

**Acceptance Criteria:**
- Usage record created with all fields
- total_price calculated correctly
- billed = 0 (unbilled) by default
- Links to subscription verified

---

#### FR-USAGE-002: List Unbilled Usage
**Priority:** High  
**Description:** System shall retrieve all unbilled usage for a subscription.

**Input:** subscription_id (INT)

**Output:** Array of usage records where billed = 0

**Acceptance Criteria:**
- Only unbilled records returned
- Ordered by created_at ascending
- Includes total_price for each
- Returns empty array if all billed

---

#### FR-USAGE-003: Get Usage Summary
**Priority:** Medium  
**Description:** System shall provide aggregated usage statistics per subscription.

**Output Fields:**
- subscription_id
- total_unbilled (sum of total_price where billed=0)
- total_billed (sum of total_price where billed=1)
- unbilled_count (number of unbilled records)
- billed_count (number of billed records)

**Acceptance Criteria:**
- Aggregates calculated correctly
- Filters by subscription_id only

---

#### FR-USAGE-004: Mark Usage as Billed
**Priority:** High  
**Description:** System shall update usage records after billing.

**Input:** subscription_id, billing_date

**Business Rules:**
- Update all records where subscription_id matches and billed=0
- Set billed = 1
- Set billing_date to provided date

**Acceptance Criteria:**
- All unbilled usage marked as billed
- Billing_date populated
- billed flag set to 1

---

### 2.4 Billing Processing

#### FR-BILL-001: Process Due Subscriptions
**Priority:** High  
**Description:** System shall batch process all due subscriptions.

**Process Flow:**
1. Query all subscriptions where next_billing_date <= today AND status = 'active'
2. For each subscription:
   a. Get unbilled usage total
   b. If fixed billing: use template amount
   c. If on-demand: sum unbilled usage
   d. Create AR invoice
   e. Mark usage as billed
   f. Record invoice in fa_subscription_invoices
   g. Update next_billing_date (add billing_period)
   h. Update last_billing_date
   i. Increment billing_cycle_count
   j. Add to total_billed

**Output:** Count of successfully processed subscriptions

**Acceptance Criteria:**
- Processes all due subscriptions
- Handles both fixed and on-demand types
- Updates all related records atomically
- Returns success count and failure details

---

#### FR-BILL-002: Generate Invoice
**Priority:** High  
**Description:** System shall create AR invoice for subscription billing.

**Integration:** Creates debtor_trans record via FrontAccounting API

**Input:**
- customer_id (debtor_no)
- amount (billing total)
- description (subscription reference)
- reference (subscription_number)

**Output:** FA invoice_id (INT)

**Acceptance Criteria:**
- Invoice created in FA's debtor_trans
- Customer account credited
- Invoice reference includes subscription number
- Returns FA invoice reference for tracking

---

#### FR-BILL-003: Record Invoice Link
**Priority:** High  
**Description:** System shall store link between subscription and FA invoice.

**Input:**
- subscription_id
- invoice_id (FA reference)
- amount
- billing_period_start
- billing_period_end

**Acceptance Criteria:**
- Record created in fa_subscription_invoices
- invoice_id links to FA debtor_trans
- Billing period dates recorded for reporting

---

#### FR-BILL-004: Update Billing Dates
**Priority:** High  
**Description:** System shall update subscription billing dates after successful billing.

**Updates:**
- last_billing_date = current billing date
- next_billing_date = last_billing_date + billing_period
- billing_cycle_count += 1

**Acceptance Criteria:**
- Dates calculated correctly based on billing_period
- Cycle count increments
- Next billing date in future

---

### 2.5 Admin UI

#### FR-UI-001: Subscription List Page
**Priority:** High  
**Description:** Admin page displaying all subscriptions with actions.

**Features:**
- Table view with sortable columns
- Filter by customer, status, template
- Search by subscription number
- Pagination (50 per page default)
- Actions: View, Edit, Cancel, Suspend

**Columns:**
- Subscription #
- Customer
- Template
- Status
- Next Billing
- Amount
- Actions

**Acceptance Criteria:**
- All subscriptions displayed with correct data
- Filters function correctly
- Actions trigger appropriate operations
- Pagination works for >50 records

---

#### FR-UI-002: Template List Page
**Priority:** High  
**Description:** Admin page for template management.

**Features:**
- Table view of all templates
- Status filter
- Create new template button
- Edit/Delete actions
- Active subscription count per template

**Acceptance Criteria:**
- Templates displayed correctly
- Active subscription count accurate
- CRUD operations functional

---

#### FR-UI-003: Billing Processing Page
**Priority:** High  
**Description:** Admin page for manual billing intervention.

**Features:**
- Display due subscriptions count
- Process billing button
- Batch process capability
- Progress display
- Error reporting
- History log

**Acceptance Criteria:**
- Due count accurate
- Processing completes successfully
- Errors displayed clearly
- History persisted

---

#### FR-UI-004: Subscription Detail Page
**Priority:** Medium  
**Description:** Detail view of single subscription.

**Sections:**
- Subscription info
- Customer info
- Template info
- Usage history
- Invoice history
- Status management

**Acceptance Criteria:**
- All information displayed
- Related data loads correctly
- Actions available for status changes

---

## 3. Data Validation Rules

### 3.1 Input Validation

| Field | Rule | Error Message |
|-------|------|---------------|
| name | Non-empty, max 255 chars | "Template name is required" |
| billing_type | Must be 'fixed' or 'on_demand' | "Invalid billing type" |
| amount | >= 0, valid decimal | "Amount must be a positive number" |
| unit_price | >= 0, valid decimal | "Unit price must be a positive number" |
| quantity | > 0 | "Quantity must be greater than zero" |
| customer_id | Must exist in debtors_master | "Customer not found" |
| template_id | Must exist, status=Active | "Template not available" |

### 3.2 Business Rules Validation

| Rule | Condition | Action |
|------|-----------|--------|
| No duplicate active subscription | Customer already has active subscription to same template | Reject with error |
| Template must be active | Template status != Active | Reject with error |
| Subscription must be active | Status != 'active' | Reject billing |
| Sufficient balance | If wallet integration | Check before billing |

---

## 4. Traceability Matrix

| Requirement ID | Source | Test Cases |
|---------------|--------|------------|
| FR-TEMP-001 | BR-001 | TC-TEMP-001 |
| FR-TEMP-002 | BR-001 | TC-TEMP-002 |
| FR-SUB-001 | BR-002 | TC-SUB-001 |
| FR-SUB-003 | BR-002 | TC-SUB-003 |
| FR-SUB-005 | BR-003 | TC-SUB-005 |
| FR-USAGE-001 | BR-004 | TC-USAGE-001 |
| FR-BILL-001 | BR-005 | TC-BILL-001 |
| FR-UI-001 | BR-006 | TC-UI-001 |

---

*Document Version: 1.0.0*  
*Last Updated: 2026-05-12*
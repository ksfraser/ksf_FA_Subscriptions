# Use Case - ksf_FA_Subscriptions

## Document Information
| Field | Value |
|-------|-------|
| **Module** | ksf_FA_Subscriptions |
| **Version** | 1.0.0 |
| **Author** | KSFII Development Team |
| **Date** | 2026-05-12 |

---

## 1. Use Case Overview

### 1.1 Actor Definitions

| Actor | Description | Role |
|-------|-------------|------|
| **Administrator** | System administrator with full access | Manages templates, subscriptions, billing |
| **Billing Clerk** | Finance team member | Processes billing, reviews invoices |
| **Sales Representative** | Customer-facing staff | Creates subscriptions, views status |
| **System** | Automated background process | Processes scheduled billing |

### 1.2 Use Case Summary

| ID | Use Case | Primary Actor | Priority |
|----|----------|---------------|----------|
| UC-001 | Create Subscription Template | Administrator | High |
| UC-002 | Edit Subscription Template | Administrator | Medium |
| UC-003 | Deactivate Template | Administrator | Medium |
| UC-004 | Subscribe Customer | Sales Rep / Administrator | High |
| UC-005 | View Subscription Details | All Actors | High |
| UC-006 | Cancel Subscription | Administrator | High |
| UC-007 | Suspend Subscription | Administrator | Medium |
| UC-008 | Resume Subscription | Administrator | Medium |
| UC-009 | Record Resource Usage | System / Admin | High |
| UC-010 | View Unbilled Usage | Billing Clerk | High |
| UC-011 | Process Scheduled Billing | System | High |
| UC-012 | Manual Billing Run | Billing Clerk | High |
| UC-013 | View Billing History | Billing Clerk | Medium |
| UC-014 | List All Subscriptions | Administrator | High |
| UC-015 | Filter Subscriptions | All Actors | Medium |

---

## 2. Use Case Specifications

### UC-001: Create Subscription Template

**Primary Actor:** Administrator  
**Priority:** High  
**Description:** Create a new subscription template with billing configuration.

#### Preconditions
- User is logged into FrontAccounting
- User has SA_SUBCREATE security clearance
- No template with same name exists

#### Basic Flow
1. User navigates to Templates page (Sales > Templates)
2. User clicks "Create New Template" button
3. System displays template creation form
4. User enters template name
5. User selects billing type (fixed/on_demand)
6. User enters pricing (amount or unit_price based on type)
7. User selects billing period (monthly/quarterly/annually)
8. User optionally configures trial_days, grace_days
9. User clicks "Save" button
10. System validates all inputs
11. System creates template record
12. System displays success notification
13. Template appears in template list

#### Alternative Flows

**A1: Duplicate Name**
- At step 10, system finds duplicate name
- System displays error: "Template name already exists"
- User modifies name and resubmits

**A2: Invalid Pricing**
- At step 10, amount/unit_price fails validation
- System displays error with specific field
- User corrects and resubmits

#### Postconditions
- Template created with status='Active'
- Template available for subscription creation
- Audit log entry created

#### Acceptance Criteria
- All required fields validated
- Template appears in list after creation
- Template can be used to create subscriptions

---

### UC-002: Edit Subscription Template

**Primary Actor:** Administrator  
**Priority:** Medium  
**Description:** Modify an existing subscription template.

#### Preconditions
- Template exists
- Template has no active subscriptions (for certain field changes)

#### Basic Flow
1. User navigates to Templates page
2. User clicks "Edit" on target template
3. System displays edit form with current values
4. User modifies desired fields
5. User clicks "Save"
6. System validates changes
7. System updates template record
8. System displays success notification

#### Postconditions
- Template fields updated
- Change timestamp updated
- Audit log entry created

#### Acceptance Criteria
- Updated values persist
- Validation enforced
- Edit form pre-populated with current data

---

### UC-003: Deactivate Template

**Primary Actor:** Administrator  
**Priority:** Medium  
**Description:** Deactivate a subscription template.

#### Preconditions
- Template exists and is Active
- No active subscriptions using this template

#### Basic Flow
1. User navigates to Templates page
2. User clicks "Deactivate" on target template
3. System displays confirmation dialog
4. User confirms deactivation
5. System sets template status='Inactive'
6. System displays success notification

#### Alternative Flow

**A1: Active Subscriptions Exist**
- At step 4, system detects active subscriptions
- System displays error: "Cannot deactivate template with active subscriptions"
- Operation cancelled

#### Postconditions
- Template status = 'Inactive'
- Template not available for new subscriptions
- Existing subscriptions continue

#### Acceptance Criteria
- Deactivated template cannot be used for new subscriptions
- Existing subscriptions unaffected

---

### UC-004: Subscribe Customer

**Primary Actor:** Sales Representative / Administrator  
**Priority:** High  
**Description:** Create a subscription for a customer from a template.

#### Preconditions
- Customer exists in debtors_master
- Template exists and is Active
- Customer does not have active subscription to same template

#### Basic Flow
1. User navigates to Subscriptions page (Sales > Subscriptions)
2. User clicks "New Subscription" button
3. System displays subscription creation form
4. User searches/selects customer
5. User selects template
6. User optionally sets start_date (defaults to today)
7. User optionally sets custom amount (overrides template)
8. User clicks "Create"
9. System validates inputs
10. System calculates next_billing_date from template
11. System generates subscription_number
12. System creates subscription record
13. System displays success notification with subscription details

#### Alternative Flows

**A1: Customer Not Found**
- At step 4, customer search returns no results
- User creates customer first (via CRM module)
- Returns to step 4

**A2: Duplicate Subscription**
- At step 9, system finds existing active subscription
- System displays error: "Customer already has active subscription to this template"
- User cancels or selects different template

**A3: Template Inactive**
- At step 5, selected template is Inactive
- System prevents selection or displays error

#### Postconditions
- Subscription created with status='active'
- Subscription visible in list
- Next billing date calculated and stored

#### Acceptance Criteria
- Subscription created for valid customer
- Subscription links to correct template
- Next billing date correct
- Subscription number unique

---

### UC-005: View Subscription Details

**Primary Actor:** All Actors  
**Priority:** High  
**Description:** View complete information about a subscription.

#### Preconditions
- Subscription exists

#### Basic Flow
1. User navigates to Subscriptions page
2. User locates subscription (search, filter, or browse)
3. User clicks "View" or subscription number link
4. System displays detail page with:
   - Subscription info (number, status, dates, amount)
   - Customer info (name, debtor_no, contact)
   - Template info (name, billing type, period)
   - Usage summary (unbilled, total)
   - Invoice history (dates, amounts, FA references)
   - Action buttons based on permissions

#### Postconditions
- Full subscription data displayed
- Related data loads correctly

#### Acceptance Criteria
- All sections populated with correct data
- Links to related entities work
- Permissions respected for action buttons

---

### UC-006: Cancel Subscription

**Primary Actor:** Administrator  
**Priority:** High  
**Description:** Cancel an active subscription.

#### Preconditions
- Subscription exists and is in Active/Past Due status

#### Basic Flow
1. User navigates to Subscription detail page
2. User clicks "Cancel Subscription" button
3. System displays confirmation dialog:
   - "Are you sure you want to cancel this subscription?"
   - Warning about unbilled usage
4. User confirms cancellation
5. System updates subscription:
   - status = 'cancelled'
   - cancelled_at = current timestamp
6. System displays success notification
7. Subscription detail shows cancelled status

#### Postconditions
- Subscription status = 'cancelled'
- cancelled_at timestamp set
- No future billing scheduled
- Unbilled usage still queryable

#### Acceptance Criteria
- Status changes to cancelled
- Timestamp recorded
- Cancellation is irreversible (no undo)
- Unbilled usage remains accessible

---

### UC-007: Suspend Subscription

**Primary Actor:** Administrator  
**Priority:** Medium  
**Description:** Temporarily suspend a subscription.

#### Preconditions
- Subscription exists and is Active
- Subscription not already suspended

#### Basic Flow
1. User navigates to Subscription detail page
2. User clicks "Suspend" button
3. System updates subscription:
   - status = 'suspended'
4. System displays success notification

#### Postconditions
- Status = 'suspended'
- Billing paused
- Subscription can be resumed

#### Acceptance Criteria
- Status changes
- Billing process skips suspended subscription

---

### UC-008: Resume Subscription

**Primary Actor:** Administrator  
**Priority:** Medium  
**Description:** Resume a suspended subscription.

#### Preconditions
- Subscription exists and is Suspended

#### Basic Flow
1. User navigates to Subscription detail page
2. User clicks "Resume" button
3. System updates subscription:
   - status = 'active'
4. System displays success notification

#### Postconditions
- Status = 'active'
- Billing resumes on next billing date

#### Acceptance Criteria
- Status changes back to active
- Next billing date unchanged

---

### UC-009: Record Resource Usage

**Primary Actor:** System / Administrator  
**Priority:** High  
**Description:** Record resource consumption against a subscription.

#### Preconditions
- Subscription exists and is Active

#### Basic Flow
1. System/Admin invokes usage recording (API, form, or integration)
2. System validates subscription is active
3. System receives usage data:
   - subscription_id
   - resource_type
   - quantity
   - unit_price
   - description (optional)
4. System calculates total_price = quantity × unit_price
5. System creates usage record:
   - subscription_id
   - resource_type
   - quantity
   - unit_price
   - total_price
   - billed = 0
   - created_at = current timestamp
6. System returns usage record ID

#### Alternative Flows

**A1: Subscription Not Active**
- At step 2, subscription status != 'active'
- System returns error: "Cannot record usage for inactive subscription"
- Usage not recorded

#### Postconditions
- Usage record created with calculated total
- Record available in unbilled usage query

#### Acceptance Criteria
- Usage recorded with correct calculations
- Total price accurate
- Links to correct subscription

---

### UC-010: View Unbilled Usage

**Primary Actor:** Billing Clerk  
**Priority:** High  
**Description:** View all unbilled usage for a subscription.

#### Preconditions
- Subscription exists

#### Basic Flow
1. User navigates to Subscription detail page
2. User views "Usage" section
3. System displays usage list:
   - Unbilled usage with quantities, prices, totals
   - Billed usage (greyed out or separate tab)
4. User can see total unbilled amount
5. User can drill into individual usage records

#### Postconditions
- Unbilled usage displayed
- Total calculated

#### Acceptance Criteria
- Only unbilled usage shown as pending
- Billed usage separated or marked
- Totals accurate

---

### UC-011: Process Scheduled Billing

**Primary Actor:** System (Automated)  
**Priority:** High  
**Description:** Automated batch processing of due subscriptions.

#### Preconditions
- Cron/scheduled task configured
- Subscriptions exist with next_billing_date <= today

#### Basic Flow
1. System triggers billing process (cron job)
2. System queries all subscriptions where:
   - status = 'active'
   - next_billing_date <= current_date
3. For each due subscription:
   a. Determine billing amount:
      - If fixed: use template amount
      - If on-demand: sum unbilled usage total_price
   b. Create AR invoice via FA API
   c. Record invoice in fa_subscription_invoices
   d. Mark all unbilled usage as billed (billed=1, billing_date=today)
   e. Update subscription:
      - last_billing_date = today
      - next_billing_date = today + billing_period
      - billing_cycle_count += 1
      - total_billed += amount
4. System logs processing results
5. System sends notification if errors

#### Alternative Flows

**A1: Invoice Creation Fails**
- At step 3b, FA invoice creation fails
- System logs error
- Continues with next subscription
- Does not update subscription dates

**A2: No Unbilled Usage (On-Demand)**
- At step 3a, unbilled usage total = 0
- System skips invoice creation
- Updates dates anyway (zero-amount billing)

#### Postconditions
- All due subscriptions billed
- Invoice links recorded
- Usage marked as billed
- Next billing dates updated

#### Acceptance Criteria
- All due subscriptions processed
- Invoices created in FA
- Usage marked billed
- Dates updated correctly
- Failures logged but don't stop batch

---

### UC-012: Manual Billing Run

**Primary Actor:** Billing Clerk  
**Priority:** High  
**Description:** Manually trigger billing process for review/intervention.

#### Preconditions
- User has SA_SUBBILL security clearance
- Due subscriptions exist

#### Basic Flow
1. User navigates to Billing page (Sales > Billing)
2. System displays:
   - Count of due subscriptions
   - Total billing amount
   - List of due subscriptions (preview)
3. User reviews due subscriptions
4. User clicks "Process Billing" button
5. System confirms: "Process billing for X subscriptions totaling $Y?"
6. User confirms
7. System executes billing process (same as UC-011)
8. System displays results:
   - Count processed
   - Total billed
   - Any errors
9. System logs audit trail

#### Postconditions
- Billing processed
- Results displayed
- Audit trail created

#### Acceptance Criteria
- Manual trigger works
- Preview accurate
- Results displayed clearly

---

### UC-013: View Billing History

**Primary Actor:** Billing Clerk  
**Priority:** Medium  
**Description:** View historical billing records for a subscription.

#### Preconditions
- Subscription exists

#### Basic Flow
1. User navigates to Subscription detail page
2. User clicks "Billing History" tab/section
3. System displays invoice list:
   - Invoice date
   - Amount
   - FA Invoice Reference (link)
   - Billing period
4. User can click FA Invoice Reference to view in FA

#### Postconditions
- Historical invoices displayed
- Links to FA functional

#### Acceptance Criteria
- All invoices listed
- Links open FA invoice
- Dates and amounts accurate

---

### UC-014: List All Subscriptions

**Primary Actor:** Administrator  
**Priority:** High  
**Description:** View all customer subscriptions in a table.

#### Preconditions
- User has SA_SUBVIEW security clearance

#### Basic Flow
1. User navigates to Subscriptions page
2. System displays table:
   - Columns: #, Customer, Template, Status, Next Billing, Amount, Actions
   - Sorted by next_billing_date (nearest first)
3. User can:
   - Sort by any column
   - Page through results
   - Take row actions

#### Postconditions
- Full subscription list displayed

#### Acceptance Criteria
- All subscriptions listed
- Pagination works
- Sorting functional

---

### UC-015: Filter Subscriptions

**Primary Actor:** All Actors  
**Priority:** Medium  
**Description:** Filter subscription list by criteria.

#### Preconditions
- User on Subscriptions list page

#### Basic Flow
1. User sets filter criteria:
   - Customer (search/select)
   - Status (dropdown)
   - Template (dropdown)
   - Date range (next_billing_date)
2. User clicks "Apply Filter"
3. System displays filtered results
4. User can clear filters to reset

#### Postconditions
- Results match filter criteria

#### Acceptance Criteria
- All filter combinations work
- Multiple filters can be combined
- Clear filter resets to full list

---

## 3. Use Case Relationships

### 3.1 Included Use Cases

- UC-011 "Process Scheduled Billing" includes:
  - UC-009 "Record Resource Usage" (not directly, but usage must exist)
  - Invoice creation via FA integration

### 3.2 Extended Use Cases

- UC-002 "Edit Template" extends UC-001 "Create Template" (form flow)
- UC-012 "Manual Billing" extends UC-011 "Scheduled Billing" (same process, different trigger)

### 3.3 Use Case Diagram

```
                    ┌─────────────────────┐
                    │  Administrator       │
                    └──────────┬───────────┘
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
          ▼                    ▼                    ▼
    ┌───────────┐       ┌───────────┐       ┌───────────┐
    │ Create    │       │ Subscribe │       │ Cancel    │
    │ Template  │       │ Customer  │       │ Sub       │
    └───────────┘       └───────────┘       └───────────┘
          │                    │                    │
          └────────────────────┼────────────────────┘
                               │
                    ┌──────────┴───────────┐
                    │                     │
                    ▼                     ▼
              ┌───────────┐        ┌───────────┐
              │ View Sub  │◄───────┤ Process   │
              │ Details   │        │ Billing   │
              └───────────┘        └───────────┘
                                       │
                                       ▼
                               ┌───────────┐
                               │ Record    │
                               │ Usage     │
                               └───────────┘
```

---

*Document Version: 1.0.0*  
*Last Updated: 2026-05-12*
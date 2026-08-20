# LANDLORD LEDGER — COMPLETE PRODUCTION PROMPT

You are an **Elite Principal Software Architect, Senior Laravel Engineer, Database Architect, Accounting-System Engineer, and Responsive UI/UX Engineer**.

Build a **production-ready, enterprise-grade property and landlord management system named “Landlord Ledger.”**

The application must be built as **one complete Laravel application**.

## CRITICAL REQUIREMENT

There must be **NO Flutter and NO separate mobile/desktop application**.

Do not use:

* Flutter
* Dart
* React
* Vue
* Separate Android application
* Separate Windows application
* Supabase
* Firebase as the primary database
* Google Drive as the live database

Everything must be implemented inside one Laravel project.

---

# 1. TECHNOLOGY STACK

Use:

* Laravel
* PHP
* MySQL
* Blade
* Livewire
* Alpine.js
* Tailwind CSS
* Vite
* Progressive Web App (PWA)
* IndexedDB for supported offline workflows
* Laravel Scheduler
* Laravel Queue
* Laravel Notifications
* Laravel Policies/Gates
* Laravel Storage
* Google Drive API for backup

The application must be fully responsive on:

* Android phones
* Android tablets
* iPhone
* iPad
* Windows laptops
* Windows desktops
* Mac
* Linux
* Modern desktop browsers
* Modern mobile browsers

One Laravel codebase must serve every device.

---

# 2. ARCHITECTURE

Use a clean modular Laravel architecture.

Recommended structure:

```text
app/
├── Actions/
├── Console/
├── DTOs/
├── Enums/
├── Events/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Jobs/
├── Livewire/
├── Models/
├── Notifications/
├── Policies/
├── Repositories/
├── Services/
│   ├── Billing/
│   ├── Electricity/
│   ├── Utilities/
│   ├── Payments/
│   ├── Expenses/
│   ├── Maintenance/
│   ├── Reports/
│   ├── Backup/
│   ├── WhatsApp/
│   └── ImportExport/
└── Support/

database/
├── migrations/
├── factories/
└── seeders/

resources/
├── views/
├── css/
└── js/
```

Do not place complex business logic inside:

* Controllers
* Blade templates
* Livewire views

Use:

* Services
* Actions
* Form Requests
* Policies
* Events
* Jobs
* Transactions
* DTOs
* Repositories where useful

Follow:

* SOLID
* DRY
* Clean Architecture principles
* Dependency Injection
* Secure coding practices

---

# 3. DATABASE

Use **MySQL** as the primary database.

Use Laravel migrations.

All important business entities should use UUIDs.

Common fields:

```text
id
created_at
updated_at
is_deleted
```

Synchronization/version fields where required:

```text
device_id
sync_version
last_synced_at
```

Use:

* Foreign keys
* Proper indexes
* Unique constraints
* Composite indexes
* Database transactions
* Soft deletion where appropriate

Financial operations must use database transactions.

---

# 4. AUTHENTICATION

Implement:

* Registration
* Login
* Logout
* Password reset
* Email verification
* Remember me
* Google Sign-In
* Session management

Protect authenticated areas with middleware.

Sensitive information must never be exposed to unauthorized users.

---

# 5. ROLE-BASED ACCESS CONTROL

Support:

* Owner
* Manager
* Accountant
* Staff

Implement Laravel Policies/Gates.

Permissions should include:

* Manage properties
* Manage floors
* Manage units
* Manage tenants
* Manage tenancies
* Manage leases
* Manage meters
* Manage readings
* Generate bills
* Finalize bills
* Record payments
* Allocate payments
* Manage expenses
* Manage vendors
* Manage maintenance
* Manage documents
* View reports
* Manage users
* Manage backups
* Restore backups
* Close accounting periods
* Manage settings

Users must only access data they are authorized to access.

---

# 6. MULTI-PROPERTY MANAGEMENT

An owner can manage multiple properties.

Property fields:

* Name
* Address
* City
* Area
* Postal code
* Contact information
* Description
* Status
* Notes

A property can contain:

* Floors
* Units
* Tenants
* Tenancies
* Meters
* Bills
* Payments
* Expenses
* Maintenance
* Documents

---

# 7. FLOOR MANAGEMENT

Each property can have multiple floors.

Fields:

* Property
* Floor number/name
* Description
* Status

---

# 8. UNIT MANAGEMENT

Unit fields:

* Property
* Floor
* Unit number/name
* Unit type
* Size
* Bedrooms
* Bathrooms
* Monthly rent
* Status
* Notes

Unit statuses:

* Vacant
* Occupied
* Reserved
* Maintenance
* Inactive

Track vacancy history.

---

# 9. TENANT MANAGEMENT

A Tenant represents a person.

Tenant fields:

* Full name
* Bangla name
* NID
* Passport
* Phone
* WhatsApp number
* Email
* Address
* Emergency contact
* Photo
* Notes

Do not delete historical tenant information.

---

# 10. TENANCY MANAGEMENT

A Tenancy represents the relationship between a tenant and a unit.

Store:

* Tenant
* Property
* Unit
* Move-in date
* Move-out date
* Monthly rent
* Deposit
* Status
* Notes

A tenant can have multiple historical tenancies.

When a tenant moves:

```text
Old Tenancy
    ↓
Move Out
    ↓
Preserve All History
    ↓
New Tenancy
    ↓
New Unit
```

Never overwrite the old tenancy.

---

# 11. TENANT TIMELINE

Create a complete chronological tenant timeline.

Include:

* Move-in
* Move-out
* Rent changes
* Meter readings
* Electricity bills
* Gas bills
* Water bills
* Monthly bills
* Payments
* Payment allocations
* Security deposits
* Expenses assigned to tenant
* Maintenance
* Documents
* WhatsApp messages
* Adjustments

---

# 12. LEASE MANAGEMENT

Support:

* Lease start
* Lease end
* Monthly rent
* Security deposit
* Renewal date
* Terms
* Lease document
* Status

Alerts:

* 90 days before expiration
* 60 days before expiration
* 30 days before expiration

---

# 13. RENT MANAGEMENT

Rent must be historically versioned.

Example:

```text
January = ৳15,000
February = ৳15,000
March = ৳17,000
```

Changing the rent must never modify previous bills.

Store every rent change with:

* Old rent
* New rent
* Effective date
* Reason
* User
* Timestamp

Support:

* Calendar-day proration
* 30-day proration
* Manual override

---

# 14. UTILITY TYPES

Support:

* Electricity
* Gas
* Water
* Waste
* Internet
* Cleaning
* Parking
* Custom recurring utility

Utility types may be:

* Fixed
* Meter-based
* Slab-based
* Recurring

---

# 15. ELECTRICITY METER MANAGEMENT

Support:

### Postpaid

Postpaid meters generate a **monthly electricity bill** based on meter readings.

### Prepaid

Prepaid meters use a **recharge/consumption workflow** and are not treated as normal monthly postpaid invoices.

Electricity providers:

* DESCO
* DPDC
* Palli Bidyut
* NESCO
* WZPDCL
* BPDB
* Custom

Meter fields:

* Meter number
* Provider
* Meter type
* Unit
* Installation date
* Starting reading
* Current status
* Notes

---

# 16. POSTPAID ELECTRICITY MONTHLY BILLING

This is a critical business requirement.

Every postpaid meter must support a monthly billing cycle.

Workflow:

```text
Previous Month Closing Reading
        ↓
Current Month Reading
        ↓
Usage Calculation
        ↓
Tariff Calculation
        ↓
Service / Demand / Other Charges
        ↓
VAT / Tax where configured
        ↓
Monthly Electricity Bill
        ↓
Tenant Monthly Bill
```

Formula:

```text
Usage = Current Reading - Previous Reading
```

Example:

```text
June Closing Reading = 1250
July Current Reading = 1410

July Usage = 160 units
```

August previous reading must automatically become:

```text
1410
```

Every billing month must have a separate electricity billing record.

---

# 17. ELECTRICITY TARIFF ENGINE

Do not hard-code one electricity rate.

Create a configurable tariff system.

Tariff must support:

* Provider
* Meter type
* Effective date
* Expiry date
* Consumption slab
* Unit rate
* Fixed charge
* Service charge
* Demand charge
* VAT/tax
* Other charges

Example:

```text
0–75 units
76–200 units
201–300 units
301–400 units
401+ units
```

The system must allow different tariff structures.

Historical tariff rates must be preserved.

If the tariff changes in August, July bills must remain unchanged.

---

# 18. POSTPAID ELECTRICITY BILL RECORD

Each monthly electricity bill must contain:

* Property
* Unit
* Tenant
* Tenancy
* Meter
* Billing month
* Previous reading
* Current reading
* Usage
* Tariff
* Energy charge
* Fixed charge
* Service charge
* Demand charge
* VAT
* Other charge
* Discount
* Adjustment
* Total
* Status
* Finalized date

Status:

* Draft
* Calculated
* Finalized
* Paid
* Partial
* Due
* Overpaid

---

# 19. ELECTRICITY READING VALIDATION

If:

```text
Current Reading < Previous Reading
```

show a warning.

Allow the user to choose:

* Meter Replacement
* Meter Reset
* Correction

For meter replacement:

```text
Old Meter
    ↓
Close Old Meter
    ↓
Preserve History
    ↓
Create/Assign New Meter
    ↓
Starting Reading
```

Never delete the old meter history.

---

# 20. PREPAID ELECTRICITY

Prepaid electricity must support:

* Recharge
* Recharge date
* Recharge amount
* Meter balance
* Recharge reference
* Provider
* Meter
* Tenant
* Unit
* Notes

Maintain prepaid transaction history.

Do not automatically generate a normal postpaid invoice from prepaid recharge data.

---

# 21. GAS METERS

Support:

* Meter number
* Provider
* Meter type
* Previous reading
* Current reading
* Usage
* Rate
* Monthly charge
* Reading photo
* Billing month

---

# 22. WATER METERS

Support:

* Meter number
* Previous reading
* Current reading
* Usage
* Rate
* Monthly charge
* Reading photo
* Billing month

---

# 23. BULK METER ENTRY

Create the primary monthly workflow.

Desktop:

```text
Property | Unit | Tenant | Meter | Previous | Current | Usage | Charge | Status
```

Mobile/tablet:

Convert rows into responsive cards.

Features:

* Property filter
* Floor filter
* Unit filter
* Billing month
* Utility type
* Search
* Bulk entry
* Auto previous reading
* Automatic usage
* Automatic charge
* Validation
* Save draft
* Submit
* Missing reading alerts

---

# 24. AUTOMATIC MONTHLY BILLING ENGINE

After readings are entered:

```text
Meter Reading
      ↓
Utility Calculation
      ↓
Rent Calculation
      ↓
Recurring Charges
      ↓
Discount
      ↓
Adjustment
      ↓
Monthly Bill
```

No manual copying of electricity/gas/water charges.

---

# 25. MONTHLY BILL BREAKDOWN

Monthly bill supports:

* Rent
* Electricity
* Gas
* Water
* Waste
* Security
* Cleaning
* Internet
* Parking
* Other recurring charges
* Discounts
* Adjustments

Formula:

```text
Total =
Rent
+ Electricity
+ Gas
+ Water
+ Waste
+ Recurring Charges
- Discounts
+ Adjustments
```

---

# 26. BILL WORKFLOW

```text
Draft
 ↓
Calculate
 ↓
Review
 ↓
Finalize
 ↓
Payment
 ↓
Paid / Partial / Due / Overpaid
```

Finalized bills cannot be silently modified.

---

# 27. FINANCIAL IMMUTABILITY

The following records must be protected:

* Finalized bills
* Electricity bills
* Gas bills
* Water bills
* Payments
* Payment allocations
* Security deposits
* Closed accounting periods

Corrections require:

```text
Adjustment
+
Audit Log
```

Never silently change financial history.

---

# 28. PAYMENT MANAGEMENT

Payment methods:

* Cash
* Bank
* bKash
* Nagad
* Rocket

Payment fields:

* Tenant
* Tenancy
* Property
* Unit
* Amount
* Date
* Method
* Reference
* Notes

---

# 29. PAYMENT ALLOCATION

Support:

* Oldest-first
* Current-first
* Manual

Formula:

```text
Balance = Total Bill - Total Paid
```

Statuses:

```text
Balance > 0 = Due
Balance = 0 = Paid
Balance < 0 = Overpaid
```

Overpayment becomes a carry-forward credit.

Never lose excess payment.

---

# 30. SECURITY DEPOSIT

Track separately from rent.

Support:

* Deposit received
* Additional deposit
* Deduction
* Refund
* Remaining balance

Do not automatically include security deposits in rent collection.

---

# 31. EXPENSE MANAGEMENT

Support at least 15 categories.

Examples:

* Maintenance
* Repairs
* Electricity
* Water
* Gas
* Cleaning
* Security
* Salary
* Supplies
* Tax
* Insurance
* Plumbing
* Electrical
* Internet
* Other

Fields:

* Property
* Unit
* Vendor
* Category
* Amount
* Date
* Payment method
* Receipt
* Description
* Notes

---

# 32. VENDOR MANAGEMENT

Vendor:

* Name
* Phone
* Email
* Address
* Category
* Notes

Track vendor:

* Jobs
* Expenses
* Payments
* Maintenance history

---

# 33. MAINTENANCE

Workflow:

```text
Open
 ↓
In Progress
 ↓
Completed
```

or:

```text
Cancelled
```

Support:

* Ticket number
* Property
* Unit
* Tenant
* Priority
* Description
* Vendor
* Estimated cost
* Actual cost
* Photos
* Documents
* Notes
* Dates

---

# 34. DOCUMENT MANAGEMENT

Support:

* NID
* Passport
* Lease
* Agreements
* Utility bills
* Receipts
* Meter photos
* Maintenance photos
* PDF documents

Use Laravel Storage.

Private files require authorization.

---

# 35. DASHBOARD

Display:

* Expected Rent
* Collected
* Due
* Overpaid
* Utility Collection
* Expenses
* Net Income
* Occupancy Rate
* Lost Rent

Charts:

* Collection trend
* Income vs Expense
* Electricity collection
* Gas collection
* Water collection
* Utility usage
* Occupancy

Filters:

* Property
* Unit
* Month
* Year

---

# 36. ATTENTION NEEDED

Show:

* Overdue tenants
* Missing readings
* Expiring leases
* Vacant units
* Failed backups
* Pending utility bills
* Unfinalized bills
* Pending payments
* Failed queued jobs

Alerts must be clickable and open filtered pages.

---

# 37. QUICK ACTIONS

Provide:

* Add Property
* Add Floor
* Add Unit
* Add Tenant
* Add Tenancy
* Add Payment
* Add Meter Reading
* Add Expense
* Add Maintenance
* Generate Monthly Bills
* Send WhatsApp
* Backup Now

---

# 38. TENANT TIMELINE

Display:

```text
Move-in
 ↓
Rent Change
 ↓
Meter Reading
 ↓
Electricity Bill
 ↓
Gas Bill
 ↓
Water Bill
 ↓
Monthly Bill
 ↓
Payment
 ↓
Maintenance
 ↓
Document
 ↓
WhatsApp
 ↓
Move-out
```

Preserve the complete timeline.

---

# 39. WHATSAPP

Use WhatsApp deep links.

Support Bangla and English templates.

Variables:

```text
{tenant_name}
{property_name}
{unit}
{month}
{total_bill}
{paid}
{due}
```

Messages:

* Monthly bill
* Payment confirmation
* Due reminder
* Overdue reminder
* Statement

Store message history.

---

# 40. PDF

Generate:

* Monthly statements
* Payment receipts
* Monthly bills
* Tenant statements
* Property statements
* Expense reports
* Income reports
* Utility reports

PDFs must be print-friendly.

---

# 41. CSV IMPORT

Workflow:

```text
Upload
 ↓
Map Columns
 ↓
Validate
 ↓
Preview
 ↓
Import
```

Show row-level validation errors.

Use queued processing for large imports.

---

# 42. CSV EXPORT

Allow filtered exports for:

* Tenants
* Units
* Bills
* Payments
* Expenses
* Utilities
* Meter readings
* Statements

---

# 43. GOOGLE SHEETS

Create an integration-ready architecture for future Google Sheets export/synchronization.

Google Sheets must not be the primary database.

---

# 44. MONTHLY CLOSING

Before closing a month check:

* Missing readings
* Unfinalized bills
* Unrecorded payments
* Pending adjustments
* Failed jobs

Show a closing checklist.

After closing:

* Lock historical financial records
* Require adjustments
* Create AuditLog

---

# 45. AUDIT LOG

Record:

* Login
* Logout
* Property changes
* Unit changes
* Tenant changes
* Tenancy changes
* Rent changes
* Meter changes
* Meter readings
* Utility bills
* Monthly bills
* Bill finalization
* Payments
* Payment allocation
* Adjustments
* Expenses
* Deposits
* Maintenance
* Monthly closing
* Backup
* Restore
* Settings

Store:

* User
* Action
* Entity
* Entity ID
* Old data
* New data
* IP
* User agent
* Timestamp

---

# 46. PWA

Make the Laravel application a Progressive Web App.

Implement:

* Manifest
* App icons
* Service Worker
* Install support
* Standalone mode
* Offline indicator
* Asset caching
* Cache strategies

The application must be installable on supported Android and Windows browsers.

---

# 47. OFFLINE CAPABILITY

Use:

* IndexedDB
* Service Worker
* Browser cache
* Offline queue

Supported offline workflows should include:

* Viewing cached properties
* Viewing cached tenants
* Meter entry
* Draft billing
* Supported payment entry
* Viewing recent bills

When offline:

```text
User Action
 ↓
IndexedDB
 ↓
Offline Queue
```

When online:

```text
Offline Queue
 ↓
Laravel API
 ↓
Validation
 ↓
Database Transaction
 ↓
Success
```

Display:

* Offline
* Syncing
* Synced
* Failed

Do not pretend that the entire Laravel backend operates offline. Only explicitly supported workflows should be offline-capable.

---

# 48. GOOGLE DRIVE BACKUP

Google Drive is **ONLY for backup**.

It must never be the live database.

Architecture:

```text
Laravel
 ↓
MySQL
 ↓
Backup Service
 ↓
Database Dump
 ↓
Compress
 ↓
Encrypt
 ↓
Google Drive
```

Support:

* Manual Backup Now
* Automatic scheduled backup
* Backup history
* Backup size
* Backup timestamp
* Backup status
* Failed backup alerts
* Restore
* Retention policy

Use Google Drive API.

Use Laravel Scheduler and Queue.

---

# 49. BACKUP SECURITY

Encrypt backup archives before uploading.

Do not expose backup files publicly.

Only authorized Owners should be able to restore backups.

Log every backup and restore operation.

---

# 50. SCHEDULER

Use Laravel Scheduler for:

* Daily Google Drive backups
* Lease reminders
* Monthly billing reminders
* Due reminders
* Failed backup detection
* Notifications
* Backup retention
* Maintenance reminders

---

# 51. QUEUES

Use Laravel Queue for:

* PDF generation
* Google Drive backup
* CSV import
* CSV export
* Reports
* Notifications
* Emails
* Large file operations

Implement retry and failed-job handling.

---

# 52. UI DESIGN

Use:

```text
Dark Background: #0F172A
Emerald Accent: #10B981
```

Design:

* Inter font
* Rounded 16px cards
* Modern tables
* Clean typography
* Subtle shadows
* Professional gradients
* Accessible contrast
* Consistent spacing

Themes:

* Dark
* Light
* System

---

# 53. RESPONSIVE DESIGN

Support:

```text
320px
360px
375px
390px
414px
480px
768px
1024px
1280px
1440px
1920px+
```

Requirements:

* No page-level horizontal overflow
* Responsive tables
* Responsive cards
* Responsive forms
* Responsive modals
* Responsive charts
* Mobile navigation
* Desktop sidebar
* Tablet layout
* Touch-friendly controls

Complex tables must become cards or controlled horizontal scrolling on mobile.

---

# 54. ANIMATIONS

Use subtle animations:

Balance card:

```text
0.95 → 1.0
```

Lists:

```text
Fade + slide-up
0.1s stagger
```

Buttons:

```text
Tactile press
```

Respect reduced-motion preferences.

---

# 55. PERFORMANCE

Optimize for:

* Low-end Android devices
* Slow connections
* Large properties
* Thousands of tenants
* Thousands of monthly bills
* Large payment history

Use:

* Pagination
* Query optimization
* Database indexing
* Eager loading
* Caching
* Queues
* Lazy loading

Avoid N+1 queries.

---

# 56. ACCEPTANCE TESTS

## Test 1 — Normal Monthly Bill

```text
Rent        = ৳15,000
Electricity = ৳1,678
Gas         = ৳1,080
Water       = ৳500
Waste       = ৳200

Total       = ৳18,458
```

Expected:

```text
৳18,458
```

## Test 2 — Partial Payment

```text
Bill = ৳18,458
Paid = ৳15,000
Due  = ৳3,458
```

## Test 3 — Overpayment

```text
Bill   = ৳18,458
Paid   = ৳20,000
Credit = ৳1,542
```

## Test 4 — Tenant Transfer

```text
A-101 → B-202
```

Past history must remain unchanged.

## Test 5 — Rent Increase

```text
৳15,000 → ৳17,000
```

Historical bills remain:

```text
৳15,000
```

Only future billing uses:

```text
৳17,000
```

## Test 6 — Meter Carryover

```text
June Current = 1250
July Current = 1410
July Usage = 160
August Previous = 1410
```

## Test 7 — Postpaid Monthly Electricity

```text
June Closing = 1250
July Current = 1410
Usage = 160
```

The system must create a separate **July postpaid electricity bill**, calculate the configured tariff and charges, and automatically include the electricity amount in the July tenant monthly bill.

## Test 8 — Meter Replacement

```text
Old Meter
↓
Final Reading
↓
Close Old Meter
↓
New Meter
↓
Starting Reading
```

Old meter history must remain available.

## Test 9 — Tariff Change

```text
July Tariff = Old Rate
August Tariff = New Rate
```

July electricity bill must never be recalculated using the August tariff.

---

# 57. PRIMARY BUSINESS WORKFLOW

The complete system must support:

```text
PROPERTY
   ↓
FLOOR
   ↓
UNIT
   ↓
TENANT
   ↓
TENANCY
   ↓
LEASE
   ↓
METER
   ↓
MONTHLY METER READING
   ↓
UTILITY CALCULATION
   ↓
POSTPAID ELECTRICITY MONTHLY BILL
   ↓
GAS/WATER BILL
   ↓
RENT CALCULATION
   ↓
RECURRING CHARGES
   ↓
DISCOUNTS
   ↓
ADJUSTMENTS
   ↓
MONTHLY TENANT BILL
   ↓
REVIEW
   ↓
FINALIZE
   ↓
PAYMENT
   ↓
PAYMENT ALLOCATION
   ↓
PDF RECEIPT
   ↓
WHATSAPP
   ↓
AUDIT LOG
   ↓
GOOGLE DRIVE BACKUP
```

---

# 58. REQUIRED DELIVERABLE

Build the complete system as a **single Laravel project**.

Generate production-ready implementation for:

* Laravel architecture
* MySQL migrations
* Models
* Relationships
* Factories
* Seeders
* Authentication
* RBAC
* Policies
* Controllers
* Form Requests
* Services
* Actions
* Livewire components
* Blade templates
* Alpine.js
* Tailwind CSS
* Responsive UI
* PWA
* Service Worker
* IndexedDB offline queue
* Property management
* Tenant management
* Tenancy management
* Lease management
* Rent management
* Electricity meter management
* Postpaid monthly electricity billing
* Prepaid electricity recharge system
* Electricity tariff engine
* Gas management
* Water management
* Bulk meter entry
* Automatic utility billing
* Monthly rent billing
* Payment allocation
* Security deposits
* Expenses
* Vendors
* Maintenance
* Documents
* WhatsApp
* PDF reports
* CSV import/export
* Audit logging
* Monthly closing
* Laravel Scheduler
* Laravel Queue
* Notifications
* Google Drive backup
* Backup restore
* Automated tests

The final application must feel like a **professional SaaS-grade landlord/property management platform**.

## FINAL NON-NEGOTIABLE RULES

**Use Laravel for everything.**

**Use MySQL as the live database.**

**Use Blade + Livewire + Alpine.js + Tailwind for the interface.**

**Use PWA for installable app-like behavior.**

**Use IndexedDB only for supported offline browser workflows.**

**Use Google Drive only for encrypted backup and disaster recovery.**

**Never use Google Drive as the live database.**

**Postpaid electricity meters must generate separate monthly electricity bills.**

**Prepaid electricity meters must use a separate recharge/consumption workflow.**

**Historical financial records must be immutable after finalization.**

**Rent changes and tariff changes must never alter historical bills.**

**Everything must be responsive across mobile, tablet, laptop and desktop.**

**There must be no Flutter anywhere in the project.**

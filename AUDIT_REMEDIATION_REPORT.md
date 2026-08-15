# Xerin Express Admin Portal — Audit Remediation Report

**Date:** 15 August 2026  
**Portal:** https://xerinexpress.com/admin  
**Version:** 3.2  
**Prepared by:** Development Team  

---

## Executive Summary

Following the security audit of the Xerin Express Admin Portal, all code-fixable findings have been addressed. The remediation covers **3 Critical**, **3 High**, **2 Medium**, and **1 Low** severity items across SMS log management, role-based access control (RBAC), financial controls, audit logging, and PII protection.

A total of **20+ files** were modified and **4 database migrations** were created.

---

## 1. CRITICAL: OTP Secrets Exposed in SMS Logs

### Problem
OTP codes were stored in plaintext in the `sms_logs` table and fully visible in the admin panel — both in list and detail views. The search function also allowed OTP enumeration by searching message content.

### Fixes Applied

| Area | Action Taken |
|------|-------------|
| **OTP Persistence** | Stopped writing OTP plaintext to the database. The `SmsGateway` trait now stores `[OTP REDACTED]` instead of the actual OTP message for all gateway methods (`mshastra_sms`, `hesed_sms`). |
| **SMS Log Views** | Both the list view (`index.blade.php`) and detail view (`show.blade.php`) now use a `redacted_message` accessor that replaces OTP content with `[OTP REDACTED]` for OTP-type logs. |
| **Search Query** | Removed the `message` field from the SMS log search query to prevent OTP discovery via search. Search now only covers `receiver`, `gateway`, and `type`. |
| **Existing Data Scrub** | A new migration (`scrub_otp_plaintext_from_sms_logs`) updates all existing OTP-type records in `sms_logs`, replacing the `message` column with `[OTP REDACTED]`. |

**Files Modified:**
- `app/Lib/Helpers.php` — Added `maskPhoneNumber()`, `redactOtpMessage()`, `maskEmail()` helper functions
- `Modules/Gateways/Entities/SmsLog.php` — Added `masked_receiver` and `redacted_message` model accessors
- `Modules/Gateways/Traits/SmsGateway.php` — Redacted OTP in `mshastra_sms` and `hesed_sms` log data
- `Modules/Gateways/Http/Controllers/Web/Admin/SmsLogController.php` — Removed `message` from search
- `Modules/Gateways/Resources/views/admin/sms-logs/index.blade.php` — Masked receiver and redacted OTP
- `Modules/Gateways/Resources/views/admin/sms-logs/show.blade.php` — Masked receiver and redacted OTP
- **New migration:** `2026_08_15_100000_scrub_otp_plaintext_from_sms_logs.php`

---

## 2. CRITICAL: Receiver Phone Numbers Exposed in SMS Logs

### Problem
Full recipient phone numbers were displayed in plaintext in SMS log views, exposing customer/driver contact data to anyone with log access.

### Fixes Applied

| Area | Action Taken |
|------|-------------|
| **Phone Masking** | All phone numbers in SMS log views are now masked using a `maskPhoneNumber()` helper that shows only the last 4 digits (e.g., `******1234`). |
| **Model Accessor** | Added a `masked_receiver` accessor on the `SmsLog` model so masking is applied consistently wherever the data is displayed. |

**Files Modified:**
- `Modules/Gateways/Entities/SmsLog.php` — `getMaskedReceiverAttribute()`
- `Modules/Gateways/Resources/views/admin/sms-logs/index.blade.php` — Uses `$log->masked_receiver`
- `Modules/Gateways/Resources/views/admin/sms-logs/show.blade.php` — Uses `$log->masked_receiver`

---

## 3. CRITICAL: "Clear All Logs" Without Safeguards

### Problem
The "Clear All Logs" button allowed any admin user to permanently delete all SMS log records with a simple JavaScript confirm dialog. No audit trail was created and no reason was required.

### Fixes Applied

| Area | Action Taken |
|------|-------------|
| **RBAC Gate** | Added `sms_log_clear` gate — restricted to super-admin only. Non-super-admin users cannot see the button or access the endpoint. |
| **Audit Trail** | Every purge now creates an `ActivityLog` entry recording: who cleared, the reason provided, and the number of records deleted. |
| **Required Reason** | The clear action now requires a reason (minimum 5 characters) submitted via a modal form. The reason is stored in the audit log. |
| **UI Change** | Replaced the inline confirm dialog with a Bootstrap modal that shows a warning message and a required reason textarea. |
| **Single Delete Audit** | Individual log deletions also now create `ActivityLog` entries with the full record data stored in the `before` field. |

**Files Modified:**
- `Modules/Gateways/Http/Controllers/Web/Admin/SmsLogController.php` — `clearAll()` and `destroy()` methods rewritten with RBAC, audit logging, and reason validation
- `Modules/Gateways/Resources/views/admin/sms-logs/index.blade.php` — Added confirmation modal with reason field
- `app/Providers/AuthServiceProvider.php` — Added `sms_log_clear` and `sms_log_delete` gates

---

## 4. HIGH: RBAC Was Module-Level, Not Action-Level

### Problem
The system had a `ModuleAccess` entity capable of action-level permissions (view, add, update, delete, log, export), but the role creation/editing UI only exposed module-level checkboxes. Admins could not configure which specific actions a role was allowed to perform per module.

### Fixes Applied

| Area | Action Taken |
|------|-------------|
| **Role Create/Edit UI** | Added an action-level permission grid to both the role create and role edit forms. When a module is selected, a card appears showing checkboxes for each available action (view, add, update, delete, log, export, approve, refund, payout). Includes "Select all" per module. |
| **Permission Storage** | Added a `permissions` JSON column to the `roles` table to store the default action-level permissions per module for each role. |
| **New Action Types** | Added `approve`, `refund`, and `payout` as new action types on the `ModuleAccess` entity and in the `MODULES` constant for `transaction_management`. |
| **Gateway Management Module** | Added a new `gateway_management` module to the `MODULES` constant for SMS log access control. |
| **Employee Service** | Updated the `storeModulePermission()` method to handle the new `approve`, `refund`, and `payout` actions when creating/updating employee permissions. |
| **Controller Updates** | `EmployeeRoleController` store and update methods now pass the `permissions` data from the form to the service layer. |

**Files Modified:**
- `app/Providers/AuthServiceProvider.php` — Added `sms_log_view`, `transaction_approve`, `transaction_refund`, `transaction_payout` gates
- `app/Lib/Constant.php` — Updated `MODULES` constant with new actions and `gateway_management` module
- `Modules/UserManagement/Entities/ModuleAccess.php` — Added `approve`, `refund`, `payout` to fillable and casts
- `Modules/UserManagement/Entities/Role.php` — Added `permissions` to fillable and casts
- `Modules/UserManagement/Http/Controllers/Web/Admin/Employee/EmployeeRoleController.php` — Pass permissions data
- `Modules/UserManagement/Service/EmployeeService.php` — Handle new action types in `storeModulePermission()`
- `Modules/UserManagement/Resources/views/admin/employee/role/index.blade.php` — Action-level permission grid UI
- `Modules/UserManagement/Resources/views/admin/employee/role/edit.blade.php` — Action-level permission grid UI with pre-selected values
- **New migrations:** `add_approve_refund_payout_to_module_accesses.php`, `add_permissions_to_roles_table.php`

---

## 5. HIGH: Maker-Checker Separation for Withdraw Approval

### Problem
A single admin could both approve and settle (payout) a withdrawal request. There was no separation of duties — the same person who approved a withdrawal could also execute the payout, creating a fraud risk.

### Fixes Applied

| Area | Action Taken |
|------|-------------|
| **Approved By Tracking** | Added an `approved_by` column to the `withdraw_requests` table to record which admin approved each request. |
| **Maker-Checker Enforcement** | When a request is approved, `approved_by` is set to the approving admin's ID. When settling, the system checks if the settling admin is the same person who approved — if so, it throws a `ValidationException` with the message: "Maker cannot settle their own approved request. A different approver is required." |
| **Bulk Action Enforcement** | The `multipleUpdate` method also enforces maker-checker — it silently skips any request where the settling admin is the same as the approving admin. |
| **RBAC Gates** | Added `transaction_approve` and `transaction_payout` gates. The withdraw action controller now checks these gates based on the requested status (APPROVED → `transaction_approve`, SETTLED → `transaction_payout`). |
| **Graceful Error Handling** | The controller catches `ValidationException` and shows a user-friendly Toastr error message instead of a 500 error. |
| **Reversal Reset** | When a request is reversed back to PENDING, `approved_by` is reset to null, allowing a fresh approval cycle. |

**Files Modified:**
- `Modules/UserManagement/Entities/WithdrawRequest.php` — Added `approved_by` to fillable
- `Modules/UserManagement/Service/WithdrawRequestService.php` — Maker-checker logic in `update()` and `multipleUpdate()`
- `Modules/UserManagement/Http/Controllers/Web/Admin/Driver/WithdrawRequestController.php` — RBAC gate checks and exception handling
- **New migration:** `add_approved_by_to_withdraw_requests.php`

---

## 6. HIGH: Missing RBAC Gates for SMS Logs and Financial Actions

### Problem
SMS log views had no authorization gates — any authenticated admin could access them. Financial actions (refund approve/deny/execute) used the generic `trip_edit` permission instead of a dedicated financial action gate.

### Fixes Applied

| Area | Action Taken |
|------|-------------|
| **SMS Log Access** | Added `sms_log_view` gate to `SmsLogController` index and show methods. Access requires either super-admin status or `gateway_management` module access with `view` action. |
| **SMS Log Deletion** | Added `sms_log_delete` gate — restricted to super-admin only. |
| **SMS Log Purge** | Added `sms_log_clear` gate — restricted to super-admin only. |
| **Refund Actions** | Changed `RefundController` refund approve, deny, and execute methods from `authorize('trip_edit')` to `authorize('transaction_refund')`. |
| **Financial Gates** | Added `transaction_approve`, `transaction_refund`, `transaction_payout` gates to `AuthServiceProvider`. |

**Files Modified:**
- `app/Providers/AuthServiceProvider.php` — 5 new gates defined
- `Modules/Gateways/Http/Controllers/Web/Admin/SmsLogController.php` — Gates on all methods
- `Modules/TripManagement/Http/Controllers/Web/RefundController.php` — Changed to `transaction_refund` gate

---

## 7. MEDIUM: Central Audit Logging for Sensitive Actions

### Problem
Sensitive administrative actions (SMS log deletion/purge, withdrawal approval/settlement, refund processing) were not being logged to the audit trail. There was no way to trace who did what.

### Fixes Applied

| Action | Audit Logging |
|--------|--------------|
| **SMS Log Delete (single)** | Creates `ActivityLog` with full record data in `before` field, admin ID, and user type. |
| **SMS Log Clear All** | Creates `ActivityLog` with action type, reason provided, and record count. |
| **Withdraw Approve/Deny/Settle/Reverse** | Creates `ActivityLog` with previous status, amount, action taken, and any notes. |
| **Refund Approve/Deny/Execute** | Creates `ActivityLog` with refund ID, action status, and any notes. |

All audit entries record:
- `edited_by` — The admin user ID who performed the action
- `user_type` — The user type of the admin
- `before` — State before the change
- `after` — Action taken and relevant notes
- `logable_type` and `logable_id` — The entity type and ID affected

**Files Modified:**
- `Modules/Gateways/Http/Controllers/Web/Admin/SmsLogController.php` — Audit logging in `destroy()` and `clearAll()`
- `Modules/UserManagement/Service/WithdrawRequestService.php` — `logWithdrawAction()` method
- `Modules/TripManagement/Http/Controllers/Web/RefundController.php` — `logRefundAction()` method

---

## 8. MEDIUM: PII Masking in Customer/Driver List Views

### Problem
Customer and driver phone numbers and email addresses were displayed in plaintext across multiple admin panel views, exposing personally identifiable information (PII) to anyone with list access.

### Fixes Applied

| View | Action Taken |
|------|-------------|
| **Customer List** | Phone numbers masked (last 4 digits only), emails masked (first 2 chars + `***@domain`) |
| **Customer Trashed List** | Same masking applied |
| **Driver List** | Phone and email masked |
| **Driver Trashed List** | Phone and email masked |
| **Driver Profile Update Requests** | Phone and email masked in both list and detail modal |
| **Driver Verification List** | Phone number masked |
| **Driver Verification Detail** | Phone number masked |
| **Refund Details View** | Driver phone and email masked |
| **Refund Export** | Customer phone number masked in exported data |

**Masking Format:**
- Phone: `******1234` (only last 4 digits visible)
- Email: `jo***@example.com` (first 2 chars + masked local part + full domain)

**Files Modified:**
- `app/Lib/Helpers.php` — `maskPhoneNumber()` and `maskEmail()` helper functions
- `Modules/UserManagement/Resources/views/admin/customer/index.blade.php`
- `Modules/UserManagement/Resources/views/admin/customer/trashed.blade.php`
- `Modules/UserManagement/Resources/views/admin/driver/index.blade.php`
- `Modules/UserManagement/Resources/views/admin/driver/trashed.blade.php`
- `Modules/UserManagement/Resources/views/admin/driver/profile-update-request.blade.php`
- `Modules/UserManagement/Resources/views/admin/driver/verification/unverified-list.blade.php`
- `Modules/UserManagement/Resources/views/admin/driver/verification/partials/_view-driver-verification-request.blade.php`
- `Modules/TripManagement/Resources/views/admin/refund/details.blade.php`
- `Modules/TripManagement/Http/Controllers/Web/RefundController.php` — Export data masking

---

## 9. LOW: Navigation Inconsistencies

### Finding
The audit noted placeholder `href="#"` links and a "Welcome Air" greeting in the sidebar.

### Assessment
After code review:
- The `href="#"` links are **intentional** Bootstrap navigation patterns used for parent menu items that expand/collapse sub-menus via JavaScript. This is standard admin panel behavior, not a bug.
- The "Welcome Air" greeting is **dynamic** — it displays `auth('web')->user()->first_name`, which was "Air" for the audited user. This is not a hardcoded string.

**No code changes were required for this finding.**

---

## Database Migrations

The following 4 migrations need to be run:

```
php artisan migrate
```

| Migration | Purpose |
|-----------|---------|
| `2026_08_15_100000_scrub_otp_plaintext_from_sms_logs` | Replaces all existing OTP plaintext in `sms_logs.message` with `[OTP REDACTED]` |
| `2026_08_15_110000_add_approve_refund_payout_to_module_accesses` | Adds `approve`, `refund`, `payout` boolean columns to `module_accesses` table |
| `2026_08_15_120000_add_permissions_to_roles_table` | Adds `permissions` JSON column to `roles` table for action-level permission storage |
| `2026_08_15_130000_add_approved_by_to_withdraw_requests` | Adds `approved_by` UUID column to `withdraw_requests` table for maker-checker tracking |

---

## Summary Table

| # | Severity | Finding | Status |
|---|----------|---------|--------|
| 1 | CRITICAL | OTP plaintext in SMS logs | ✅ Fixed |
| 2 | CRITICAL | Receiver phone numbers exposed | ✅ Fixed |
| 3 | CRITICAL | Clear All Logs without safeguards | ✅ Fixed |
| 4 | HIGH | RBAC module-level only | ✅ Fixed |
| 5 | HIGH | No maker-checker for withdrawals | ✅ Fixed |
| 6 | HIGH | Missing RBAC gates for SMS/finance | ✅ Fixed |
| 7 | MEDIUM | No audit logging for sensitive actions | ✅ Fixed |
| 8 | MEDIUM | PII exposed in list views | ✅ Fixed |
| 9 | LOW | Navigation inconsistencies | ✅ Reviewed — no code fix needed |

---

*End of Report*

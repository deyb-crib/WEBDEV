# KATOC Multi-Hospital Phase 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a backward-compatible role and hospital-ownership foundation so the existing admin remains the Super Admin and future Hospital Admin accounts can be securely scoped to one hospital.

**Architecture:** Preserve the existing `users` and `dialysis_centers` tables as the current data boundary, then add explicit role and ownership fields through an idempotent migration. Authentication will expose separate Super Admin and Hospital Admin guards; hospital-admin queries will use the authenticated session's hospital id rather than URL input. Existing patient accounts, admin sessions, centers, bookings, and schedules remain usable during the migration.

**Tech Stack:** PHP 8+, MySQL/MariaDB, PDO prepared statements, PHP sessions, existing KATOC admin CSS/HTML, lightweight PHP regression scripts where no test framework exists.

**Spec:** `docs/superpowers/specs/2026-09-08-multi-hospital-platform-design.md`

## Global Constraints

- Preserve existing patient accounts, hospitals, schedules, bookings, public pages, and styling.
- Keep `admin@katoc.local` as the Super Admin account.
- Never trust `hospital_id` from a URL or form for Hospital Admin authorization.
- Use CSRF protection on state-changing actions.
- Do not delete or recreate the existing database.
- Use PDO prepared statements for all user-controlled values.
- Report unmatched legacy booking-center names instead of silently assigning them.

---

### Task 1: Define the role and ownership migration

**Files:**
- Create: `database/migrations/2026-09-08-multi-hospital-phase-1.sql`
- Modify: `database/schema.sql`
- Modify: `database/admin_schema.sql`
- Test: `tests/phase1_migration_check.php`

**Interfaces:**
- Produces columns `users.role`, `users.hospital_id`, `dialysis_centers.facility_type`, and `appointments.hospital_id`.
- Produces a `hospital_admins` view/table boundary that references `users.id` and `dialysis_centers.id`.
- Preserves existing `admin` and `patient` role values during transition.

- [ ] **Step 1: Write the failing migration check**

Create a PHP check that connects through `getMySqlConnection()`, asserts the required columns/tables are present, and reports each missing object by name. The check must inspect `information_schema.columns` and `information_schema.tables`; it must not modify the database.

- [ ] **Step 2: Run the check and verify it fails before migration**

Run:

```powershell
php tests/phase1_migration_check.php
```

Expected: FAIL with at least one missing Phase 1 object on an un-migrated database.

- [ ] **Step 3: Write the migration**

Add an explicit one-time SQL migration that:

1. Adds `hospital_id` to `users` as nullable with an index and a foreign key to `dialysis_centers(id)` after the existing center table is available.
2. Adds `hospital_id` to `appointments` as nullable with an index and a foreign key to `dialysis_centers(id)`.
3. Creates `hospital_admins` only if it does not exist, with `user_id` unique, `hospital_id` indexed, active status, and timestamps.
4. Keeps existing role values unchanged and records the intended transition values `SUPER_ADMIN`, `HOSPITAL_ADMIN`, and `PATIENT` in schema documentation.
5. Creates a migration report query that lists appointments whose `center_name` does not match an existing center name.

Because the local MySQL version may not support `ADD COLUMN IF NOT EXISTS`, the migration must use a stored procedure or explicit information-schema guards for idempotency.

- [ ] **Step 4: Update fresh-install schemas**

Add the same final columns and constraints to `database/schema.sql` and `database/admin_schema.sql`, without changing existing seed records except to label the seeded admin as the Super Admin-compatible account.

- [ ] **Step 5: Run the migration and check**

Run the migration in the local `katoc` database, then run:

```powershell
php tests/phase1_migration_check.php
```

Expected: PASS with every required object listed as present.

---

### Task 2: Add explicit authentication role helpers

**Files:**
- Modify: `admin/includes/auth.php`
- Modify: `admin/login.php`
- Create: `hospital/includes/auth.php`
- Test: `tests/authorization_scope_check.php`

**Interfaces:**
- Produces `requireSuperAdmin(): array` for platform-wide admin pages.
- Produces `requireHospitalAdmin(): array` returning `id`, `name`, `email`, `role`, and authenticated `hospital_id`.
- Produces `currentHospitalId(): int` that reads only the authenticated Hospital Admin session.

- [ ] **Step 1: Write failing authorization checks**

The check must exercise the authorization helper contract with sessions representing:

```php
['id' => 10, 'role' => 'HOSPITAL_ADMIN', 'hospital_id' => 3]
['id' => 11, 'role' => 'HOSPITAL_ADMIN', 'hospital_id' => 4]
['id' => 1, 'role' => 'SUPER_ADMIN', 'hospital_id' => null]
```

Assert that a Hospital Admin receives only their session hospital id and that a supplied URL hospital id is ignored by the helper.

- [ ] **Step 2: Run the check and verify it fails**

Run:

```powershell
php tests/authorization_scope_check.php
```

Expected: FAIL because the new role-specific helpers do not yet exist.

- [ ] **Step 3: Implement the helpers**

Update admin authentication to accept the transition role `admin` for the seeded account while writing `SUPER_ADMIN` into the session. Add explicit role checks for `SUPER_ADMIN` and `HOSPITAL_ADMIN`. Hospital Admin authentication must load the linked hospital id from the database, not from request data.

- [ ] **Step 4: Run the focused check**

Run:

```powershell
php tests/authorization_scope_check.php
```

Expected: PASS, including the cross-hospital URL tampering case.

---

### Task 3: Create the Hospital Admin account management foundation

**Files:**
- Create: `admin/hospital_admins.php`
- Create: `admin/actions/create_hospital_admin.php`
- Create: `admin/actions/update_hospital_admin.php`
- Modify: `admin/includes/sidebar.php`
- Test: `tests/hospital_admin_account_check.php`

**Interfaces:**
- Super Admin can list Hospital Admin accounts with their assigned hospital and status.
- Super Admin can create an account with `name`, `email`, `password`, and `hospital_id`.
- Super Admin can activate/deactivate an account without deleting historical bookings.

- [ ] **Step 1: Write the failing account check**

The check must assert that a created account has role `HOSPITAL_ADMIN`, exactly one linked hospital, a password hash that verifies, and no plaintext password stored.

- [ ] **Step 2: Run the check and verify it fails**

Run:

```powershell
php tests/hospital_admin_account_check.php
```

Expected: FAIL because the management endpoint and persistence contract do not exist.

- [ ] **Step 3: Implement the Super Admin page and actions**

Use the existing admin patterns for CSRF validation, prepared statements, redirects, flash messages, and CSS. The create action must validate email/password/hospital selection, hash the password with `password_hash`, and force role `HOSPITAL_ADMIN`. The update action must scope changes by the target user id while preserving the assigned hospital relationship and historical data.

- [ ] **Step 4: Add the navigation entry**

Add `Hospital Admins` to the Super Admin sidebar. Do not show it to Hospital Admin sessions.

- [ ] **Step 5: Run focused checks and PHP lint**

Run:

```powershell
php tests/hospital_admin_account_check.php
php -l admin/hospital_admins.php
php -l admin/actions/create_hospital_admin.php
php -l admin/actions/update_hospital_admin.php
```

Expected: PASS and no syntax errors.

---

### Task 4: Add scoped query boundaries for future Hospital Admin pages

**Files:**
- Create: `hospital/includes/scope.php`
- Create: `hospital/dashboard.php`
- Create: `hospital/bookings.php`
- Create: `hospital/patients.php`
- Test: `tests/hospital_scope_queries.php`

**Interfaces:**
- `hospitalScopeWhere(string $alias = ''): array` returns SQL fragment and bound `hospital_id` parameter for authenticated Hospital Admin requests.
- Hospital pages never accept a hospital id from query string or form data.
- Super Admin remains able to access platform-wide pages.

- [ ] **Step 1: Write failing cross-hospital scope tests**

Seed or use two existing centers and assert that a session for hospital A cannot retrieve hospital B bookings, schedules, or patients even when `?hospital_id=B` is supplied.

- [ ] **Step 2: Run the tests and verify failure**

Run:

```powershell
php tests/hospital_scope_queries.php
```

Expected: FAIL because scoped Hospital Admin routes do not exist.

- [ ] **Step 3: Implement the scope helper**

Build every Hospital Admin query around the authenticated session hospital id and add an explicit guard that rejects missing or inactive hospital assignments. Keep SQL aliases explicit to prevent ambiguous joins.

- [ ] **Step 4: Implement the initial hospital dashboard, bookings, and patients pages**

Add read-only pages first. Display the hospital name, booking counts, recent booking requests, and patient list using only scoped queries. Keep booking status changes for the next phase so this phase does not mix ownership with workflow mutation.

- [ ] **Step 5: Run scope tests and lint**

Run:

```powershell
php tests/hospital_scope_queries.php
php -l hospital/includes/scope.php
php -l hospital/dashboard.php
php -l hospital/bookings.php
php -l hospital/patients.php
```

Expected: PASS with no cross-hospital data leakage.

---

### Task 5: Migration verification and handoff

**Files:**
- Modify: `docs/superpowers/specs/2026-09-08-multi-hospital-platform-design.md`
- Create: `docs/superpowers/plans/phase-1-verification-report.md`

- [ ] **Step 1: Run the complete Phase 1 checks**

Run all migration, authorization, account, scope, PHP lint, and existing application smoke checks. Record the exact commands and results.

- [ ] **Step 2: Verify compatibility**

Confirm that the existing Super Admin can still open the current dashboard, centers, schedules, bookings, users, and reports pages, and that the public hospital page still renders existing and newly added facilities.

- [ ] **Step 3: Record unresolved migration rows**

Write any unmatched legacy appointments or inactive/invalid hospital assignments to the verification report for explicit resolution before Phase 2.

- [ ] **Step 4: Update the design handoff**

Mark Phase 1 as complete in the design documentation and list the exact interfaces Phase 2 can consume.

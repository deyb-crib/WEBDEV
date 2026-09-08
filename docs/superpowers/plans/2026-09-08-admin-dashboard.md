# KATOC Admin Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add a role-protected, MySQL-backed KATOC administration area for centers, schedules, bookings, patients, notifications, reports, and settings.

**Architecture:** Reuse the existing PDO connection, PHP sessions, request helpers, CSRF helpers, and KATOC visual language. Add an admin role to users, admin-only guards, shared admin layout files, and parameterized action endpoints. Keep the patient booking and dashboard flows unchanged.

**Tech Stack:** PHP 8, MySQL, HTML, CSS, vanilla JavaScript.

**Spec:** User-provided KATOC Admin Interface/Dashboard requirements in the current conversation.

## Global Constraints

- Admin pages require an authenticated session with `users.role = 'admin'`.
- All writes use prepared statements and CSRF validation.
- All request fields use `config/request.php`; rendered database values use `htmlspecialchars()`.
- Admin actions must scope records by IDs and validate allowed status values.
- Existing patient routes and booking behavior remain compatible.

### Task 1: Schema and Admin Authentication

**Files:**
- Create: `database/admin_schema.sql`
- Create: `admin/includes/auth.php`
- Create: `admin/login.php`
- Create: `admin/logout.php`
- Modify: `database/schema.sql`
- Modify: `login/login_function.php`
- Modify: `login/signup_function.php`

Add `users.role` with `patient` default and `admin` allowlist, plus `admin_notifications`, `dialysis_centers`, and `center_schedules` tables. Add an admin guard that redirects non-admin sessions to `admin/login.php`.

### Task 2: Shared Admin Shell and Styling

**Files:**
- Create: `admin/includes/db.php`
- Create: `admin/includes/header.php`
- Create: `admin/includes/sidebar.php`
- Create: `admin/includes/footer.php`
- Create: `admin/css/admin.css`
- Create: `admin/js/admin.js`

Build the fixed responsive sidebar and shared header, active navigation state, flash messages, modal behavior, and KATOC visual tokens.

### Task 3: Dashboard Metrics and Reports

**Files:**
- Create: `admin/dashboard.php`
- Create: `admin/reports.php`

Use SQL aggregates for patient count, center count/status, booking statuses, today/upcoming activity, center demand, and monthly status totals. Render accessible tables plus lightweight CSS charts.

### Task 4: Center and Schedule CRUD

**Files:**
- Create: `admin/centers.php`
- Create: `admin/schedules.php`
- Create: `admin/actions/add_center.php`
- Create: `admin/actions/edit_center.php`
- Create: `admin/actions/delete_center.php`
- Create: `admin/actions/save_schedule.php`
- Create: `admin/actions/delete_schedule.php`

Add authenticated CRUD with validation for center fields and schedule slot counts, search/filter query handling, and confirmation messages.

### Task 5: Booking and Patient Management

**Files:**
- Create: `admin/bookings.php`
- Create: `admin/users.php`
- Create: `admin/actions/update_booking.php`
- Create: `admin/actions/update_user.php`

List bookings and users using parameterized filters. Allow valid booking status transitions, user activate/deactivate, and detail/history views while keeping patient ownership boundaries intact.

### Task 6: Notifications and Settings

**Files:**
- Create: `admin/notifications.php`
- Create: `admin/settings.php`

Display unread/read admin notifications and provide profile, notification, and security settings backed by the existing user/session model.

### Task 7: Validation

Run PHP lint on every admin PHP file, JavaScript syntax checks, SQL schema review, and request-boundary smoke checks. Verify unauthenticated and non-admin requests redirect, while admin pages render with seeded admin data.

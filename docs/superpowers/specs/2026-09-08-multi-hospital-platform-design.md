# KATOC Multi-Hospital Platform Design

## Decision

KATOC will be migrated incrementally into a multi-hospital dialysis booking platform. Existing patient accounts, hospitals, schedules, bookings, public pages, and styling will be preserved. The existing `admin@katoc.local` account remains the KATOC Super Admin.

## Roles and Ownership

- `SUPER_ADMIN`: platform-wide access to hospitals, hospital admins, patients, bookings, reports, notifications, and settings.
- `HOSPITAL_ADMIN`: access limited to the hospital stored in the authenticated session.
- `PATIENT`: public search, booking, personal bookings, and personal notifications.

Hospital-admin queries must derive `hospital_id` from the authenticated session. URL parameters may select a record within that scope, but must never select the tenant scope.

## Data Model Migration

The existing `dialysis_centers` table becomes the hospitals table boundary without deleting current rows. A compatibility migration adds:

- Stable hospital ownership through `hospital_id` references.
- Hospital-admin accounts linked to one hospital.
- Normalized dialysis types and hospital-service relationships while retaining current text values during migration.
- Schedule ownership through `hospital_id` and schedule identifiers.
- Booking ownership through `hospital_id`, `schedule_id`, and dialysis type identifiers.
- Persistent patient and hospital notifications.

Existing appointments are backfilled by matching their current `center_name` to `dialysis_centers.name`. Ambiguous or unmatched rows are reported rather than silently assigned.

## Authentication

The current admin authentication remains the Super Admin entry point. A hospital-admin login uses the same application session model but stores the authenticated role and hospital identifier. Authorization helpers will expose separate guards for Super Admin and Hospital Admin routes. Hospital-admin pages will query through scoped helpers so every booking, schedule, patient, report, and notification is filtered by the session hospital.

## Booking Flow

1. A patient selects an active hospital.
2. The application reads that hospital's real schedules and remaining capacity.
3. A booking is created with patient, hospital, schedule, dialysis type, date, time, and `PENDING` status.
4. The owning Hospital Admin confirms or rejects it.
5. Confirmation reserves capacity and creates a patient notification.
6. Rejection records a reason, releases any pending hold, and creates a patient notification.
7. Super Admin can observe and report on all bookings without manually processing each one.

State changes must be transactional and must prevent a confirmed booking from exceeding a schedule's available capacity.

## Interfaces

The existing public website remains the patient-facing entry point. Its hospital cards and availability dialogs will read active hospitals and real schedules from the database. The existing Super Admin area will gain hospital-admin management and platform-wide views. A new `/hospital` area will provide dashboard, bookings, schedules, dialysis services, patients, notifications, reports, profile, and settings pages using the same application and database.

## Delivery Phases

1. **Ownership foundation:** schema migration, role constants, session guards, hospital-admin accounts, and safe backfill reporting.
2. **Scoped hospital administration:** hospital dashboard, bookings, patients, and profile with enforced hospital scope.
3. **Real schedules and availability:** schedule CRUD, dialysis-service assignment, capacity calculation, and public availability.
4. **Booking decisions:** confirm/reject workflows, transactions, status history, and notifications.
5. **Reports and platform monitoring:** hospital reports, Super Admin reports, activity monitoring, and booking trends.
6. **UI consolidation and verification:** role-specific navigation, responsive behavior, regression coverage, and migration checks.

Each phase will preserve the existing workflow until its replacement path is verified. No destructive reset or database recreation is part of the migration.

## Error Handling and Security

- Reject invalid or cross-hospital record identifiers with a safe response.
- Never trust `hospital_id` from a form or URL for authorization.
- Use CSRF protection on all state-changing actions.
- Use transactions for booking confirmation, rejection, and slot updates.
- Keep persistent notifications so users see them after logging in later.
- Report unmatched legacy bookings during migration.

## Verification

Phase gates will include PHP syntax checks, focused database migration checks, authorization tests for cross-hospital access, booking state-transition tests, capacity-concurrency tests, and browser checks for the public and role-specific flows.

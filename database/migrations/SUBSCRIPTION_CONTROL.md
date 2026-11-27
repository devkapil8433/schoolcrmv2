# Subscription & Branch Access Control

Adds a School (tenant) level above branches, per-school subscriptions with manual payments, a grace period,
an audit log, and a central server-side access guard. Framework: CodeIgniter 3.1.13, PHP 8.2, MariaDB.

## Hierarchy

Platform Super Admin (role 1) -> School (`school`) -> Branch (`branch.school_id`) -> users (`branch_id` columns, unchanged)

* Super Admin: everything, never blocked.
* School Admin = role 2 (Admin) listed in `school_admin`: manages every branch of that school (Branch Management, "work in this branch").
* Branch Admin = any other role 2: own branch only (unchanged).
* Teacher / Student / Parent / Accountant etc.: unchanged permissions, plus the subscription/branch gate.

## Enforcement (server side)

`MY_Controller` -> `Access_guard::run()` (application/libraries/Access_guard.php) on every request of a logged-in non-super-admin:

1. branch exists and `branch.status = 1`
2. branch linked to a school and `school.status = 1`
3. school has a subscription
4. effective state is `active` or `grace` (`suspended` / `expired` are blocked)

Blocked requests get HTTP 402 (subscription) or 403 (branch/school inactive): an HTML page for browsers, JSON for AJAX /
`Accept: application/json` / `/api/...` requests. Always reachable: `authentication/*` (login, logout, account switch),
`subscription_notice` (status page + JSON), error pages. Public pages (website, online admission, payment callbacks, cron)
are not user-authenticated and are not touched.

Effective state: suspended (admin flag) -> active (today <= expiry) -> grace (<= expiry + grace days) -> expired.
Grace days = subscription override, otherwise the platform default (Subscription Settings).

### Mobile app / API contract

No mobile API exists in this project. Any API controller that extends `MY_Controller` is protected automatically.
Controllers that do not extend it must call `$this->load->library('access_guard'); $this->access_guard->api_check($branch_id);`.
Blocked response:

```json
{"status": false, "code": "SUBSCRIPTION_EXPIRED", "subscription_state": "expired",
 "message": "...", "expiry_date": "2026-11-03", "grace_end_date": "2026-11-03", "contact": "..."}
```

Codes: `SUBSCRIPTION_EXPIRED`, `SUBSCRIPTION_SUSPENDED`, `SUBSCRIPTION_MISSING`, `BRANCH_INACTIVE`, `SCHOOL_INACTIVE`,
`SCHOOL_NOT_ASSIGNED`, `BRANCH_NOT_FOUND`. `GET /subscription_notice/status` returns the same contract (or `status:true` with
state/expiry/warning) for the logged-in user.

## Database

Migration: `database/migrations/2026_11_04_subscription_control.sql`
Rollback:  `database/migrations/2026_11_04_subscription_control_rollback.sql`

New tables: `school`, `school_admin`, `school_subscription`, `subscription_payment`, `subscription_settings`, `platform_audit_log`.
Changed table: `branch` (+ `school_id`, `status_changed_at`, `status_changed_by`, indexes, FK). The migration links existing branches to a
school created from `branch.school_name` and creates a baseline Active subscription (expiry 2027-11-04) so nobody is locked out.
No existing rows are deleted. Passwords/payment credentials are not stored anywhere in the new tables.

### Backup (do this first)

```
C:\xampp\mysql\bin\mysqldump.exe -u root --single-transaction schoolpilot360 > database\backups\before_subscription.sql
```
Apply:    `mysql -u root schoolpilot360 < database\migrations\2026_11_04_subscription_control.sql`
Rollback: `mysql -u root schoolpilot360 < database\migrations\2026_11_04_subscription_control_rollback.sql`
(rollback drops the new tables - export them first if you need the payment/audit data). If the new tables are missing the guard
does nothing (feature off), so the application keeps working after a rollback; also revert the code files listed below if you want
the menus gone.

Full restore: import the dump taken before migrating.

## New and modified files

New: controllers `Schools`, `Subscriptions`, `Branch_manager`, `Subscription_notice`; model `Subscription_model`; library `Access_guard`;
views `schools/*`, `subscriptions/*`, `branch_manager/*`, `subscription/blocked.php`; the two migration SQL files.

Modified: `core/MY_Controller.php` (guard call), `controllers/Branch.php` (hard delete disabled - it removed staff/logins; use deactivate),
`models/Branch_model.php` (legacy create form links branch to a school + audit), `helpers/general_helper.php` (`subscription_badge`,
`get_subscription_notice`), `config/routes.php` (3 routes), `views/layout/sidebar.php` (menus), `views/layout/index.php` (renewal banner),
`views/branch/add.php` (delete button replaced), `.gitignore` (allow migration .sql).

## Super Admin menus ("Subscription Control")

School Management, Branch Management, Subscription Management (per-school page: suspend/activate, mark paid/unpaid, extend, record
payment, void payment, history), Payment History, Expiring and Expired, Subscription Settings (default grace days, warning days, message), Audit Log.
Payments are never deleted - a correction voids the row (reason required) and a new one is recorded. Each payment stores amount, date,
method, reference, note and the recording administrator.

Audit log events: school/branch created/updated/activated/deactivated, branch switched, subscription created/updated/suspended/activated/
extended/payment-status, payment recorded/voided, settings updated, School Admin assigned/removed.

## Test scenarios (all executed against the local database, then reverted)

1. Super Admin sees all new pages; Teacher/Student/Accountant/School Admin cannot open `/subscriptions`, `/schools`.
2. School Admin sees only own school's branches; cannot touch other school's branches, cannot create/move branches across schools,
   cannot mark a subscription paid, cannot deactivate the branch it is working in.
3. Branch deactivate -> teacher/student/accountant/school admin get 403 (pages, direct URLs, AJAX JSON); Super Admin unaffected;
   reactivate -> access restored; row counts of student/staff/enroll/attendance/fees unchanged.
4. Subscription: grace (warning for admins only, access kept) -> expired (402 everywhere incl. direct URLs and JSON) -> extend/payment
   restores access; suspend blocks, activate restores; other school's expiry does not affect this school.
5. Existing login and dashboard work for all roles before and after.

To repeat manually: Subscription Management -> school -> Edit, set expiry to yesterday and grace 0, log in as a teacher in another
browser; then extend expiry and refresh.

## Limitations / notes

* No mobile API exists in this repo; use the contract above. Enforcement for a separate API project must call `api_check()` or the status endpoint.
* A school without a subscription is blocked (secure default). Branches created from the legacy Branch form get a school automatically
  but need a subscription created by the Super Admin.
* The `edschool/` and `schoolpilot360/` sub-folders were not modified (root `application/` only).

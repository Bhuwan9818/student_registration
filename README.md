# Student Admission Portal

A complete PHP + MySQL admin panel for managing student admissions across
**multiple universities** — each with its own courses, seat capacity, and
semester-wise fee structure — with multi-step registration, fee collection,
role-based staff logins, and a premium, fully responsive design.

## Design

Branded for **VS Academy** — ink-navy + brass-gold theme sampled directly
from the college logo (`assets/img/logo.png`), which appears in the sidebar,
login screen, and printable admission slip. Serif display face (Fraunces)
for headings and numbers, a clean sans (Inter) for UI, and a monospace face
(IBM Plex Mono) for registration numbers/IDs. The sidebar collapses into a
slide-out drawer with a backdrop on tablet/mobile; stat cards, tables, and
forms all reflow down to phone width.

To swap in a different logo later, just replace `assets/img/logo.png` with
a same-aspect-ratio PNG (transparent background works best) — no code
changes needed.

## How the multi-university model works

This is the core of the portal, so it's worth explaining up front:

- **Universities own courses.** Each course (e.g. "B.Tech", "BBA") belongs to
  exactly one university — not a shared global list. University A can have
  30 courses while University B has 5; they're independent.
- **Courses own their fee structure.** Each course has its own semester-wise
  fee amounts (semester 1, 2, 3...), set by the admin in **Master Data →
  Manage semester fees**. Number of semesters = course duration in years × 2.
- **"Change University" drives everything else.** Every logged-in user
  (admin or staff) has an *active university* for their session, set via the
  **Change University** button in the top-right of the navbar. Once picked,
  it stays active until changed again, and everything scopes to it:
  - The registration wizard's course dropdown only shows that university's courses.
  - "All Registrations", "Fee Verification", and both dashboards only show
    that university's data.
  - Master Data's course list/seat usage/fee structure editing is scoped to it.
  - Re-registration search only matches students already in that university.
- **First login (or first-ever setup)** sends the user to a full-page
  university picker (`choose_university.php`) before they can do anything
  else. If no universities exist yet at all, admins are pointed to Master
  Data to create the first one.

This mirrors how a real multi-college admission office works: staff/admin
pick which university's desk they're sitting at, and the whole portal
reflects that until they switch.

## Features

- **Admin login** — full access to everything below, across all universities
  (by switching between them).
- **Centers & Sub-Centers** — admin creates Center accounts (the primary
  registration accounts, formerly called "staff"). Each Center can also have
  Sub-Center accounts working under it, created by admin and handed off to
  whoever the Center wants to delegate to. Sub-Centers have identical
  permissions to Centers (registration, fee submission, viewing their own
  work) but everything they do is tracked separately: their registrations
  show up under **Sub-Centers**, a Center's own registrations show up under
  **Centers**, and admin can always see which Center a given Sub-Center
  belongs to (on the Sub-Centers page, and next to their name anywhere their
  registrations appear).
- **Per-Center / per-Sub-Center reports** — both the Centers and Sub-Centers
  pages show, at a glance, each account's total registrations, online fees
  collected, and offline fees collected (online = Online transfer/UPI/Card;
  offline = Cash/Cheque). Clicking through to a **Report** page shows the
  same three figures as stat cards, the full list of that account's
  registrations with fee channel per student, and a **Download Report (CSV)**
  button for that specific Center or Sub-Center.
- **Centers and Sub-Centers as expandable sidebar menus** — each opens into:
  *Center/Sub-Center Ledgers* (the account list/creation page), *Online Fee*,
  and *Offline Fee* (portal-wide filterable ledgers of every online/offline
  payment across all Centers or all Sub-Centers, each with its own CSV
  export). Sub-Centers also has **Student Ledger** — a one-page form for
  admin to directly add a student's record (skipping the step-by-step
  wizard) and attribute it to whichever Center, Sub-Center, or admin's own
  account it should be tracked under.
- **Multi-step registration wizard** (5 steps): Personal Details → Contact &
  Address → Guardian Details → Academic Background → Course Selection &
  Review. Course list is scoped to the active university, shows live
  seat-availability. Available to **both admin and staff** — labeled
  "Apply Fresh" in the sidebar.
- **Re-Registration** — for existing *approved* students (in the active
  university) continuing into a new session/year. Search by registration
  number, mobile, or name, pick the student, choose the new session (and
  course if it changed), and a new registration record is created with all
  personal/contact/academic details carried forward automatically, semester
  number incremented by one. Linked back to the original record on the
  student detail page, and routes straight to the fee page afterward.
- **Semester-wise fee structure per course** — admin sets an amount for each
  semester of each course. When staff submit a fee, the portal shows the
  expected amount for that student's current semester and pre-fills it
  (still editable, in case the actual amount differs).
- **Seat capacity tracking** — set a total seat count per course; the portal
  shows filled/available seats on Master Data, the registration wizard, and
  the staff dashboard.
- **View all registrations** (scoped to the active university) with filters:
  course, session/year, status, registration type (fresh/re-reg), staff,
  gender, date range, and free-text search — plus a topbar quick-search from
  anywhere in the portal.
- **Bulk approve/reject** — select multiple registrations in the list and
  action them together.
- **CSV export** of the filtered registration list.
- **Fee submission** — a separate step after registration succeeds. Staff can
  either enter fee details manually (cash/cheque, no proof) or upload a
  payment proof (UTR number and/or screenshot). Admin verifies or rejects
  each fee entry.
- **Registration review** — admin can approve/reject a submitted registration.
- **Printable admission slip** — a clean, letterhead-style A4 page per
  student (photo, course, semester, fee status) that staff/admin can print
  or save as PDF.
- **Dashboard analytics** — a 14-day registration trend chart and a
  registrations-by-course chart (both scoped to the active university), plus
  a portal-wide activity feed.
- **Activity log** — a full audit trail of registrations, approvals,
  rejections, fee submissions/verifications, and staff account creation.
- **Master data management** — admin adds/disables universities (global),
  and per-university: courses, seat caps, and semester-wise fee structures.
  Sessions/years are shared across all universities.

## Setup (XAMPP / local)

### Fresh install

1. Copy the `admission-portal` folder into your `htdocs` directory (or your
   web server's document root).
2. Open **phpMyAdmin**, create nothing manually — instead import
   `database/schema.sql` directly (it creates the `admission_portal`
   database itself, including sample universities/courses/fee structure).
3. Open `config/config.php` and update DB credentials if needed (defaults to
   `root` with no password, standard XAMPP setup).
4. If you host the project in a subfolder (e.g. `http://localhost/admission-portal`),
   set `BASE_URL` in `config/config.php` to `/admission-portal`. If it's the
   webroot itself, leave it as `''`.
5. Make sure the `uploads/` folder (and its subfolders `photos`, `marksheets`,
   `fee_proofs`) are writable by the web server.
6. Visit `login.php` in your browser. On first login you'll be sent to pick
   a university before reaching the dashboard.

### Updating an existing install (you already had the portal running)

Just replace your PHP/CSS/JS files with the ones in this package, then run
whichever of these you haven't already, **in order**, against your existing
`admission_portal` database:

1. `database/migration_v2.sql` — adds `total_seats` on `courses` and the
   `activity_log` table.
2. `database/migration_v3.sql` — adds `registration_type` and
   `parent_student_id` on `students` for Fresh vs Re-Registration support.
3. `database/migration_v4.sql` — makes courses belong to a specific
   university, adds the `course_fees` table (semester-wise pricing), and
   adds `semester_no` on `students`. **Note:** since your existing courses
   weren't tied to a university before, this migration assigns them all to
   your first university by default — go to Master Data afterward (with that
   university active) to review them, and move any to the correct
   university by re-creating them there if needed.
4. `database/migration_v5.sql` — adds `parent_user_id` on `users` for the
   Centers/Sub-Centers hierarchy. Every existing staff account becomes a
   Center automatically (nothing to fix up here).

None of these delete existing data.

## Default login

```
Username: admin
Password: Admin@123
```

**Change this immediately** — once logged in, click your name in the top-right
corner → **Change Password**. Only admin has self-service password changes;
Center and Sub-Center passwords are set/reset by admin from the Centers or
Sub-Centers page (the "Reset Pwd" button on each account).

## Roles at a glance

| Action                          | Admin | Center | Sub-Center |
|----------------------------------|:-----:|:-----:|:-----:|
| Switch active university         |   ✅   |   ✅   |   ✅   |
| Register a new student (Apply Fresh) |  ✅  |   ✅   |   ✅   |
| Re-register an existing student for a new session | ✅ | ✅ | ✅ |
| View all registrations (active university) |  ✅  |   —   |   —   |
| View only their own registrations|   —   |   ✅   |   ✅   |
| Filter/search registrations      |   ✅   |   ✅ (own only) | ✅ (own only) |
| Approve/reject a registration    |   ✅   |   —   |   —   |
| Submit fee for a student they registered | ✅ | ✅ | ✅ |
| Verify/reject a fee submission   |   ✅   |   —   |   —   |
| Create/disable Center accounts   |   ✅   |   —   |   —   |
| Create/disable Sub-Center accounts (under a Center) | ✅ | — | — |
| Manage universities              |   ✅   |   —   |   —   |
| Manage courses/seat caps/fee structure (active university) | ✅ | — | — |

Centers and Sub-Centers have identical day-to-day permissions — the only
difference is that admin can see which Center a Sub-Center belongs to, and
their registrations are tracked in separate lists (Centers page vs.
Sub-Centers page) for reporting purposes.

## Folder structure

```
admission-portal/
├── config/config.php          <- DB connection + auth + university-context helpers
├── database/schema.sql        <- import this for a fresh install
├── database/migration_v2.sql  <- run if upgrading from the original build
├── database/migration_v3.sql  <- run to add Fresh vs Re-Registration support
├── database/migration_v4.sql  <- run to add multi-university courses + fee structure
├── database/migration_v5.sql  <- run to add Centers/Sub-Centers hierarchy
├── includes/                  <- shared header/footer/sidebar (incl. university switcher)
├── assets/css/style.css       <- all styling (design tokens at the top)
├── assets/img/logo.png        <- VS Academy logo (swap this file to rebrand)
├── assets/js/script.js        <- sidebar drawer, fee form toggle, bulk-select
├── uploads/                   <- photos, marksheets, fee proofs get saved here
├── login.php / logout.php / index.php / change_password.php
├── choose_university.php      <- the "Change University" picker page
├── admin_dashboard.php        <- admin home: stats, charts, activity feed (scoped)
├── admin_students.php         <- all registrations, filters, bulk actions, CSV export (scoped)
├── admin_fees.php             <- fee verification queue (scoped)
├── admin_centers.php          <- create/disable Center accounts, reset passwords
├── center_detail.php          <- per-Center report: registrations, online/offline fees, CSV export
├── admin_subcenters.php       <- create/disable Sub-Center accounts (under a Center)
├── subcenter_detail.php       <- per-Sub-Center report: registrations, online/offline fees, CSV export
├── fee_report.php             <- Online/Offline Fee ledgers (shared page, ?scope=center|subcenter&channel=online|offline)
├── student_ledger.php         <- admin's one-page manual student entry + attribution
├── admin_master.php           <- manage universities (global) + courses/seat caps (scoped)
├── course_fees.php            <- set semester-wise fee amounts for one course
├── admin_activity.php         <- full audit trail / activity log
├── staff_dashboard.php        <- staff home with their own stats (scoped)
├── register_student.php       <- the 5-step "Apply Fresh" wizard (scoped, with seat check)
├── re_registration.php        <- search + re-register an existing student (scoped)
├── submit_fee.php             <- fee submission (manual or upload, shows expected fee)
├── my_students.php            <- staff's own submissions list (scoped)
├── student_detail.php         <- shared detail view (admin sees approve/reject
│                                  + fee verify; staff sees their own record)
└── print_slip.php             <- printable admission slip (A4, letterhead style)
```

"Scoped" above means the page only shows/affects data for whichever
university is currently active in the user's session.

## Notes / things you may want to extend later

- Semesters are computed as `duration_years × 2` — the standard convention.
  If a course doesn't follow that pattern, you can still set whatever number
  of semester fee rows makes sense by editing `courseTotalSemesters()` in
  `config/config.php` for that case, or simply leave unused semester fees at 0.
- Currently one fee entry per semester per student — the `fees` table
  doesn't distinguish which semester a payment was for beyond the student's
  current `semester_no` at time of submission.
- Email/SMS notifications on approval or fee verification aren't wired up —
  the codebase is structured so you could drop in a mail call in
  `student_detail.php` and `admin_fees.php` where status changes happen
  (right next to the `logActivity()` calls).

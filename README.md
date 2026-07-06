# Student Admission Portal

A complete PHP + MySQL admin panel for managing student admissions — multi-step
registration, fee collection, role-based staff logins, and a premium,
fully responsive design (mobile / tablet / laptop).

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

## Features

- **Admin login** — full access to everything below.
- **Staff logins** — created by admin; can only register students, submit fees,
  and view their own submitted forms (not other staff's).
- **Multi-step registration wizard** (5 steps): Personal Details → Contact &
  Address → Guardian Details → Academic Background → Course Selection & Review.
  Shows live seat-availability while picking a course.
- **View all submissions** with filters: course, university, session/year,
  status, staff, gender, date range, and free-text search — plus a topbar
  quick-search from anywhere in the portal.
- **Bulk approve/reject** — select multiple registrations in the list and
  action them together.
- **CSV export** of the filtered registration list.
- **Fee submission** — a separate step after registration succeeds. Staff can
  either enter fee details manually (cash/cheque, no proof) or upload a
  payment proof (UTR number and/or screenshot). Admin verifies or rejects
  each fee entry.
- **Registration review** — admin can approve/reject a submitted registration.
- **Printable admission slip** — a clean, letterhead-style A4 page per student
  (photo, course, fee status) that staff/admin can print or save as PDF.
- **Seat capacity tracking** — set a total seat count per course; the portal
  shows filled/available seats on Master Data and during registration.
- **Dashboard analytics** — a 14-day registration trend chart and a
  registrations-by-course chart, plus a live activity feed.
- **Activity log** — a full audit trail of registrations, approvals,
  rejections, fee submissions/verifications, and staff account creation.
- **Master data management** — admin can add/disable courses, universities,
  and sessions/years, which populate the dropdowns used in the form and filters.

## Setup (XAMPP / local)

### Fresh install

1. Copy the `admission-portal` folder into your `htdocs` directory (or your
   web server's document root).
2. Open **phpMyAdmin**, create nothing manually — instead import
   `database/schema.sql` directly (it creates the `admission_portal`
   database itself, including the new seat-capacity and activity-log tables).
3. Open `config/config.php` and update DB credentials if needed (defaults to
   `root` with no password, standard XAMPP setup).
4. If you host the project in a subfolder (e.g. `http://localhost/admission-portal`),
   set `BASE_URL` in `config/config.php` to `/admission-portal`. If it's the
   webroot itself, leave it as `''`.
5. Make sure the `uploads/` folder (and its subfolders `photos`, `marksheets`,
   `fee_proofs`) are writable by the web server.
6. Visit `login.php` in your browser.

### Updating an existing install (you already had the portal running)

Just replace your PHP/CSS/JS files with the ones in this package, then run
`database/migration_v2.sql` in phpMyAdmin against your existing
`admission_portal` database — it adds the `total_seats` column to `courses`
and creates the new `activity_log` table without touching your existing data.

## Default login

```
Username: admin
Password: Admin@123
```

**Change this immediately** — once logged in, click your name in the top-right
corner → **Change Password**. This works for both admin and staff accounts.

## Roles at a glance

| Action                          | Admin | Staff |
|----------------------------------|:-----:|:-----:|
| Register a new student           |   —   |   ✅   |
| View all registrations           |   ✅   |   —   |
| View only their own registrations|   —   |   ✅   |
| Filter/search registrations      |   ✅   |   ✅ (own only) |
| Approve/reject a registration    |   ✅   |   —   |
| Submit fee for a student they registered | — | ✅ |
| Verify/reject a fee submission   |   ✅   |   —   |
| Create/disable staff accounts    |   ✅   |   —   |
| Manage courses/universities/sessions | ✅ | — |

## Folder structure

```
admission-portal/
├── config/config.php          <- DB connection + auth helpers (edit DB creds here)
├── database/schema.sql        <- import this for a fresh install
├── database/migration_v2.sql  <- run this if upgrading an existing install
├── includes/                  <- shared header/footer/sidebar
├── assets/css/style.css       <- all styling (design tokens at the top)
├── assets/js/script.js        <- sidebar drawer, fee form toggle, bulk-select
├── uploads/                   <- photos, marksheets, fee proofs get saved here
├── login.php / logout.php / index.php
├── admin_dashboard.php        <- admin home: stats, charts, activity feed
├── admin_students.php         <- all registrations, filters, bulk actions, CSV export
├── admin_fees.php             <- fee verification queue
├── admin_users.php            <- create/disable staff, reset passwords
├── admin_master.php           <- manage courses/universities/sessions + seat caps
├── admin_activity.php         <- full audit trail / activity log
├── staff_dashboard.php        <- staff home with their own stats
├── register_student.php       <- the 5-step registration wizard (with seat check)
├── submit_fee.php             <- fee submission (manual or upload)
├── my_students.php            <- staff's own submissions list
├── student_detail.php         <- shared detail view (admin sees approve/reject
│                                  + fee verify; staff sees their own record)
└── print_slip.php             <- printable admission slip (A4, letterhead style)
```

## Notes / things you may want to extend later

- Currently one fee entry per student (no installments) — this matches what
  was requested, but the `fees` table is structured so you could add an
  `installment_no` column later without much rework.
- Email/SMS notifications on approval or fee verification aren't wired up —
  the codebase is structured so you could drop in a mail call in
  `student_detail.php` and `admin_fees.php` where status changes happen
  (right next to the `logActivity()` calls).

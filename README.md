# Student Admission Portal

A complete PHP + MySQL admin panel for managing student admissions — multi-step
registration, fee collection, and role-based staff logins.

## Features

- **Admin login** — full access to everything below.
- **Staff logins** — created by admin; can only register students, submit fees,
  and view their own submitted forms (not other staff's).
- **Multi-step registration wizard** (5 steps): Personal Details → Contact &
  Address → Guardian Details → Academic Background → Course Selection & Review.
- **View all submissions** with filters: course, university, session/year,
  status, staff, gender, date range, and free-text search.
- **Fee submission** — a separate step after registration succeeds. Staff can
  either enter fee details manually (cash/cheque, no proof) or upload a
  payment proof (UTR number and/or screenshot). Admin verifies or rejects
  each fee entry.
- **Registration review** — admin can approve/reject a submitted registration.
- **Master data management** — admin can add/disable courses, universities,
  and sessions/years, which populate the dropdowns used in the form and filters.

## Setup (XAMPP / local)

1. Copy the `admission-portal` folder into your `htdocs` directory (or your
   web server's document root).
2. Open **phpMyAdmin**, create nothing manually — instead import
   `database/schema.sql` directly (it creates the `admission_portal`
   database itself).
3. Open `config/config.php` and update DB credentials if needed (defaults to
   `root` with no password, standard XAMPP setup).
4. If you host the project in a subfolder (e.g. `http://localhost/admission-portal`),
   set `BASE_URL` in `config/config.php` to `/admission-portal`. If it's the
   webroot itself, leave it as `''`.
5. Make sure the `uploads/` folder (and its subfolders `photos`, `marksheets`,
   `fee_proofs`) are writable by the web server.
6. Visit `login.php` in your browser.

## Default login

```
Username: admin
Password: Admin@123
```

**Change this password immediately** — go to Manage Staff... actually the
admin account itself doesn't have a UI to change its own password yet; the
simplest fix is to open phpMyAdmin, generate a new bcrypt hash for your new
password (e.g. using PHP's `password_hash()`), and update the `password`
column on the `admin` row in the `users` table.

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
├── database/schema.sql        <- import this to create the DB
├── includes/                  <- shared header/footer/sidebar
├── assets/css/style.css       <- all styling
├── assets/js/script.js        <- sidebar toggle + fee form toggle
├── uploads/                   <- photos, marksheets, fee proofs get saved here
├── login.php / logout.php / index.php
├── admin_dashboard.php        <- admin home with stats
├── admin_students.php         <- all registrations + filters
├── admin_fees.php             <- fee verification queue
├── admin_users.php            <- create/disable staff, reset passwords
├── admin_master.php           <- manage courses/universities/sessions
├── staff_dashboard.php        <- staff home with their own stats
├── register_student.php       <- the 5-step registration wizard
├── submit_fee.php             <- fee submission (manual or upload)
├── my_students.php            <- staff's own submissions list
└── student_detail.php         <- shared detail view (admin sees approve/reject
                                   + fee verify; staff sees their own record)
```

## Notes / things you may want to extend later

- Currently one fee entry per student (no installments) — this matches what
  was requested, but the `fees` table is structured so you could add an
  `installment_no` column later without much rework.
- CSV/Excel export of the filtered registration list isn't included yet —
  easy to add to `admin_students.php` if needed.
- Email/SMS notifications on approval or fee verification aren't wired up —
  the codebase is structured so you could drop in a mail call in
  `student_detail.php` and `admin_fees.php` where status changes happen.

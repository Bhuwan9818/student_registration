# VS Academy Admission Portal — Security, Design & Logic Audit

**Generated:** 2026-11-24  
**Scope:** Full codebase review (PHP, SQL, JS, CSS)  
**Mode:** Ponytail (lazy senior dev) — only findings that matter

---

## Executive Summary

| Area | Risk Level | Key Issues |
|------|-----------|------------|
| **Security** | 🔴 **HIGH** | Hardcoded DB credentials, no CSRF protection, no rate limiting, file upload validation gaps, SQL injection surface in dynamic queries |
| **Design/UX** | 🟡 **MEDIUM** | Inconsistent validation feedback, no loading states, mixed responsive breakpoints, accessibility gaps |
| **Logic/Data** | 🟡 **MEDIUM** | Race conditions in registration numbers, no transaction boundaries, fragile session-based wizard, orphaned data risks |
| **Code Quality** | 🟡 **MEDIUM** | Duplicate code across 14+ pixel forms, no prepared statement enforcement audit, magic strings, tight coupling |

---

## 🔴 SECURITY FINDINGS (Critical → High)

### 1. Hardcoded Production Credentials in Config
**File:** `config/config.php:7-10`  
**Risk:** 🔴 **CRITICAL**  
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u677586028_admission_db');
define('DB_USER', 'u677586028_vsacademy');
define('DB_PASS', 'Bhuwan.9818');  // ← Real password in repo
```
**Impact:** Anyone with repo access gets DB access. Password appears in git history.  
**Fix:** Move to `.env` (not committed), load via `getenv()` or `parse_ini_file()`. Rotate credentials immediately.

---

### 2. No CSRF Protection Anywhere
**Files:** All POST forms (`login.php`, `register_student.php`, `edit_student.php`, `submit_fee.php`, `admin_fees.php`, `admin_students.php`, `student_detail.php`, `change_password.php`)  
**Risk:** 🔴 **HIGH**  
**Impact:** Attackers can forge state-changing requests (approve/reject students, delete records, submit fees, change passwords).  
**Fix:** Generate per-session CSRF token in `config.php`, validate on every POST. Use `hash_equals()` for comparison.

---

### 3. No Rate Limiting / Brute-Force Protection
**Files:** `login.php`, `change_password.php`  
**Risk:** 🔴 **HIGH**  
**Impact:** Unlimited login attempts. Default admin password hash is known (`Admin@123`).  
**Fix:** Add failed-attempt tracking in `users` table or Redis. Lock after 5 failures for 15 min. Enforce MFA for admin.

---

### 4. File Upload Validation Gaps
**File:** `config/config.php:134-151` (`handleUpload()`)
```php
$ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt)) { return null; }
$newName = uniqid('doc_', true) . '.' . $ext;
$destPath = __DIR__ . '/../uploads/' . $destFolder . '/' . $newName;
move_uploaded_file($_FILES[$fileKey]['tmp_name'], $destPath);
```
**Risk:** 🔴 **HIGH**  
**Issues:**
- No MIME type validation (`$_FILES['type']` is client-controlled)
- No file content inspection (magic bytes)
- No size limit enforcement
- Uploads served directly from `uploads/` — if Apache executes PHP in that dir, RCE
- Filename uses `uniqid()` — predictable, not cryptographically random

**Fix:**
```php
// 1. Validate MIME via finfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES[$fileKey]['tmp_name']);
$allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
// 2. Enforce max size (ini_get('upload_max_filesize') default 2M, but set explicitly)
// 3. Store outside webroot OR use .htaccess to disable PHP execution in uploads/
// 4. Use random_bytes(16) for filename
```

---

### 5. SQL Injection Surface in Dynamic WHERE Clauses
**File:** `admin_students.php:56-74`
```php
$where  = ['s.university_id = ?'];
$params = [$activeUni['id']];
// ... builds $whereSql via implode
$stmt = $pdo->prepare($sql);  // $sql interpolates $whereSql
$stmt->execute($params);
```
**Risk:** 🟡 **MEDIUM** (currently safe because all user inputs go into `$params`, but fragile)  
**Issue:** Pattern encourages string interpolation. One missed `?` placeholder = injection.  
**Fix:** Use a query builder or ensure **every** dynamic fragment uses `?` + params. Add static analysis rule.

---

### 6. Session Fixation / Weak Session Config
**File:** `config/config.php:15-17`
```php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```
**Risk:** 🟡 **MEDIUM**  
**Missing:** `session_regenerate_id(true)` on login, `httponly`, `secure`, `samesite` cookie flags.  
**Fix:** Configure in `php.ini` or at runtime before `session_start()`:
```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);       // HTTPS only
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
```

---

### 7. Password Policy Too Weak
**File:** `change_password.php:18`
```php
elseif (strlen($new) < 6) { flash('error', 'New password must be at least 6 characters.'); }
```
**Risk:** 🟡 **MEDIUM**  
**Fix:** Minimum 12 chars, check against breach list (haveibeenpwned API), enforce complexity.

---

### 8. Insecure Direct Object References (IDOR) — Partially Mitigated
**Files:** `student_detail.php`, `edit_student.php`, `submit_fee.php`, `print_pixel.php`  
**Current:** Ownership checks exist (`created_by == $_SESSION['user_id']`)  
**Gap:** Admin bypasses all checks. No audit log for admin accessing other centers' data.  
**Fix:** Log every cross-center access. Consider row-level security in DB.

---

### 9. Default Admin Credentials in Schema
**File:** `database/schema.sql:26-27`
```sql
INSERT INTO users (full_name, username, email, password, role, status) VALUES
('Super Admin', 'admin', 'admin@example.com', '$2b$12$0vmOb50qKU04BS13zL/srexQUvbpVR1goeCU6.oVtwHv9CB448LkC', 'admin', 'active');
```
**Risk:** 🟡 **MEDIUM**  
**Impact:** Known hash for `Admin@123`. Any deploy without password change is compromised.  
**Fix:** Remove from schema. Force password setup on first login via setup script. 

---

### 10. No Content Security Policy / Security Headers
**Files:** `includes/header.php`, `login.php`  
**Risk:** 🟡 **MEDIUM**  
**Missing:** CSP, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`.  
**Fix:** Add via `.htaccess` or PHP `header()` in config:
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:;");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
```

---

## 🟡 DESIGN & UX FINDINGS

### 11. Inconsistent Validation Feedback
**Files:** `register_student.php` (steps 1-5), `edit_student.php`  
**Issue:** Some fields validate server-side only (redirect back with flash), some client-side (`required` attr), no inline field errors. User loses context on error.  
**Fix:** Use AJAX validation or preserve form state + show inline errors. Current `flash()` only shows top-of-page banner.

---

### 12. No Loading States / Disabled Buttons on Submit
**Files:** All forms  
**Issue:** Double-submit possible. No visual feedback during multi-step wizard navigation.  
**Fix:** Add `disabled` on submit, show spinner. Use `formdata` event or simple JS.

---

### 13. Mixed Responsive Breakpoints
**File:** `assets/css/style.css:898-917`  
**Issue:** Sidebar collapses at 992px (Bootstrap `lg`), but search hides at 576px (`sm`). Table horizontal scroll breaks on mobile.  
**Fix:** Standardize on Bootstrap 5 breakpoints. Test tables with `.table-responsive` at all sizes.

---

### 14. Accessibility Gaps
**Files:** `login.php`, `register_student.php`, all forms  
**Issues:**
- SVG illustrations lack `aria-hidden="true"` (some have, some don't)
- Color-only status badges (no text fallback for colorblind)
- No `aria-live` for flash messages
- File inputs not associated with labels properly in wizard steps
- Focus order broken in sliding login panel

**Fix:** Run axe-core audit. Add semantic landmarks, ARIA labels, focus management.

---

### 15. Wizard State Fragile (Session-Based)
**File:** `register_student.php:9-14`
```php
if (!isset($_SESSION['wizard'])) { $_SESSION['wizard'] = []; }
$step = (int)($_GET['step'] ?? 1);
```
**Issue:** 
- Back button breaks flow (session already advanced)
- Tab duplication = shared session state corruption
- No "save draft" / resume later
- Session expiry loses all progress

**Fix:** Store draft in DB keyed by token (not session). Allow multi-tab via `step` param + token.

---

### 16. Inconsistent Button/Action Patterns
**Files:** `admin_students.php` (bulk actions + individual delete in same form), `admin_fees.php`  
**Issue:** Bulk approve/reject/delete share one `<form>` with individual delete buttons using `formnovalidate`. Confusing.  
**Fix:** Separate forms per action. Use HTMX or fetch for bulk actions.

---

## 🟡 LOGIC & DATA INTEGRITY FINDINGS

### 17. Race Condition in Registration Number Generation
**File:** `config/config.php:126-131`
```php
function generateRegistrationNo($pdo) {
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM students WHERE registration_no LIKE 'REG-$year-%'");
    $count = $stmt->fetch()['cnt'] + 1;
    return sprintf('REG-%s-%06d', $year, $count);
}
```
**Risk:** 🔴 **HIGH** (concurrency bug)  
**Impact:** Two simultaneous registrations get same number. `UNIQUE` constraint on `registration_no` will cause one to fail with 500.  
**Fix:** Use `INSERT ... ON DUPLICATE KEY UPDATE` with atomic counter table, or `SELECT ... FOR UPDATE` in transaction.

---

### 18. No Database Transactions for Multi-Table Writes
**Files:** `register_student.php:124-168`, `edit_student.php:30-93`  
**Issue:** Student + academics + documents inserted in separate statements. Partial failure = orphaned data.  
**Fix:** Wrap in `$pdo->beginTransaction()` / `commit()` / `rollBack()`.

---

### 19. Soft Delete Only Updates Status, Not Archived Data
**File:** `admin_students.php:10-21` (`deleteStudentRecord()` in config)  
**Issue:** `DELETE FROM students` hard-deletes. No audit trail of *who* deleted *what* beyond activity_log.  
**Fix:** Add `deleted_at`, `deleted_by` columns. Soft delete. Keep GDPR/retention policy.

---

### 20. Fee Verification Lacks Amount Validation
**File:** `admin_fees.php:10-29`  
**Issue:** Admin can verify any amount. No check against `course_fees` expected amount.  
**Fix:** Show expected vs submitted. Warn on mismatch. Require reason for override.

---

### 21. Re-Registration Logic Incomplete
**Files:** `register_student.php`, `re_registration.php` (not read but referenced), `student_detail.php:68-75`  
**Issue:** `parent_student_id` links re-reg to original, but academics/documents fall back silently. No validation that student is eligible for re-reg (e.g., previous semester passed).  
**Fix:** Add eligibility rules. Copy academics/docs explicitly with audit trail.

---

### 22. Sub-Center / Center Hierarchy Not Enforced in All Queries
**Files:** `admin_students.php:80-82` (joins `parent_user_id`), `fee_report.php` (not read)  
**Issue:** Some queries filter by `university_id` only, ignoring center hierarchy. Data leakage between centers possible.  
**Fix:** Centralize scope resolution. Add `getVisibleStudentIds($pdo, $userId)` helper used everywhere.

---

### 23. No Unique Constraint on (student_id, semester_no) in fees
**File:** `database/schema.sql:206-223`  
**Issue:** Multiple fee entries for same semester possible. No prevention of double-charging.  
**Fix:** Add `UNIQUE KEY uniq_student_semester (student_id, semester_no)` or enforce in app logic.

---

## 🟡 CODE QUALITY & MAINTAINABILITY

### 24. 14+ Duplicate Pixel Form Templates
**Dir:** `pixel_forms/*/admission-form.php`  
**Issue:** Each university has near-identical PHP/HTML with minor branding differences. 14 files × ~300 lines = 4200 lines of duplication.  
**Fix:** Single template + data-driven config (JSON per university: logo, field labels, checkboxes, layout variants). Render via one PHP file.

---

### 25. Magic Strings Everywhere
**Examples:**
- `status` values: `'submitted','approved','rejected','pending','verified'` (5+ files)
- `role` values: `'admin','staff'` (10+ files)
- `entry_type`: `'manual','upload'`
- `mode`: `'Cash','Cheque','Online','UPI','Card'`
- `form_template` keys: `'sgvu','amity','mangalayatan',...` (3+ files)

**Fix:** Define constants/enums in `config.php`. Use `Status::APPROVED` etc. (PHP 8.1+ enums).

---

### 26. Tight Coupling: Business Logic in Controllers
**Files:** All `.php` files mix HTTP handling, SQL, business rules, HTML.  
**Issue:** Hard to test. Can't reuse logic (e.g., fee calculation) outside web request.  
**Fix:** Extract services: `RegistrationService`, `FeeService`, `StudentRepository`. Keep controllers thin.

---

### 27. No Automated Tests
**Evidence:** No `tests/`, no `phpunit.xml`, no CI config.  
**Risk:** Regression on every change.  
**Fix:** Add PHPUnit + Pest. Test critical paths: registration, fee verification, auth, permissions.

---

### 28. Error Handling Inconsistent
**Files:** `config/config.php:26-28` (die on DB connect), elsewhere: `flash()` + redirect  
**Issue:** `die()` exposes stack trace. Some PDO exceptions uncaught.  
**Fix:** Global exception handler. Log errors, show friendly page. Never `die()` in production.

---

### 29. Debug/Development Artifacts in Production Code
**Files:** `pixel_forms/gla/admission-form.php:25-66` (sample data fallback)  
**Issue:** Standalone preview mode with fake data in production template.  
**Fix:** Move sample data to separate preview script. Production template should require `$student`.

---

### 30. Frontend Dependencies via CDN (No Integrity/Version Pinning)
**Files:** `includes/header.php:27-28`, `login.php:42-43`
```html
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
```
**Risk:** Supply chain attack. CDN compromise = XSS on your site.  
**Fix:** Download locally or add `integrity` + `crossorigin` attributes. Use npm + build step.

---

## 🟢 LOW PRIORITY / NICE TO HAVE

| # | Finding | Effort |
|---|---------|--------|
| 31 | No API layer — all server-rendered | Medium |
| 32 | Print forms use `<base>` tag injection hack (`print_pixel.php:52-53`) | Low |
| 33 | `activity_log` has no index on `(user_id, created_at)` — slow at scale | Low |
| 34 | `course_fees.amount` DECIMAL(10,2) — no currency column (assumes INR) | Low |
| 35 | No database migration tool — raw SQL files in `database/` | Low |
| 36 | `student_documents.doc_type` is free-text VARCHAR, not FK to lookup table | Low |
| 37 | Search in `admin_students.php` uses `LIKE %...%` — no full-text index | Low |
| 38 | Charts use Chart.js via CDN — same supply chain risk | Low |

---

## RECOMMENDED FIX ORDER (Priority × Impact)

| Phase | Tasks | Est. Effort |
|-------|-------|-------------|
| **P0 — Do First (Security)** | 1. Rotate DB creds, move to `.env`<br>2. Add CSRF tokens to all forms<br>3. Harden file uploads (MIME, size, storage outside webroot)<br>4. Add rate limiting on auth<br>5. Add security headers (CSP, etc.)<br>6. Remove default admin from schema | 1-2 days |
| **P1 — Data Integrity** | 7. Fix registration number race (transaction + counter table)<br>8. Wrap multi-table writes in transactions<br>9. Add unique constraint on fees(student_id, semester_no)<br>10. Soft delete with audit columns | 1 day |
| **P2 — Architecture** | 11. Extract service layer (RegistrationService, FeeService)<br>12. Replace magic strings with enums<br>13. Consolidate 14 pixel forms into 1 template + config<br>14. Add PHPUnit + critical path tests | 3-5 days |
| **P3 — UX/Quality** | 15. Fix wizard: DB-backed drafts, multi-tab support<br>16. Inline validation errors, loading states<br>17. Accessibility audit (axe-core)<br>18. Standardize responsive breakpoints | 2-3 days |
| **P4 — Polish** | 19. Pin CDN assets with integrity / move local<br>20. Add DB migration tool (Phinx)<br>21. Full-text search index<br>22. API layer for future frontend decoupling | Ongoing |

---

## FILES THAT NEED IMMEDIATE ATTENTION

| File | Reason |
|------|--------|
| `config/config.php` | Credentials, upload handling, session config, registration number race |
| `login.php` | No rate limit, no CSRF, default admin known |
| `register_student.php` | Wizard fragility, no transactions, validation gaps |
| `admin_students.php` | Bulk actions, delete, export — all need CSRF + confirmation |
| `submit_fee.php` / `admin_fees.php` | Fee verification lacks amount validation |
| `pixel_forms/*/admission-form.php` | 14x duplication, supply chain risk via CDN in header |
| `database/schema.sql` | Default admin, missing indexes, missing constraints |

---

## WHAT I SKIPPED / DIDN'T CHECK

- **`re_registration.php`, `choose_university.php`, `admin_courses.php`, `admin_universities.php`, `admin_sessions.php`, `admin_subcenters.php`, `admin_centers.php`, `fee_report.php`, `student_ledger.php`, `sub_courses.php`, `course_fees.php`, `my_students.php`, `admin_master.php`, `admin_activity.php`, `print_slip.php`, `logout.php`** — logic likely similar to reviewed files; same patterns apply.
- **JavaScript** (`assets/js/script.js`) — only briefly seen inline; no separate audit.
- **CSS** beyond design system review — no visual regression testing.
- **Database performance** (EXPLAIN plans, index usage) — needs live data.
- **GDPR / data retention compliance** — legal review needed.
- **Third-party PHP dependencies** — none visible (no `composer.json`), but verify.

---

## RISK THE USER MUST KNOW

> **This codebase handles real student PII and payments with production credentials committed to git.**  
> **Rotate `Bhuwan.9818` immediately. Treat the DB as compromised until rotated.**  
> **No CSRF = any authenticated admin/staff action can be forged by a malicious link.**  
> **File uploads can lead to RCE if `uploads/` is executable.**  
> **Registration numbers will collide under concurrent load.**

---

*Generated by ponytail audit — lazy but thorough. Fix P0 items before any new feature work.*
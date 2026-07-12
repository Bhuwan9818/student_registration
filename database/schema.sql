-- ============================================================
-- Student Registration / Admission Portal - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS admission_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE admission_portal;

-- ------------------------------------------------------------
-- Users (Admin + Staff/Counsellor logins)
-- ------------------------------------------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) DEFAULT NULL,
  phone VARCHAR(15) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  parent_user_id INT DEFAULT NULL,   -- if set, this "staff" account is a Sub-Center under that Center
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Default admin login -> username: admin | password: Admin@123 (change after first login)
INSERT INTO users (full_name, username, email, password, role, status) VALUES
('Super Admin', 'admin', 'admin@example.com', '$2b$12$0vmOb50qKU04BS13zL/srexQUvbpVR1goeCU6.oVtwHv9CB448LkC', 'admin', 'active');

-- ------------------------------------------------------------
-- Universities
-- ------------------------------------------------------------
CREATE TABLE universities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Courses — each course belongs to exactly one university
-- ------------------------------------------------------------
CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  university_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  duration_years INT DEFAULT 1,
  total_seats INT DEFAULT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (university_id) REFERENCES universities(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Semester-wise fee structure — one row per course per semester
-- (number of semesters = duration_years * 2, standard convention)
-- ------------------------------------------------------------
CREATE TABLE course_fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  semester_no INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_course_sem (course_id, semester_no),
  FOREIGN KEY (course_id) REFERENCES courses(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Sessions / academic years
-- ------------------------------------------------------------
CREATE TABLE sessions_years (
  id INT AUTO_INCREMENT PRIMARY KEY,
  year_label VARCHAR(20) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---- Seed data: a couple of real-shaped universities with their own courses ----
INSERT INTO universities (name) VALUES ('Delhi University'), ('IGNOU'), ('IP University');

INSERT INTO courses (university_id, name, duration_years, total_seats) VALUES
  (1, 'B.Tech (Computer Science)', 4, 120),
  (1, 'B.Tech (Mechanical)', 4, 90),
  (1, 'BBA', 3, 60),
  (1, 'BCA', 3, 60),
  (1, 'MBA', 2, 40),
  (2, 'BA (Programme)', 3, 200),
  (2, 'MA (English)', 2, 80),
  (3, 'B.Com (Hons)', 3, 80),
  (3, 'BCA', 3, 60);

-- Example semester-wise fee structure for a couple of courses (admin can edit/add the rest)
INSERT INTO course_fees (course_id, semester_no, amount) VALUES
  (1, 1, 55000), (1, 2, 55000), (1, 3, 58000), (1, 4, 58000),
  (1, 5, 60000), (1, 6, 60000), (1, 7, 62000), (1, 8, 62000),
  (3, 1, 35000), (3, 2, 35000), (3, 3, 37000), (3, 4, 37000), (3, 5, 38000), (3, 6, 38000);

INSERT INTO sessions_years (year_label) VALUES ('2025-2026'), ('2026-2027');

-- ------------------------------------------------------------
-- Students (the multi-step registration form data)
-- ------------------------------------------------------------
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  registration_no VARCHAR(30) UNIQUE NOT NULL,
  registration_type ENUM('fresh','re-registration') NOT NULL DEFAULT 'fresh',
  parent_student_id INT DEFAULT NULL,          -- for re-registration: points to the earlier record
  created_by INT NOT NULL,               -- staff/admin user who filled the form

  -- Step 1: Personal details
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) DEFAULT NULL,
  dob DATE DEFAULT NULL,
  gender ENUM('Male','Female','Other') DEFAULT NULL,
  category ENUM('General','OBC','SC','ST','EWS','Other') DEFAULT NULL,
  aadhar_no VARCHAR(20) DEFAULT NULL,
  photo_path VARCHAR(255) DEFAULT NULL,

  -- Step 2: Contact & address
  mobile VARCHAR(15) NOT NULL,
  alt_mobile VARCHAR(15) DEFAULT NULL,
  email VARCHAR(100) DEFAULT NULL,
  address TEXT,
  city VARCHAR(50) DEFAULT NULL,
  state VARCHAR(50) DEFAULT NULL,
  pincode VARCHAR(10) DEFAULT NULL,

  -- Step 3: Guardian details
  father_name VARCHAR(100) DEFAULT NULL,
  mother_name VARCHAR(100) DEFAULT NULL,
  guardian_mobile VARCHAR(15) DEFAULT NULL,

  -- Step 4: Academic background
  last_qualification VARCHAR(100) DEFAULT NULL,
  board_university VARCHAR(150) DEFAULT NULL,
  passing_year VARCHAR(10) DEFAULT NULL,
  percentage VARCHAR(10) DEFAULT NULL,
  marksheet_path VARCHAR(255) DEFAULT NULL,

  -- Step 5: Course applying for
  university_id INT DEFAULT NULL,
  course_id INT DEFAULT NULL,
  session_id INT DEFAULT NULL,
  semester_no INT NOT NULL DEFAULT 1,     -- which semester this registration covers

  status ENUM('submitted','approved','rejected') NOT NULL DEFAULT 'submitted',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (created_by) REFERENCES users(id),
  FOREIGN KEY (university_id) REFERENCES universities(id),
  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (session_id) REFERENCES sessions_years(id),
  FOREIGN KEY (parent_student_id) REFERENCES students(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Fees (submitted after registration, manual entry OR proof upload)
-- ------------------------------------------------------------
CREATE TABLE fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  mode ENUM('Cash','Cheque','Online','UPI','Card') NOT NULL,
  entry_type ENUM('manual','upload') NOT NULL,
  utr_no VARCHAR(50) DEFAULT NULL,
  proof_path VARCHAR(255) DEFAULT NULL,
  remarks VARCHAR(255) DEFAULT NULL,
  status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  submitted_by INT NOT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  verified_by INT DEFAULT NULL,
  verified_at TIMESTAMP NULL DEFAULT NULL,

  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (submitted_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Activity log (dashboard activity feed / lightweight audit trail)
-- ------------------------------------------------------------
CREATE TABLE activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  student_id INT DEFAULT NULL,
  action VARCHAR(50) NOT NULL,
  description VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB;

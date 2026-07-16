-- ============================================================
-- Migration v7: Extended registration form
--   - New personal/contact fields on students (ABC ID, DEB ID,
--     employment status, marital status, religion, nationality,
--     specialization, alternate email, district)
--   - Multi-level academic history (10th/12th/UG/PG) in its own table
--   - Multiple document uploads in their own table
-- Run this on an EXISTING admission_portal database.
-- (If you're setting up fresh, use database/schema.sql instead —
-- it already includes this.)
-- ============================================================

USE admission_portal;

ALTER TABLE students
  ADD COLUMN abc_id VARCHAR(20) DEFAULT NULL AFTER aadhar_no,
  ADD COLUMN deb_id VARCHAR(20) DEFAULT NULL AFTER abc_id,
  ADD COLUMN employment_status VARCHAR(30) DEFAULT NULL AFTER category,
  ADD COLUMN marital_status VARCHAR(20) DEFAULT NULL AFTER employment_status,
  ADD COLUMN religion VARCHAR(30) DEFAULT NULL AFTER marital_status,
  ADD COLUMN nationality VARCHAR(50) DEFAULT 'Indian' AFTER religion,
  ADD COLUMN specialization VARCHAR(100) DEFAULT NULL AFTER course_id,
  ADD COLUMN alt_email VARCHAR(100) DEFAULT NULL AFTER email,
  ADD COLUMN district VARCHAR(50) DEFAULT NULL AFTER city;

CREATE TABLE IF NOT EXISTS student_academics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  level ENUM('10th','12th','UG','PG') NOT NULL,
  institution_board VARCHAR(150) DEFAULT NULL,
  year_of_passing VARCHAR(10) DEFAULT NULL,
  percentage VARCHAR(10) DEFAULT NULL,
  marksheet_path VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  doc_type VARCHAR(50) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB;

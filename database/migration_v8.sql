-- ============================================================
-- Migration v8: Sub-Courses (dependent on Courses, which are
-- dependent on Universities)
-- Run this on an EXISTING admission_portal database.
-- (If you're setting up fresh, use database/schema.sql instead —
-- it already includes this. Note: student_academics.marksheet_path
-- already existed from migration_v7, so no change needed there —
-- the per-level marksheet upload feature just starts using it.)
-- ============================================================

USE admission_portal;

CREATE TABLE IF NOT EXISTS sub_courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES courses(id)
) ENGINE=InnoDB;

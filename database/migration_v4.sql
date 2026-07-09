-- ============================================================
-- Migration v4: Real multi-university data model
--   - Courses now belong to a specific university (not global)
--   - Semester-wise fee structure per course
--   - Students track which semester their registration is for
-- Run this on an EXISTING admission_portal database.
-- (If you're setting up fresh, use database/schema.sql instead —
-- it already includes this.)
-- ============================================================

USE admission_portal;

-- Courses now belong to a university
ALTER TABLE courses ADD COLUMN university_id INT DEFAULT NULL AFTER name;
UPDATE courses SET university_id = (SELECT id FROM universities ORDER BY id LIMIT 1) WHERE university_id IS NULL;
ALTER TABLE courses MODIFY university_id INT NOT NULL;
ALTER TABLE courses ADD CONSTRAINT fk_course_university FOREIGN KEY (university_id) REFERENCES universities(id);

-- Semester-wise fee structure (one row per course per semester)
CREATE TABLE IF NOT EXISTS course_fees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  semester_no INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_course_sem (course_id, semester_no),
  FOREIGN KEY (course_id) REFERENCES courses(id)
) ENGINE=InnoDB;

-- Track which semester a registration/re-registration is for
ALTER TABLE students ADD COLUMN semester_no INT NOT NULL DEFAULT 1 AFTER session_id;

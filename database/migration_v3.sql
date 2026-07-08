-- ============================================================
-- Migration v3: Fresh vs Re-Registration support
-- Run this on an EXISTING admission_portal database.
-- (If you're setting up fresh, use database/schema.sql instead —
-- it already includes this.)
-- ============================================================

USE admission_portal;

ALTER TABLE students ADD COLUMN registration_type ENUM('fresh','re-registration') NOT NULL DEFAULT 'fresh' AFTER registration_no;
ALTER TABLE students ADD COLUMN parent_student_id INT DEFAULT NULL AFTER registration_type;
ALTER TABLE students ADD CONSTRAINT fk_parent_student FOREIGN KEY (parent_student_id) REFERENCES students(id);

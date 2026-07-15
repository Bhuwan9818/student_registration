-- ============================================================
-- Migration v6: University logo upload
--   (Registration deletion doesn't need a schema change — it's
--   handled in application code by cleaning up dependent rows
--   before deleting the student record.)
-- Run this on an EXISTING admission_portal database.
-- (If you're setting up fresh, use database/schema.sql instead —
-- it already includes this.)
-- ============================================================

USE u677586028_admission_db;

ALTER TABLE universities ADD COLUMN logo_path VARCHAR(255) DEFAULT NULL AFTER name;

-- ============================================================
-- Migration v5: Centers & Sub-Centers
--   "Manage Staff" becomes "Centers". A Center account can have
--   Sub-Center accounts under it (created by admin), with identical
--   permissions to a Center, but tracked separately so admin can see
--   which Center each Sub-Center belongs to.
-- Run this on an EXISTING admission_portal database.
-- (If you're setting up fresh, use database/schema.sql instead —
-- it already includes this.)
-- ============================================================

USE admission_portal;

ALTER TABLE users ADD COLUMN parent_user_id INT DEFAULT NULL AFTER role;
ALTER TABLE users ADD CONSTRAINT fk_parent_user FOREIGN KEY (parent_user_id) REFERENCES users(id);

-- Every existing staff account becomes a "Center" by default (parent_user_id stays NULL).
-- No further changes needed — admin can now create Sub-Centers under them from the
-- new "Sub-Centers" page.

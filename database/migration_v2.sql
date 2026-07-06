-- ============================================================
-- Migration: run this on an EXISTING admission_portal database
-- to add the new features (activity log + course seat capacity).
-- If you're setting up fresh, just use database/schema.sql instead
-- (it already includes these changes).
-- ============================================================

USE admission_portal;

-- Seat capacity tracking per course
ALTER TABLE courses ADD COLUMN total_seats INT DEFAULT NULL AFTER duration_years;

-- Activity log (for the dashboard activity feed / audit trail)
CREATE TABLE IF NOT EXISTS activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  student_id INT DEFAULT NULL,
  action VARCHAR(50) NOT NULL,
  description VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB;

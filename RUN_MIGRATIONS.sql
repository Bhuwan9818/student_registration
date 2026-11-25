-- ============================================================
-- Combined Migration Script - Run this in your database
-- ============================================================

-- ============================================================
-- Migration v9: Center Fee Sharing Configuration
-- ============================================================

-- Table to store center sharing/discount percentages
-- sharing_percentage = % discount the center gets on course fee
-- e.g., 60% sharing = center pays 40% of course fee
CREATE TABLE center_sharing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    center_id INT NOT NULL,                    -- The center (staff user with parent_user_id IS NULL)
    course_id INT DEFAULT NULL,                -- NULL = default for all courses
    sharing_percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,  -- % discount center gets (0-100)
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (center_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,

    -- Ensure one default per center, and one per course per center
    UNIQUE KEY uniq_center_course (center_id, course_id)
) ENGINE=InnoDB;

-- Index for faster lookups
CREATE INDEX idx_center_sharing_center ON center_sharing(center_id);
CREATE INDEX idx_center_sharing_course ON center_sharing(course_id);

-- ============================================================
-- Migration v10: Fee Frequency Support (yearly | semester)
-- ============================================================

-- Add fee_frequency to universities table
ALTER TABLE universities
ADD COLUMN fee_frequency ENUM('yearly','semester') NOT NULL DEFAULT 'semester' AFTER status;

-- Add fee_frequency to courses table (NULL = inherit from university)
ALTER TABLE courses
ADD COLUMN fee_frequency ENUM('yearly','semester') DEFAULT NULL AFTER total_seats;

-- Update existing data: default to semester (backward compatible)
UPDATE universities SET fee_frequency = 'semester';
UPDATE courses SET fee_frequency = NULL; -- inherit from university

-- Index for performance
CREATE INDEX idx_universities_fee_freq ON universities(fee_frequency);
CREATE INDEX idx_courses_fee_freq ON courses(fee_frequency);
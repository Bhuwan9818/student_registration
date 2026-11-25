-- ============================================================
-- Center Fee Sharing Configuration (Migration v9) - REVISED
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
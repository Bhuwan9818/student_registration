-- ============================================================
-- Fee Frequency Support (Migration v10)
-- yearly | semester per university and per course
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
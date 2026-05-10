-- ============================================================
-- Migration: Add 'statut' column to 'posts' table
-- Purpose: Track publication status of blog posts
-- ============================================================

-- Check if column doesn't exist before adding it
ALTER TABLE posts ADD COLUMN IF NOT EXISTS statut ENUM('publie', 'brouillon', 'supprime') DEFAULT 'publie' AFTER video;

-- Optional: Update existing posts to have 'publie' status (they should already default to 'publie' if added)
UPDATE posts SET statut = 'publie' WHERE statut IS NULL;

-- Display confirmation
SELECT 'Migration completed: Added statut column to posts table' AS status;

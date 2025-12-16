-- Add versioning support to documents table
-- This migration adds version tracking and file path storage for document history

-- Add new columns to documents table
ALTER TABLE documents ADD COLUMN version_number INT DEFAULT 1 AFTER document_id;
ALTER TABLE documents ADD COLUMN parent_document_id INT AFTER version_number;
ALTER TABLE documents ADD COLUMN file_path VARCHAR(255) AFTER document_type;
ALTER TABLE documents ADD COLUMN file_size INT AFTER file_path;
ALTER TABLE documents ADD COLUMN mime_type VARCHAR(100) AFTER file_size;
ALTER TABLE documents ADD COLUMN is_latest BOOLEAN DEFAULT TRUE AFTER is_template;

-- Create foreign key for version history
ALTER TABLE documents ADD CONSTRAINT fk_parent_document 
FOREIGN KEY (parent_document_id) REFERENCES documents(document_id) ON DELETE CASCADE;

-- Create index for efficient version queries
CREATE INDEX idx_document_versions ON documents(parent_document_id, version_number);
CREATE INDEX idx_document_latest ON documents(faculty_id, is_latest);
CREATE INDEX idx_document_status_version ON documents(status, is_latest);

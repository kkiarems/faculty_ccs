<?php
/**
 * Document Versioning Helper Functions
 * Provides utility functions for managing document versions
 */

/**
 * Get all versions of a document
 * @param int $document_id - The document ID (can be parent or child)
 * @param mysqli $conn - Database connection
 * @return mysqli_result - Result set of all versions
 */
function getDocumentVersions($document_id, $conn) {
    $query = "SELECT * FROM documents 
              WHERE parent_document_id = ? OR document_id = ? 
              ORDER BY version_number DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $document_id, $document_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get the latest version of a document
 * @param int $parent_id - The parent document ID
 * @param mysqli $conn - Database connection
 * @return array - Latest document record
 */
function getLatestVersion($parent_id, $conn) {
    $query = "SELECT * FROM documents 
              WHERE (document_id = ? OR parent_document_id = ?) AND is_latest = TRUE 
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $parent_id, $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get version count for a document
 * @param int $parent_id - The parent document ID
 * @param mysqli $conn - Database connection
 * @return int - Total number of versions
 */
function getVersionCount($parent_id, $conn) {
    $query = "SELECT COUNT(*) as total FROM documents 
              WHERE parent_document_id = ? OR document_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $parent_id, $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

/**
 * Get next version number
 * @param int $parent_id - The parent document ID
 * @param mysqli $conn - Database connection
 * @return int - Next version number
 */
function getNextVersionNumber($parent_id, $conn) {
    $query = "SELECT MAX(version_number) as max_version FROM documents 
              WHERE parent_document_id = ? OR document_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $parent_id, $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return ($row['max_version'] ?? 0) + 1;
}

/**
 * Mark old versions as not latest
 * @param int $parent_id - The parent document ID
 * @param mysqli $conn - Database connection
 * @return bool - Success status
 */
function markOldVersionsAsNotLatest($parent_id, $conn) {
    $query = "UPDATE documents SET is_latest = FALSE 
              WHERE (document_id = ? OR parent_document_id = ?) AND is_latest = TRUE";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $parent_id, $parent_id);
    return $stmt->execute();
}

/**
 * Format file size for display
 * @param int $bytes - File size in bytes
 * @return string - Formatted file size
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return 'N/A';
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Get version status badge HTML
 * @param string $status - Document status
 * @return string - HTML badge
 */
function getStatusBadge($status) {
    $class = 'status-' . htmlspecialchars($status);
    return '<span class="status-badge ' . $class . '">' . ucfirst($status) . '</span>';
}

/**
 * Validate file upload
 * @param array $file - $_FILES array element
 * @param int $max_size - Maximum file size in bytes
 * @return array - ['valid' => bool, 'error' => string]
 */
function validateFileUpload($file, $max_size = 52428800) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'File upload error'];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File size exceeds limit'];
    }
    
    if ($file['size'] == 0) {
        return ['valid' => false, 'error' => 'File is empty'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Get safe file extension
 * @param string $filename - Original filename
 * @return string - File extension
 */
function getSafeFileExtension($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt', 'ppt', 'pptx'];
    return in_array(strtolower($ext), $allowed) ? strtolower($ext) : 'bin';
}
?>

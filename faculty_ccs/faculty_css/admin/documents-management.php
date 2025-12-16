<?php
$page_title = 'Documents Management';
require_once 'header.php';
require_once '../config/database.php';

$message = '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_document'])) {
        $document_id = intval($_POST['document_id']);
        $status = sanitize($_POST['status']);
        
        $update = "UPDATE documents SET status = ?, approved_by = ?, approval_date = NOW() WHERE document_id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("sii", $status, $current_user['admin_id'], $document_id);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Document status updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating document</div>';
        }
    } elseif (isset($_POST['delete_document'])) {
        $document_id = intval($_POST['document_id']);
        
        // Get file path before deleting
        $get_file = $conn->query("SELECT file_path FROM documents WHERE document_id = $document_id");
        $doc = $get_file->fetch_assoc();
        
        // Delete from database
        $delete = "DELETE FROM documents WHERE document_id = ?";
        $stmt = $conn->prepare($delete);
        $stmt->bind_param("i", $document_id);
        
        if ($stmt->execute()) {
            // Delete file if it exists
            if (!empty($doc['file_path']) && file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            $message = '<div class="alert alert-success">Document deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting document</div>';
        }
    }
}

$query = "SELECT d.*, f.name as faculty_name FROM documents d 
          LEFT JOIN faculty f ON d.faculty_id = f.faculty_id";

if ($status_filter !== 'all') {
    $query .= " WHERE d.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query .= " ORDER BY d.uploaded_date DESC";
$documents_list = $conn->query($query);

// Get document statistics
$stats = $conn->query("SELECT status, COUNT(*) as count FROM documents GROUP BY status");
$status_counts = [];
while ($row = $stats->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}
?>

<style>
    .filter-tabs {
        display: flex;
        gap: var(--spacing-md);
        margin-bottom: var(--spacing-xl);
        border-bottom: 2px solid var(--border-color);
        padding-bottom: var(--spacing-md);
    }
    .filter-tab {
        padding: var(--spacing-md) var(--spacing-lg);
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 500;
        transition: var(--transition);
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
    }
    .filter-tab:hover {
        color: var(--text-primary);
    }
    .filter-tab.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
    }
    .filter-tab .count {
        background-color: var(--primary-light);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-left: 6px;
    }
    .document-card {
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }
    .document-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: var(--spacing-md);
    }
    .document-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .status-badge {
        display: inline-block;
        padding: var(--spacing-xs) var(--spacing-md);
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        font-weight: 500;
    }
    .status-pending {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning);
    }
    .status-approved {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }
    .status-rejected {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }
    .document-meta {
        display: flex;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        font-size: 0.9rem;
        color: var(--text-secondary);
        flex-wrap: wrap;
    }
    .document-actions {
        display: flex;
        gap: var(--spacing-md);
        margin-top: var(--spacing-md);
        flex-wrap: wrap;
        align-items: center;
    }
    .btn-action {
        padding: var(--spacing-sm) var(--spacing-md);
        border: none;
        border-radius: var(--radius-md);
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: var(--transition);
    }
    .btn-view {
        background-color: var(--primary);
        color: white;
    }
    .btn-view:hover {
        background-color: var(--primary-dark);
    }
    .btn-approve {
        background-color: var(--success);
        color: white;
    }
    .btn-approve:hover {
        background-color: #059669;
    }
    .btn-reject {
        background-color: var(--danger);
        color: white;
    }
    .btn-reject:hover {
        background-color: #dc2626;
    }
    .btn-delete {
        background-color: #6b7280;
        color: white;
    }
    .btn-delete:hover {
        background-color: #4b5563;
    }
    .btn-disabled {
        background-color: #9ca3af;
        color: white;
        cursor: not-allowed;
    }
    .status-form {
        display: flex;
        gap: var(--spacing-md);
        margin-top: var(--spacing-md);
        align-items: center;
        flex-wrap: wrap;
    }
    .status-form select {
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        background-color: var(--bg-dark);
        color: var(--text-primary);
        font-size: 0.9rem;
    }
    .status-form button {
        padding: var(--spacing-sm) var(--spacing-md);
        background-color: var(--accent);
        color: var(--bg-dark);
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }
    .status-form button:hover {
        background-color: #fbbf24;
    }
</style>

<?php echo $message; ?>

<h3>Documents Management</h3>

<div class="filter-tabs">
    <a href="?status=all" class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
        All Documents
        <span class="count"><?php echo array_sum($status_counts); ?></span>
    </a>
    <a href="?status=pending" class="filter-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
        Pending
        <span class="count"><?php echo $status_counts['pending'] ?? 0; ?></span>
    </a>
    <a href="?status=approved" class="filter-tab <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">
        Approved
        <span class="count"><?php echo $status_counts['approved'] ?? 0; ?></span>
    </a>
    <a href="?status=rejected" class="filter-tab <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">
        Rejected
        <span class="count"><?php echo $status_counts['rejected'] ?? 0; ?></span>
    </a>
</div>

<h4>Documents</h4>

<?php if ($documents_list->num_rows === 0): ?>
<div style="text-align: center; padding: var(--spacing-xl); color: var(--text-secondary);">
    No documents found.
</div>
<?php else: ?>
    <?php while ($document = $documents_list->fetch_assoc()): ?>
    <div class="document-card">
        <div class="document-header">
            <div>
                <div class="document-title"><?php echo htmlspecialchars($document['document_name']); ?></div>
                <?php if (!$document['is_template']): ?>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: var(--spacing-sm);">
                    By: <?php echo htmlspecialchars($document['faculty_name']); ?>
                </div>
                <?php else: ?>
                <div style="color: var(--accent); font-size: 0.9rem; margin-top: var(--spacing-sm);">
                    Template
                </div>
                <?php endif; ?>
            </div>
            <span class="status-badge status-<?php echo $document['status']; ?>">
                <?php echo ucfirst($document['status']); ?>
            </span>
        </div>
        
        <div class="document-meta">
            <span>Type: <?php echo htmlspecialchars($document['document_type']); ?></span>
            <span>Category: <?php echo htmlspecialchars($document['category']); ?></span>
            <span>Uploaded: <?php echo date('M d, Y', strtotime($document['uploaded_date'])); ?></span>
        </div>
        
        <div class="document-actions">
            <?php if (!empty($document['file_path'])): ?>
                <?php if (file_exists($document['file_path'])): ?>
                <a href="download-document.php?id=<?php echo $document['document_id']; ?>" class="btn-action btn-view">View Document</a>
                <?php else: ?>
                <span class="btn-action btn-disabled">File Not Found</span>
                <?php endif; ?>
            <?php else: ?>
            <span class="btn-action btn-disabled">No File</span>
            <?php endif; ?>
            
            <?php if ($document['status'] !== 'approved'): ?>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="document_id" value="<?php echo $document['document_id']; ?>">
                <input type="hidden" name="status" value="approved">
                <button type="submit" name="update_document" class="btn-action btn-approve">✓ Approve</button>
            </form>
            <?php endif; ?>
            
            <?php if ($document['status'] !== 'rejected'): ?>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="document_id" value="<?php echo $document['document_id']; ?>">
                <input type="hidden" name="status" value="rejected">
                <button type="submit" name="update_document" class="btn-action btn-reject">✗ Reject</button>
            </form>
            <?php endif; ?>
            
            <!-- Delete button -->
            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this document?');">
                <input type="hidden" name="document_id" value="<?php echo $document['document_id']; ?>">
                <button type="submit" name="delete_document" class="btn-action btn-delete">Delete</button>
            </form>
        </div>
    </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>

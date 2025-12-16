<?php
$page_title = 'Research Management';
require_once 'header.php';
require_once '../config/database.php';
require_once '../config/notifications.php';

$message = '';

// Handle research approval/decline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_research'])) {
    $research_id = intval($_POST['research_id']);
    $status = sanitize($_POST['status']);
    $comments = sanitize($_POST['comments']);
    
    // Get research and faculty info for notification
    $research_info = $conn->query("SELECT r.*, f.faculty_id FROM research r JOIN faculty f ON r.faculty_id = f.faculty_id WHERE r.research_id = $research_id")->fetch_assoc();
    
    $update = "UPDATE research 
               SET status = ?, comments = ?, approved_by = ?, approval_date = NOW() 
               WHERE research_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssii", $status, $comments, $current_user['admin_id'], $research_id);
    
    if ($stmt->execute()) {
        // Notify faculty of status change
        notifyResearchStatus($conn, $research_info['faculty_id'], $research_info['title'], $status, $research_id);
        
        $message = '<div class="alert alert-success">✓ Research status updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating research</div>';
    }
}

// Handle delete research
if (isset($_GET['delete'])) {
    $research_id = intval($_GET['delete']);
    $delete = "DELETE FROM research WHERE research_id = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("i", $research_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">✓ Research deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting research</div>';
    }
}

// Time Travel / Filter setup
$filter_date = isset($_POST['filter_date']) ? sanitize($_POST['filter_date']) : date('Y-m-d');
$filter_status = isset($_POST['filter_status']) && !empty($_POST['filter_status']) ? sanitize($_POST['filter_status']) : '';

// Build query with date filter
$query = "SELECT r.*, f.name as faculty_name 
          FROM research r 
          JOIN faculty f ON r.faculty_id = f.faculty_id 
          WHERE DATE(r.submission_date) <= ?";

$params = [$filter_date];
$types = "s";

if (!empty($filter_status)) {
    $query .= " AND r.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$query .= " ORDER BY r.submission_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$research_list = $stmt->get_result();
?>

<style>
    .page-header {
        margin-bottom: var(--spacing-xl);
    }
    
    .page-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    
    .research-card {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-dark) 100%);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-lg);
        transition: var(--transition);
    }
    
    .research-card:hover {
        border-color: var(--accent);
        box-shadow: 0 4px 12px rgba(0, 212, 255, 0.1);
    }
    
    .research-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: var(--spacing-md);
    }
    
    .research-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
    }
    
    .status-badge {
        display: inline-block;
        padding: var(--spacing-xs) var(--spacing-md);
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-pending {
        background-color: rgba(245, 158, 11, 0.2);
        color: var(--warning);
    }
    
    .status-approved {
        background-color: rgba(16, 185, 129, 0.2);
        color: var(--success);
    }
    
    .status-declined {
        background-color: rgba(239, 68, 68, 0.2);
        color: var(--danger);
    }
    
    .status-revision_requested {
        background-color: rgba(59, 130, 246, 0.2);
        color: var(--info);
    }
    
    .research-meta {
        display: flex;
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-md);
        font-size: 0.9rem;
        color: var(--text-secondary);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-lg);
    }
    
    .time-travel-filter {
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-dark) 100%);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
        margin-bottom: var(--spacing-xl);
    }
    
    .filter-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
    }
    
    .filter-info {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: var(--spacing-lg);
    }
    
    .action-buttons {
        display: flex;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-md);
    }
    
    .btn-sm {
        padding: var(--spacing-xs) var(--spacing-md);
        font-size: 0.85rem;
    }
    
    .comments-section {
        padding: var(--spacing-md);
        background-color: var(--primary);
        border-radius: var(--radius-md);
        border-left: 4px solid var(--accent);
        margin-top: var(--spacing-md);
    }
    
    .edit-section {
        margin-top: var(--spacing-lg);
        padding-top: var(--spacing-lg);
        border-top: 1px solid var(--border-color);
    }
</style>

<div class="page-header">
    <h2>🔬 Research Management</h2>
    <p style="color: var(--text-secondary);">Review and manage research submissions</p>
</div>

<?php echo $message; ?>

<!-- Time Travel Filter -->
<div class="time-travel-filter">
    <form method="POST" style="display: grid; gap: var(--spacing-lg);">
        <div>
            <div class="filter-title">Goin back to hanolulu</div>
            <p class="filter-info">just to get that mowie wowie</p>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="filter_date">View Records Up To Date:</label>
                <input type="date" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="filter_status">Filter by Status (Optional):</label>
                <select id="filter_status" name="filter_status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="declined" <?php echo $filter_status === 'declined' ? 'selected' : ''; ?>>Declined</option>
                    <option value="revision_requested" <?php echo $filter_status === 'revision_requested' ? 'selected' : ''; ?>>Revision Requested</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">🔍 Apply Filter</button>
    </form>
</div>

<?php 
if ($research_list->num_rows > 0):
    while ($research = $research_list->fetch_assoc()): 
?>
<div class="research-card">
    <div class="research-header">
        <div style="flex: 1;">
            <div class="research-title"><?php echo htmlspecialchars($research['title']); ?></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: var(--spacing-xs);">
                By: <strong><?php echo htmlspecialchars($research['faculty_name']); ?></strong>
            </div>
        </div>
        <span class="status-badge status-<?php echo $research['status']; ?>">
            <?php echo ucfirst(str_replace('_', ' ', $research['status'])); ?>
        </span>
    </div>
    
    <div class="research-meta">
        <span>Category: <?php echo htmlspecialchars($research['category']); ?></span>
        <span>Submitted: <?php echo date('M d, Y', strtotime($research['submission_date'])); ?></span>
        <?php if ($research['approval_date']): ?>
            <span>Reviewed: <?php echo date('M d, Y', strtotime($research['approval_date'])); ?></span>
        <?php endif; ?>
    </div>
    
    <div style="margin: var(--spacing-md) 0; padding: var(--spacing-md); background: var(--primary); border-radius: var(--radius-md);">
        <strong style="color: var(--text-primary);">Description:</strong>
        <p style="margin-top: var(--spacing-sm); color: var(--text-secondary); line-height: 1.6;">
            <?php echo nl2br(htmlspecialchars($research['description'])); ?>
        </p>
    </div>
    
    <?php if ($research['status'] === 'pending'): ?>
    <div class="edit-section">
        <h4 style="color: var(--text-primary); margin-bottom: var(--spacing-md);">Review Research</h4>
        <form method="POST" style="display: grid; gap: var(--spacing-lg);">
            <input type="hidden" name="research_id" value="<?php echo $research['research_id']; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="status_<?php echo $research['research_id']; ?>">Decision *</label>
                    <select id="status_<?php echo $research['research_id']; ?>" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="approved">✓ Approve</option>
                        <option value="declined">✗ Decline</option>
                        <option value="revision_requested">Request Revision</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comments_<?php echo $research['research_id']; ?>">Admin Comments</label>
                <textarea id="comments_<?php echo $research['research_id']; ?>" 
                          name="comments" 
                          placeholder="Add your feedback or comments..."
                          style="min-height: 100px;"></textarea>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="update_research" class="btn btn-primary">Update Status</button>
                <a href="?delete=<?php echo $research['research_id']; ?>" 
                   class="btn btn-danger btn-sm" 
                   onclick="return confirm('Are you sure you want to delete this research? This action cannot be undone.');">
                   Delete Research
                </a>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="comments-section">
        <strong style="color: var(--text-primary);">Admin Comments:</strong>
        <p style="margin-top: var(--spacing-sm); color: var(--text-secondary);">
            <?php echo $research['comments'] ? nl2br(htmlspecialchars($research['comments'])) : 'No comments provided.'; ?>
        </p>
    </div>
    
    <div class="action-buttons">
        <a href="?delete=<?php echo $research['research_id']; ?>" 
           class="btn btn-danger btn-sm" 
           onclick="return confirm('Are you sure you want to delete this research? This action cannot be undone.');">
           Delete Research
        </a>
    </div>
    <?php endif; ?>
</div>
<?php 
    endwhile;
else:
?>
<div style="text-align: center; padding: var(--spacing-2xl); background: var(--primary-light); border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
    <p style="font-size: 1.2rem; color: var(--text-secondary); margin-bottom: var(--spacing-sm);">No research submissions found</p>
    <p style="color: var(--text-secondary);">
        <?php if (!empty($filter_status)): ?>
            Try adjusting your filter settings
        <?php else: ?>
            No research has been submitted yet
        <?php endif; ?>
    </p>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
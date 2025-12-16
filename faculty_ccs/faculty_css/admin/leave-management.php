<?php
$page_title = 'Leave Management';
require_once 'header.php';
require_once '../config/database.php';

$message = '';

// Handle leave approval/decline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_leave'])) {
    $leave_id = intval($_POST['leave_id']);
    $status = sanitize($_POST['status']);
    $admin_comments = sanitize($_POST['admin_comments']);
    
    $update = "UPDATE leaves SET status = ?, admin_comments = ?, approved_by = ?, approval_date = NOW() WHERE leave_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssii", $status, $admin_comments, $current_user['admin_id'], $leave_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Leave status updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating leave</div>';
    }
}

// Filter setup
$filter_status = isset($_POST['filter_status']) && !empty($_POST['filter_status']) ? sanitize($_POST['filter_status']) : '';

// Build query
$query = "SELECT l.*, f.name as faculty_name 
          FROM leaves l 
          JOIN faculty f ON l.faculty_id = f.faculty_id";

$params = [];
$types = "";

// Add status filter if selected
if (!empty($filter_status)) {
    $query .= " WHERE l.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$query .= " ORDER BY l.requested_date DESC";

$stmt = $conn->prepare($query);
if (!empty($filter_status)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$leaves_list = $stmt->get_result();
?>

<style>
    .leave-card {
        background-color: var(--primary-light);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
    }
    .leave-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: var(--spacing-md);
    }
    .leave-title {
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
    .status-declined {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }
    .leave-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        font-size: 0.9rem;
    }
    .detail-item {
        color: var(--text-secondary);
    }
    .detail-label {
        font-weight: 600;
        color: var(--text-primary);
    }
    .filter-section {
    background-color: var(--primary-light);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}
</style>

<?php echo $message; ?>

<h3>Leave Requests</h3>

<div class="filter-section">
<form method="POST" action="leave-management.php">
        <div class="form-group">
            <label for="filter_status">Filter by Status:</label>
            <select id="filter_status" name="filter_status">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="declined" <?php echo $filter_status === 'declined' ? 'selected' : ''; ?>>Declined</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top: var(--spacing-md);">Apply Filter</button>
    </form>
</div>

<?php while ($leave = $leaves_list->fetch_assoc()): ?>
<div class="leave-card">
    <div class="leave-header">
        <div>
            <div class="leave-title"><?php echo htmlspecialchars($leave['faculty_name']); ?></div>
            <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: var(--spacing-sm);">
                <?php echo ucfirst($leave['leave_type']); ?> Leave
            </div>
        </div>
        <span class="status-badge status-<?php echo $leave['status']; ?>">
            <?php echo ucfirst($leave['status']); ?>
        </span>
    </div>
    
    <div class="leave-details">
        <div class="detail-item">
            <div class="detail-label">From:</div>
            <?php echo date('M d, Y', strtotime($leave['start_date'])); ?>
        </div>
        <div class="detail-item">
            <div class="detail-label">To:</div>
            <?php echo date('M d, Y', strtotime($leave['end_date'])); ?>
        </div>
        <div class="detail-item">
            <div class="detail-label">Days:</div>
            <?php echo (strtotime($leave['end_date']) - strtotime($leave['start_date'])) / 86400 + 1; ?> days
        </div>
        <div class="detail-item">
            <div class="detail-label">Requested:</div>
            <?php echo date('M d, Y', strtotime($leave['requested_date'])); ?>
        </div>
    </div>
    
    <p style="margin-bottom: var(--spacing-md); color: var(--text-secondary);">
        <strong>Reason:</strong> <?php echo htmlspecialchars($leave['reason']); ?>
    </p>
    
    <?php if ($leave['status'] === 'pending'): ?>
    <form method="POST" style="display: grid; gap: var(--spacing-lg);">
        <input type="hidden" name="leave_id" value="<?php echo $leave['leave_id']; ?>">
        
        <div class="form-group">
            <label for="status_<?php echo $leave['leave_id']; ?>">Decision</label>
            <select id="status_<?php echo $leave['leave_id']; ?>" name="status" required>
                <option value="pending">Pending</option>
                <option value="approved">Approve</option>
                <option value="declined">Decline</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="comments_<?php echo $leave['leave_id']; ?>">Comments</label>
            <textarea id="comments_<?php echo $leave['leave_id']; ?>" name="admin_comments" placeholder="Add your comments..."></textarea>
        </div>
        
        <button type="submit" name="update_leave" class="btn btn-primary">Update Status</button>
    </form>
    <?php else: ?>
    <div style="padding: var(--spacing-md); background-color: var(--primary); border-radius: var(--radius-md);">
        <p><strong>Admin Comments:</strong></p>
        <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($leave['admin_comments']); ?></p>
    </div>
    <?php endif; ?>
</div>
<?php endwhile; ?>

<?php require_once 'footer.php'; ?>

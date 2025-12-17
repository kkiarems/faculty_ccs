<?php
$page_title = 'Leave';
require_once '../config/database.php';
require_once 'header.php';
requireFaculty();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure session user exists
if (!isset($_SESSION['current_user'])) {
    echo '<div class="alert alert-danger">Session expired. Please log in again.</div>';
    exit;
}

$current_user = $_SESSION['current_user'];
$faculty_id = $current_user['faculty_id'] ?? null;

$message = '';
$leaves_list = null;

// Handle add leave request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_leave'])) {
    if ($faculty_id) {
        $leave_type = $_POST['leave_type'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';

        $insert = "INSERT INTO leaves (faculty_id, leave_type, reason, start_date, end_date, status) 
                   VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("issss", $faculty_id, $leave_type, $reason, $start_date, $end_date);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Leave request submitted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error submitting leave request.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Unable to find your faculty ID.</div>';
    }
}

// Fetch leave records
if ($faculty_id) {
    $query = "SELECT * FROM leaves WHERE faculty_id = ? ORDER BY requested_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $leaves_list = $stmt->get_result();
}
?>


<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Leave Management</h2>
    <button class="btn btn-primary" onclick="openLeaveModal()">View Leave Requests</button>
</div>

<?php echo $message; ?>

<div class="form-container">
    <h3>Submit Leave Request</h3>
    <form method="POST">
        <div class="form-group">
            <label for="leave_type">Leave Type</label>
            <select id="leave_type" name="leave_type" required>
                <option value="">Select Type</option>
                <option value="sick">Sick Leave</option>
                <option value="vacation">Vacation Leave</option>
                <option value="emergency">Emergency Leave</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="reason">Reason</label>
            <textarea id="reason" name="reason" required></textarea>
        </div>

        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" required>
        </div>

        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" required>
        </div>

        <button type="submit" name="add_leave" class="btn btn-primary">Submit Request</button>
    </form>
</div>

<div id="leaveModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6);">
    <div class="modal-content" style="background: var(--primary-light); border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; max-width: 900px; width: 90%; max-height: 80vh; overflow-y: auto; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <h2 style="color: var(--text-primary);">All Leave Requests</h2>
            <button onclick="closeLeaveModal()" style="background: none; border: none; color: var(--text-secondary); font-size: 2rem; cursor: pointer;">&times;</button>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(0, 188, 212, 0.1);">
                        <th style="color: var(--accent); padding: 1.5rem; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border-color);">Leave Type</th>
                        <th style="color: var(--accent); padding: 1.5rem; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border-color);">From Date</th>
                        <th style="color: var(--accent); padding: 1.5rem; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border-color);">To Date</th>
                        <th style="color: var(--accent); padding: 1.5rem; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border-color);">Status</th>
                        <th style="color: var(--accent); padding: 1.5rem; text-align: left; font-weight: 600; border-bottom: 2px solid var(--border-color);">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$leaves_list || $leaves_list->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-secondary);">No leave requests yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php while ($leave = $leaves_list->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 1.5rem; color: var(--text-secondary);"><?php echo htmlspecialchars(ucfirst($leave['leave_type'])); ?></td>
                                <td style="padding: 1.5rem; color: var(--text-secondary);"><?php echo htmlspecialchars($leave['start_date']); ?></td>
                                <td style="padding: 1.5rem; color: var(--text-secondary);"><?php echo htmlspecialchars($leave['end_date']); ?></td>
                                <td style="padding: 1.5rem;">
                                    <span class="status-badge status-<?php echo htmlspecialchars($leave['status']); ?>" style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                        <?php echo ucfirst($leave['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1.5rem; color: var(--text-secondary);"><?php echo htmlspecialchars($leave['reason']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openLeaveModal() {
    document.getElementById('leaveModal').style.display = 'flex';
    document.getElementById('leaveModal').style.alignItems = 'center';
    document.getElementById('leaveModal').style.justifyContent = 'center';
}

function closeLeaveModal() {
    document.getElementById('leaveModal').style.display = 'none';
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('leaveModal');
    if (event.target === modal) {
        closeLeaveModal();
    }
});
</script>

<?php require_once 'footer.php'; ?>

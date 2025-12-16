<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = intval($_POST['leave_id']);
    $status = sanitize($_POST['status']);
    $admin_comments = sanitize($_POST['admin_comments']);
    
    $update = "UPDATE leaves SET status = ?, admin_comments = ?, approved_by = ?, approval_date = NOW() WHERE leave_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssii", $status, $admin_comments, $_SESSION['user_id'], $leave_id);
    
    if ($stmt->execute()) {
        header("Location: ../admin/leave-management.php?success=1");
    } else {
        header("Location: ../admin/leave-management.php?error=1");
    }
    exit();
}
?>

<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireFaculty();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = sanitize($_POST['leave_type']);
    $start_date = sanitize($_POST['start_date']);
    $end_date = sanitize($_POST['end_date']);
    $reason = sanitize($_POST['reason']);
    
    $insert = "INSERT INTO leaves (faculty_id, leave_type, start_date, end_date, reason, status) 
               VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("issss", $_SESSION['user_id'], $leave_type, $start_date, $end_date, $reason);
    
    if ($stmt->execute()) {
        header("Location: ../faculty/leave.php?success=1");
    } else {
        header("Location: ../faculty/leave.php?error=1");
    }
    exit();
}
?>

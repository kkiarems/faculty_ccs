<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $research_id = intval($_POST['research_id']);
    $status = sanitize($_POST['status']);
    $comments = sanitize($_POST['comments']);
    
    $update = "UPDATE research SET status = ?, comments = ?, approved_by = ?, approval_date = NOW() WHERE research_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssii", $status, $comments, $_SESSION['user_id'], $research_id);
    
    if ($stmt->execute()) {
        header("Location: ../admin/research-management.php?success=1");
    } else {
        header("Location: ../admin/research-management.php?error=1");
    }
    exit();
}
?>

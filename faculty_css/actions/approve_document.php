<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_id = intval($_POST['document_id']);
    $status = sanitize($_POST['status']);
    
    $update = "UPDATE documents SET status = ?, approved_by = ?, approval_date = NOW() WHERE document_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("sii", $status, $_SESSION['user_id'], $document_id);
    
    if ($stmt->execute()) {
        header("Location: ../admin/documents-management.php?success=1");
    } else {
        header("Location: ../admin/documents-management.php?error=1");
    }
    exit();
}
?>

<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if (isset($_GET['id'])) {
    $faculty_id = intval($_GET['id']);
    
    $delete = "DELETE FROM faculty WHERE faculty_id = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("i", $faculty_id);
    
    if ($stmt->execute()) {
        header("Location: ../admin/faculty-management.php?success=1");
    } else {
        header("Location: ../admin/faculty-management.php?error=1");
    }
    exit();
}
?>

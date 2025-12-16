<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if (isset($_GET['id'])) {
    $course_id = intval($_GET['id']);
    
    $delete = "DELETE FROM courses WHERE course_id = ?";
    $stmt = $conn->prepare($delete);
    $stmt->bind_param("i", $course_id);
    
    if ($stmt->execute()) {
        header("Location: ../admin/course-management.php?success=1");
    } else {
        header("Location: ../admin/course-management.php?error=1");
    }
    exit();
}
?>

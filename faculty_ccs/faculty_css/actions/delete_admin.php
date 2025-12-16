<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if (isset($_GET['id'])) {
    $admin_id = intval($_GET['id']);
    
    if ($admin_id !== $_SESSION['user_id']) {
        $delete = "DELETE FROM admins WHERE admin_id = ?";
        $stmt = $conn->prepare($delete);
        $stmt->bind_param("i", $admin_id);
        
        if ($stmt->execute()) {
            header("Location: ../admin/admin-management.php?success=1");
        } else {
            header("Location: ../admin/admin-management.php?error=1");
        }
    } else {
        header("Location: ../admin/admin-management.php?error=2");
    }
    exit();
}
?>

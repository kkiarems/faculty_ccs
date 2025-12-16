<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = hashPassword(sanitize($_POST['password']));
    $position = sanitize($_POST['position']);
    $department = sanitize($_POST['department']);
    
    $insert = "INSERT INTO admins (name, email, password, position, department) 
               VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("sssss", $name, $email, $password, $position, $department);
    
    if ($stmt->execute()) {
        header("Location: ../admin/admin-management.php?success=1");
    } else {
        header("Location: ../admin/admin-management.php?error=1");
    }
    exit();
}
?>

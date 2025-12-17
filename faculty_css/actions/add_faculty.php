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
    $contact = sanitize($_POST['contact']);
    
    $insert = "INSERT INTO faculty (name, email, password, position, department, contact) 
               VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("ssssss", $name, $email, $password, $position, $department, $contact);
    
    if ($stmt->execute()) {
        header("Location: ../admin/faculty-management.php?success=1");
    } else {
        header("Location: ../admin/faculty-management.php?error=1");
    }
    exit();
}
?>

<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireFaculty();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $document_name = sanitize($_POST['document_name']);
    $document_type = sanitize($_POST['document_type']);
    $category = sanitize($_POST['category']);
    
    $insert = "INSERT INTO documents (faculty_id, document_name, document_type, category, status) 
               VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("isss", $_SESSION['user_id'], $document_name, $document_type, $category);
    
    if ($stmt->execute()) {
        header("Location: ../faculty/documents.php?success=1");
    } else {
        header("Location: ../faculty/documents.php?error=1");
    }
    exit();
}
?>

<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireFaculty();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize(input: $_POST['title']);
    $description = sanitize(input: $_POST['description']);
    $category = sanitize(input: $_POST['category']);
    
    $insert = "INSERT INTO research (faculty_id, title, description, category, status) 
               VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare(query: $insert);
    $stmt->bind_param("isss", $_SESSION['user_id'], $title, $description, $category);
    
    if ($stmt->execute()) {
        header(header: "Location: ../faculty/research.php?success=1");
    } else {
        header(header: "Location: ../faculty/research.php?error=1");
    }
    exit();
}
?>

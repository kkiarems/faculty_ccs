<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = sanitize($_POST['course_code']);
    $course_name = sanitize($_POST['course_name']);
    $description = sanitize($_POST['description']);
    $units = intval($_POST['units']);
    $semester = intval($_POST['semester']);
    $year_level = intval($_POST['year_level']);
    
    $insert = "INSERT INTO courses (course_code, course_name, description, units, semester, year_level, created_by) 
               VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("sssiii", $course_code, $course_name, $description, $units, $semester, $year_level, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        header("Location: ../admin/course-management.php?success=1");
    } else {
        header("Location: ../admin/course-management.php?error=1");
    }
    exit();
}
?>

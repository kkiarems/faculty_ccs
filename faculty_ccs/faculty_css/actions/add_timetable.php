<?php
require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faculty_id = intval($_POST['faculty_id']);
    $course_id = intval($_POST['course_id']);
    $day_of_week = sanitize($_POST['day_of_week']);
    $start_time = sanitize($_POST['start_time']);
    $end_time = sanitize($_POST['end_time']);
    $room_number = sanitize($_POST['room_number']);
    
    $insert = "INSERT INTO timetables (faculty_id, course_id, day_of_week, start_time, end_time, room_number, created_by) 
               VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("iissssi", $faculty_id, $course_id, $day_of_week, $start_time, $end_time, $room_number, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        header("Location: ../admin/timetable-management.php?success=1");
    } else {
        header("Location: ../admin/timetable-management.php?error=1");
    }
    exit();
}
?>

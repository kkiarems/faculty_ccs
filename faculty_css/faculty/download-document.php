<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

$document_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? null;
$faculty_id = $_SESSION['current_user']['faculty_id'] ?? null;

// Get document
$query = "SELECT * FROM documents WHERE document_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $document_id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

if (!$document) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

// Check if user is the owner
if ($document['faculty_id'] != $faculty_id) {
    header("HTTP/1.0 403 Forbidden");
    exit();
}

// Check if file exists
if (!file_exists($document['file_path'])) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

// Download file
header('Content-Type: ' . ($document['mime_type'] ?? 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($document['document_name']) . '"');
header('Content-Length: ' . $document['file_size']);
readfile($document['file_path']);
exit();
?>

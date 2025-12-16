<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Get document ID
$document_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$faculty_id = $_SESSION['faculty_id'] ?? 0;

if ($document_id <= 0 || $faculty_id <= 0) {
    die('Invalid request');
}

// Get document details
$stmt = $conn->prepare("SELECT * FROM documents WHERE document_id = ? AND faculty_id = ?");
$stmt->bind_param("ii", $document_id, $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Document not found or access denied');
}

$document = $result->fetch_assoc();
$file_path = '../' . $document['file_path'];

if (!file_exists($file_path)) {
    die('File not found on server');
}

// Get file extension
$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Set appropriate content type
$content_types = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'ppt' => 'application/vnd.ms-powerpoint',
    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'txt' => 'text/plain',
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed'
];

$content_type = $content_types[$file_extension] ?? 'application/octet-stream';

// For images and PDFs, display inline; for others, force download
$disposition = in_array($file_extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt']) ? 'inline' : 'attachment';

// Set headers
header('Content-Type: ' . $content_type);
header('Content-Disposition: ' . $disposition . '; filename="' . basename($document['document_name']) . '.' . $file_extension . '"');
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output file
readfile($file_path);
exit;
?>
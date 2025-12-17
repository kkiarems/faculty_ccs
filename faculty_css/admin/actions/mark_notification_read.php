<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../config/notifications.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$notification_id = isset($data['notification_id']) ? (int)$data['notification_id'] : 0;

if ($notification_id > 0) {
    $success = markAsRead($conn, $notification_id);
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false]);
}
?>
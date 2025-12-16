<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
require_once '../../config/notifications.php';

header('Content-Type: application/json');

$user_type = 'admin';
$user_id = $_SESSION['admin_id'];

if ($user_id > 0) {
    $success = markAllAsRead($conn, $user_type, $user_id);
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false]);
}
?>
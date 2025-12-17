<?php
// config/notifications.php - Notification Helper Functions

// Create a notification
function createNotification($conn, $user_type, $user_id, $title, $message, $type, $reference_id = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, title, message, type, reference_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssi", $user_type, $user_id, $title, $message, $type, $reference_id);
    return $stmt->execute();
}

// Notify all admins
function notifyAllAdmins($conn, $title, $message, $type, $reference_id = null) {
    $admins = $conn->query("SELECT admin_id FROM admins");
    while ($admin = $admins->fetch_assoc()) {
        createNotification($conn, 'admin', $admin['admin_id'], $title, $message, $type, $reference_id);
    }
}

// Notify specific faculty
function notifyFaculty($conn, $faculty_id, $title, $message, $type, $reference_id = null) {
    return createNotification($conn, 'faculty', $faculty_id, $title, $message, $type, $reference_id);
}

// Get unread notification count
function getUnreadCount($conn, $user_type, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_type = ? AND user_id = ? AND is_read = FALSE");
    $stmt->bind_param("si", $user_type, $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}

// Get notifications for user
function getNotifications($conn, $user_type, $user_id, $limit = 20) {
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE user_type = ? AND user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->bind_param("sii", $user_type, $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Mark notification as read
function markAsRead($conn, $notification_id) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE notification_id = ?");
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}

// Mark all as read for user
function markAllAsRead($conn, $user_type, $user_id) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_type = ? AND user_id = ?");
    $stmt->bind_param("si", $user_type, $user_id);
    return $stmt->execute();
}

// Clear all notifications for user
function clearAllNotifications($conn, $user_type, $user_id) {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE user_type = ? AND user_id = ?");
    $stmt->bind_param("si", $user_type, $user_id);
    return $stmt->execute();
}

// Get time ago format
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $current = time();
    $seconds = $current - $time;
    
    if ($seconds < 60) return 'Just now';
    if ($seconds < 3600) return floor($seconds / 60) . 'm ago';
    if ($seconds < 86400) return floor($seconds / 3600) . 'h ago';
    if ($seconds < 604800) return floor($seconds / 86400) . 'd ago';
    if ($seconds < 2592000) return floor($seconds / 604800) . 'w ago';
    return floor($seconds / 2592000) . 'mo ago';
}

// NOTIFICATION TRIGGERS
// Call these when actions happen

// When faculty submits research
function notifyResearchSubmitted($conn, $faculty_name, $research_title, $research_id) {
    notifyAllAdmins(
        $conn,
        "New Research Submission",
        "$faculty_name submitted research: \"$research_title\"",
        "research",
        $research_id
    );
}

// When admin approves/rejects research
function notifyResearchStatus($conn, $faculty_id, $research_title, $status, $research_id) {
    $statusText = $status === 'approved' ? 'approved' : 'declined';
    notifyFaculty(
        $conn,
        $faculty_id,
        "Research $statusText",
        "Your research \"$research_title\" has been $statusText",
        "research",
        $research_id
    );
}

// When faculty requests leave
function notifyLeaveRequested($conn, $faculty_name, $leave_type, $leave_id) {
    notifyAllAdmins(
        $conn,
        "New Leave Request",
        "$faculty_name requested $leave_type leave",
        "leave",
        $leave_id
    );
}

// When admin approves/rejects leave
function notifyLeaveStatus($conn, $faculty_id, $leave_type, $status, $leave_id) {
    $statusText = $status === 'approved' ? 'approved' : 'declined';
    notifyFaculty(
        $conn,
        $faculty_id,
        "Leave Request $statusText",
        "Your $leave_type leave request has been $statusText",
        "leave",
        $leave_id
    );
}

// When faculty uploads document
function notifyDocumentUploaded($conn, $faculty_name, $document_name, $document_id) {
    notifyAllAdmins(
        $conn,
        "New Document Upload",
        "$faculty_name uploaded: \"$document_name\"",
        "document",
        $document_id
    );
}

// When admin approves/rejects document
function notifyDocumentStatus($conn, $faculty_id, $document_name, $status, $document_id) {
    $statusText = $status === 'approved' ? 'approved' : 'rejected';
    notifyFaculty(
        $conn,
        $faculty_id,
        "Document $statusText",
        "Your document \"$document_name\" has been $statusText",
        "document",
        $document_id
    );
}
?>
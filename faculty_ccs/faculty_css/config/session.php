<?php
// Start session safely (avoid duplicate start warnings)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

// Check if user is faculty
function isFaculty() {
    return isLoggedIn() && $_SESSION['user_type'] === 'faculty';
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /faculty_css/index.php");
        exit();
    }
}

// Redirect to login if not admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: /faculty_css/index.php");
        exit();
    }
}

// Redirect to login if not faculty
function requireFaculty() {
    if (!isFaculty()) {
        header("Location: /faculty_css/index.php");
        exit();
    }
}

// Get current user info
function getCurrentUser($conn) {
    if (!$conn || $conn->connect_error) {
        return null;
    }
    
    if (!isLoggedIn()) return null;
    
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];
    
    $query = $user_type === 'admin'
        ? "SELECT * FROM admins WHERE admin_id = ?"
        : "SELECT * FROM faculty WHERE faculty_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}
?>

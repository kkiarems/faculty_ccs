<?php
// Database configuration for Faculty Information System
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'faculty_css');

// Try to connect with database name first, if it fails (database doesn't exist), connect without it
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// If database doesn't exist, create a connection without specifying the database
if ($conn->connect_error && strpos($conn->connect_error, 'Unknown database') !== false) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create the database if it doesn't exist
    $create_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if (!$conn->query($create_db)) {
        die("Error creating database: " . $conn->error);
    }
    
    // Select the database
    if (!$conn->select_db(DB_NAME)) {
        die("Error selecting database: " . $conn->error);
    }
} elseif ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Function to sanitize input
function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim($input));
}

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>

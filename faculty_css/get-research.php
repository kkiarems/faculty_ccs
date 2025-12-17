<?php
require_once 'config/session.php';
requireFaculty();

header('Content-Type: application/json');

try {
    $faculty_id = $_SESSION['user_id'];
    
    if (!$faculty_id) {
        throw new Exception("Faculty ID not found in session");
    }

    // Direct database connection
    $conn = new mysqli('localhost', 'root', '', 'faculty_css');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $query = "SELECT research_id, title, category, status, submission_date FROM research WHERE faculty_id = ? ORDER BY submission_date DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $faculty_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $research = [];

    while ($row = $result->fetch_assoc()) {
        $research[] = $row;
    }

    echo json_encode([
        'success' => true,
        'research' => $research
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
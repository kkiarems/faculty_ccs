<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $conn->query("DELETE FROM documents WHERE id = $id");
    header('Location: ../faculty/documents.php');
    exit();
}
?>

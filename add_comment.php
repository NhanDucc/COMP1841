<?php
session_start();
include 'includes/DatabaseConnection.php'; // Include database connection
include 'includes/DatabaseFunctions.php'; // Include functions

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = $_POST['question_id'];
    $username = $_SESSION['username'];
    $comment_text = $_POST['comment_text'];
    addComment($pdo, $question_id, $username, $comment_text);
    header('Location: index.php');
    exit();
}
?>
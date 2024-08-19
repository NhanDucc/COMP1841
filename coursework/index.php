<?php
session_start();
include 'includes/DatabaseConnection.php';
include 'includes/DatabaseFunctions.php';

if (isset($_SESSION['username'])) {
    checklogoutUser();
    try {
        $search_term = isset($_GET['search']) ? $_GET['search'] : ''; 
        $questions = getQuestions($pdo, $search_term);
        $question_ids = array_column($questions, 'id');
        $comments = getComments($pdo, $question_ids);
        include 'templates/index.html.php'; 
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
    }
} else {
    header('Location: login.php');
    exit; 
}
?>
<?php
session_start();
include 'includes/DatabaseConnection.php';
include 'includes/DatabaseFunctions.php';

if (isset($_SESSION['username']) && isset($_GET['id'])) {
    $question_id = $_GET['id'];
    $username = $_SESSION['username'];
    try {
        $user_id = getUserIdDel($pdo, $username);
        $question = getQuestion($pdo, $question_id, $user_id);
        if ($question) {
            deleteImages($pdo, $question_id);
            deleteQuestion($pdo, $question_id);
            //echo '<script>alert("The question has been successfully deleted"); window.location.href="index.php";</script>';
            $_SESSION['message'] = 'The question has been successfully deleted';
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error'] = 'You do not have permission to delete this question';
        }
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
    }
} else {
    header('Location: login.php');
    exit;
}
?>

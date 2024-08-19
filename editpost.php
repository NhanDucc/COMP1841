<?php
session_start(); // Start the session
include 'includes/DatabaseConnection.php'; // Include the database connection file
include 'includes/DatabaseFunctions.php'; // Include the database functions file

checkLogin();
if (isset($_GET['id'])) {
    $question_id = $_GET['id'];
    try {
        $question = getQuestionById($pdo, $question_id);
        if ($question && $question['username'] == $_SESSION['username']) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $module_id = $_POST['module'];
                $question_text = $_POST['question'];
                $existing_images = isset($_POST['existing_images']) ? explode(',', $_POST['existing_images']) : [];
                updateQuestion($pdo, $question_id, $module_id, $question_text);
                deleteOldImages($pdo, $question_id, $existing_images);
                if (!empty($_FILES['images']['name'][0])) {
                    uploadNewImages($pdo, $question_id, $_FILES['images']);
                }
                echo json_encode(['success' => true, 'message' => 'Question updated successfully']);
                exit;
            }
            include 'templates/editpost.html.php';
        } else {
            echo 'You are not authorized to edit this post.';
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo 'Invalid request.';
}

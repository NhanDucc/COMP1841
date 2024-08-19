<?php
session_start();  // Start a session
include 'includes/DatabaseFunctions.php';  // Include the file for database functions
include 'includes/DatabaseConnection.php';  // Include the file for database connection

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the action is to post a question
    if (isset($_GET['action']) && $_GET['action'] === 'post') {  
        $question_text = isset($_POST['question']) ? $_POST['question'] : '';  // Retrieve the question text from the form, default to empty string if not set
        $module_id = isset($_POST['module']) ? $_POST['module'] : '';  // Retrieve the module ID from the form, default to empty string if not set
        $username = $_SESSION['username'];  // Retrieve the username from the session
        try {
            $user_id = getUserId($pdo, $username);  // Get the user ID based on the username
                $question_id = saveQuestion($pdo, $user_id, $module_id, $question_text);  // Save the question and get the question ID
                // Check if any images were uploaded
                if (!empty($_FILES["images"]["name"][0])) {
                    uploadImages($pdo, $question_id, $_FILES["images"]);  // Upload the images associated with the question
                }
            } catch (PDOException $e) {
                echo 'Database error: ' . $e->getMessage();  // Display a database error message
            }
    }
}
include 'templates/addquestion.html.php';  // Include the HTML template for adding a question

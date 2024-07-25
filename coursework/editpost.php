<?php
session_start(); // Start the session
include 'includes/DatabaseConnection.php'; // Include the database connection file
include 'includes/DatabaseFunctions.php'; // Include the database functions file

checkLogin();
// Check if 'id' parameter is present in GET request
if (isset($_GET['id'])) {
    $question_id = $_GET['id']; // Get 'id' parameter from GET request
    try {
        $question = getQuestionById($pdo, $question_id);  // Get question details by question ID
        if ($question && $question['username'] == $_SESSION['username']) {  // Check if the question exists and the current user is the owner
            // Handle POST request
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $module_id = $_POST['module'];
                $question_text = $_POST['question'];
                $existing_images = isset($_POST['existing_images']) ? explode(',', $_POST['existing_images']) : [];
                updateQuestion($pdo, $question_id, $module_id, $question_text);  // Update question in the database
                deleteOldImages($pdo, $question_id, $existing_images);  // Delete old images not present in existing_images
                // Handle new image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    uploadNewImages($pdo, $question_id, $_FILES['images']);
                }
                echo json_encode(['success' => true, 'message' => 'Question updated successfully']);  // Return success response as JSON
                exit;  // Terminate script
            }
            include 'templates/editpost.html.php';  // Include HTML template for editing the post
        } else {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to edit this post.']);  // Return failure response as JSON if user is not authorized to edit the post
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);  // Return failure response as JSON if there is a database error
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);  // Return failure response as JSON if request is invalid
}
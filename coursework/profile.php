<?php
session_start();  // Start the session
include 'includes/DatabaseConnection.php';  // Include the database connection file
include 'includes/DatabaseFunctions.php';  // Include the database functions file

checkLogin();  // Check if the user is logged in

try {
    $user = getUserDetails($pdo, $_SESSION['username']);  // Get the details of the logged-in user
    $questions = getUserQuestions($pdo, $user['id']);  // Get the questions posted by the user
    include 'templates/profile.html.php';  // Include the profile template

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {  // Check if the request method is POST
        if (isset($_POST['delete']) && $_POST['delete'] == 'delete') {  // Check if the delete account request is made
            deleteAccount($pdo, $user['id']);  // Delete the user account
        } else {
            $newFullname = $_POST['fullname'];  // Get the new fullname from the form
            $newPhonenumber = $_POST['phonenumber'];  // Get the new phone number from the form
            $newEmail = $_POST['email'];  // Get the new email from the form
            $newGender = $_POST['gender'];  // Get the new gender from the form
            $newPassword = $_POST['password'];  // Get the new password from the form
            updateUser($pdo, $user['id'], $newFullname, $newPhonenumber, $newEmail, $newGender, $newPassword, $user['fullname'], $user['phonenumber'], $user['email'], $user['gender']);  // Update the user details
        }
    }
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();  // Catch and display the database error message
}
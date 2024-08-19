<?php
session_start();
include 'includes/DatabaseConnection.php';
include 'includes/DatabaseFunctions.php';

checkLogin();
try {
    $user = getUserDetails($pdo, $_SESSION['username']);
    $questions = getUserQuestions($pdo, $user['id']);
    include 'templates/profile.html.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete']) && $_POST['delete'] == 'delete') {
            deleteAccount($pdo, $user['id']);
        } else {
            $newFullname = $_POST['fullname'];
            $newPhonenumber = $_POST['phonenumber'];
            $newEmail = $_POST['email'];
            $newGender = $_POST['gender'];
            $newPassword = $_POST['password'];
            updateUser($pdo, $user['id'], $newFullname, $newPhonenumber, $newEmail, $newGender, $newPassword, $user['fullname'], $user['phonenumber'], $user['email'], $user['gender']);  // Update the user details
        }
    }
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
}

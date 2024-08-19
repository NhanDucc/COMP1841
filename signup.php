<?php
session_start();
include 'includes/DatabaseConnection.php';
include 'includes/DatabaseFunctions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $phonenumber = $_POST['phonenumber'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    if (checkUsernameExists($pdo, $username)) {
        $_SESSION['error'] = "Username already exists.";
        $_SESSION['fullname'] = $fullname;
        $_SESSION['phonenumber'] = $phonenumber;
        $_SESSION['email'] = $email;
        $_SESSION['gender'] = $gender;
        $_SESSION['username'] = $username;
        header("Location: signup.php");
        exit;
    } else {
        if (saveUser($pdo, $fullname, $phonenumber, $email, $gender, $username, $password)) {
            header('Location: login.php');
            exit;
        } else {
            $_SESSION['error'] = "Please try again.";
            header("Location: signup.php");
            exit;
        }
    }
}
$previousFullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';
$previousPhonenumber = isset($_SESSION['phonenumber']) ? $_SESSION['phonenumber'] : '';
$previousEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$previousGender = isset($_SESSION['gender']) ? $_SESSION['gender'] : '';
$previousUsername = isset($_SESSION['username']) ? $_SESSION['username'] : '';
include 'templates\signup.html.php';
?>

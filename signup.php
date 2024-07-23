<?php
session_start();
$error_message = '';
include 'includes\DatabaseConnection.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $phonenumber = $_POST['phonenumber'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM account WHERE username = ?");
    $checkStmt->execute([$username]);
    $usernameExists = $checkStmt->fetchColumn();

    if ($usernameExists) {
        $error_message = "Username already exists.";
        $_SESSION['error_message'] = $error_message;

        $_SESSION['fullname'] = $fullname;
        $_SESSION['phonenumber'] = $phonenumber;
        $_SESSION['email'] = $email;
        $_SESSION['gender'] = $gender;
        $_SESSION['username'] = $username;

        header("Location: index.php");
        exit;
    } else {
        $stmt = $pdo->prepare("INSERT INTO account (fullname, phonenumber, email, gender, username, password) VALUES (?, ?, ?, ?, ?, ?)");

        if ($stmt->execute([$fullname, $phonenumber, $email, $gender, $username, $password])) {
            header('Location: index.php');
            exit;
        } else {
            $error_message = "Please try again.";
            $_SESSION['error_message'] = $error_message;
            header("Location: index.php");
            exit;
        }
    }
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}


$previousFullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : '';
$previousPhonenumber = isset($_SESSION['phonenumber']) ? $_SESSION['phonenumber'] : '';
$previousEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$previousGender = isset($_SESSION['gender']) ? $_SESSION['gender'] : '';
$previousUsername = isset($_SESSION['username']) ? $_SESSION['username'] : '';


include 'templates\signup.html.php';
?>
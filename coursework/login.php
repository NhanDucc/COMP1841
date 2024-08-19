<?php
session_start();
$error_message = '';
include 'includes/DatabaseConnection.php';
include 'includes/DatabaseFunctions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($user = validateLogin($pdo, $username, $password)) {
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: login.php");
        exit;
    }
}
include 'templates\login.html.php';
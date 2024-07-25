<?php
session_start();
$error_message = '';
include 'includes\DatabaseConnection.php';
include 'includes/DatabaseFunctions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($user = validateLogin($pdo, $username, $password)) {
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        setSessionError("Invalid username or password.");
        header("Location: login.php");
        exit;
    }
}

$error_message = getSessionError();

include 'templates\login.html.php';
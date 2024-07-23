<?php
session_start();
$error_message = '';
include 'includes\DatabaseConnection.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM account WHERE username = :username AND password = :password");
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $password);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error_message = "Invalid username or password.";
        $_SESSION['error_message'] = $error_message;
        header("Location: login.php");
        exit;
    }
}

if(isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

include 'templates\login.html.php';
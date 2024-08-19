<?php
session_start(); // Start the session or continue the existing one
include 'includes/DatabaseConnection.php'; // Include the database connection file
include 'includes/DatabaseFunctions.php'; // Include the file containing database handling functions
include 'templates/contact.html.php'; // Include the HTML file for the contact interface

checkLogin();   // Check if the user is logged in
require("PHPMailer-master/src/PHPMailer.php");
require("PHPMailer-master/src/SMTP.php");
require("PHPMailer-master/src/Exception.php");

if  ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $message = $_POST["message"];

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->IsSMTP();

    $mail->SMTPDebug = 0;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'ssl';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 465;
    $mail->IsHTML(true);
    $mail->Username = "iiduc124@gmail.com";
    $mail->Password = "mrnd daoe nnwk fgub";
    $mail->SetFrom("iiduc124@gmail.com", "DuckY");
    $mail->Subject = "New Message from $name";
    $mail->Body = "From: $email <br> Message: $message";
    $mail->AddAddress("iiduc124@gmail.com");

    $mail->Send();
    $_SESSION['message'] = "Message has been sent";
    header('Location: contact.php');
}

?>
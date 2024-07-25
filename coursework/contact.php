<?php
session_start(); // Start the session or continue the existing one
include 'includes/DatabaseConnection.php'; // Include the database connection file
include 'includes/DatabaseFunctions.php'; // Include the file containing database handling functions
include 'templates/contact.html.php'; // Include the HTML file for the contact interface

checkLogin();   // Check if the user is logged in
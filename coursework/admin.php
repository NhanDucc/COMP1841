<?php
session_start();
include 'includes/DatabaseConnection.php';
include 'includes/DatabaseFunctions.php';
include 'templates/admin.html.php';

checkLogin();
$username = $_SESSION['username'];
if (isAdmin($username)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_module'])) {
        $newModule = trim($_POST['new_module']);
        if (!empty($newModule)) {
            if (!isModuleExists($pdo, $newModule)) {
                try {
                    addModule($pdo, $newModule);
                    $_SESSION['message'] = "Module has been added successfully.";
                    header('Location: admin.php');
                    exit;
                } catch (PDOException $e) {
                    $_SESSION['error'] = 'Error database: ' . $e->getMessage();
                    header('Location: admin.php');
                    exit;
                }
            } else {
                $_SESSION['error'] = "Module name already exists.";
                header('Location: admin.php');
                exit;
            }
        } else {
            $_SESSION['error'] = "Please enter a new module name.";
            header('Location: admin.php');
            exit;
        }
    }
} else {
    $_SESSION['error'] = "You are not authorised to access this page.";
    header('Location: index.php');
    exit;
}
?>
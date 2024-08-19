<?php
// Index

// Check if the user has requested to log out
function checklogoutUser() {
    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
        // Destroy the session and redirect the user to the login page
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Fetch all questions from the database
function getQuestions($pdo, $search_term = '') {
    // Query to retrieve questions, associated module, user, and images
    $query = "SELECT q.id, q.question_text, q.time_post, a.username, m.module_name, GROUP_CONCAT(i.image_link) AS images 
              FROM question q
              JOIN module m ON q.module_id = m.id
              JOIN account a ON q.user_id = a.id 
              LEFT JOIN image i ON q.id = i.question_id ";
    // Add search condition if a search term is provided
    if ($search_term) {
        $query .= "WHERE q.question_text LIKE :search_term ";
    }
    // Group results by question ID and order by the time of posting
    $query .= "GROUP BY q.id ORDER BY q.time_post DESC";
    $stmt = $pdo->prepare($query);
    // Bind the search term to the query if provided
    if ($search_term) {
        $stmt->bindValue(':search_term', '%' . $search_term . '%');
    }
    // Execute the query and return all results
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Retrieve comments for a list of questions
function getComments($pdo, $question_ids) {
    $comments = [];
    $query_comments = "SELECT * FROM comments WHERE question_id = :question_id ORDER BY time_post DESC";
    $stmt_comments = $pdo->prepare($query_comments);
    // Loop through each question ID and get related comments
    foreach ($question_ids as $question_id) {
        $stmt_comments->execute(['question_id' => $question_id]);
        $comments[$question_id] = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
    }
    // Return the array of comments
    return $comments;
}

// Signup

// Check if the username already exists in the database
function checkUsernameExists($pdo, $username) {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM account WHERE username = ?");
    $checkStmt->execute([$username]);
    // Return the number of matching records
    return $checkStmt->fetchColumn();
}

// Save new user information into the database
function saveUser($pdo, $fullname, $phonenumber, $email, $gender, $username, $password) {
    $stmt = $pdo->prepare("INSERT INTO account (fullname, phonenumber, email, gender, username, password) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$fullname, $phonenumber, $email, $gender, $username, $password]);
}

// Set error message in the session
function setSessionError($message) {
    $_SESSION['error_message'] = $message;
}

function getSessionError() {
    if (isset($_SESSION['error_message'])) {
        $error_message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return $error_message;
    }
    return '';
}

// Login
function validateLogin($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM account WHERE username = :username AND password = :password");
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $password);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

// Profile
function checkLogin() {
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }
}

function getUserDetails($pdo, $username) {
    $stmt = $pdo->prepare('SELECT * FROM account WHERE username = :username');
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    return $stmt->fetch();
}

function getUserQuestions($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT q.id, q.question_text, q.time_post, GROUP_CONCAT(i.image_link) AS images, m.module_name 
                           FROM question q 
                           LEFT JOIN image i ON q.id = i.question_id 
                           LEFT JOIN module m ON q.module_id = m.id
                           WHERE q.user_id = :user_id 
                           GROUP BY q.id, m.module_name 
                           ORDER BY q.time_post DESC");
    $stmt->bindValue(':user_id', $userId);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteAccount($pdo, $userId) {
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('DELETE image FROM image INNER JOIN question ON image.question_id = question.id WHERE question.user_id = :user_id');
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        $stmt = $pdo->prepare('DELETE FROM question WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        $stmt = $pdo->prepare('DELETE FROM account WHERE id = :id');
        $stmt->bindValue(':id', $userId);
        $stmt->execute();

        $pdo->commit();

        session_destroy();
        echo "<script>
                 window.location.href = 'login.php';
               </script>";
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>showAlert('Failed to delete account and related data.');</script>";
    }
}

function updateUser($pdo, $userId, $newFullname, $newPhonenumber, $newEmail, $newGender, $newPassword, $currentFullname, $currentPhonenumber, $currentEmail, $currentGender) {
    $fullnameToUpdate = !empty($newFullname) ? $newFullname : $currentFullname;
    $phonenumberToUpdate = !empty($newPhonenumber) ? $newPhonenumber : $currentPhonenumber;
    $emailToUpdate = !empty($newEmail) ? $newEmail : $currentEmail;
    $genderToUpdate = !empty($newGender) ? $newGender : $currentGender;

    if (!empty($newPassword)) {
        $stmt = $pdo->prepare('UPDATE account SET fullname = :fullname, phonenumber = :phonenumber, email = :email, gender = :gender, password = :password WHERE id = :id');
        $stmt->bindValue(':fullname', $fullnameToUpdate);
        $stmt->bindValue(':phonenumber', $phonenumberToUpdate);
        $stmt->bindValue(':email', $emailToUpdate);
        $stmt->bindValue(':gender', $genderToUpdate);
        $stmt->bindValue(':password', $newPassword);
        $stmt->bindValue(':id', $userId);
    } else {
        $stmt = $pdo->prepare('UPDATE account SET fullname = :fullname, phonenumber = :phonenumber, email = :email, gender = :gender WHERE id = :id');
        $stmt->bindValue(':fullname', $fullnameToUpdate);
        $stmt->bindValue(':phonenumber', $phonenumberToUpdate);
        $stmt->bindValue(':email', $emailToUpdate);
        $stmt->bindValue(':gender', $genderToUpdate);
        $stmt->bindValue(':id', $userId);
    }

    if ($stmt->execute()) {
        echo "<script>showAlert('Profile updated successfully.');</script>";
    } else {
        echo "<script>showAlert('Failed to update profile.');</script>";
    }
}

// Add Question
function getUserId($pdo, $username) {
    $stmt = $pdo->prepare('SELECT id FROM account WHERE username = :username');
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    return $stmt->fetchColumn(); // Return user ID
}

function saveQuestion($pdo, $user_id, $module_id, $question_text) {
    $stmt = $pdo->prepare("INSERT INTO question (user_id, module_id, question_text) VALUES (:user_id, :module_id, :question_text)");
    $stmt->bindValue(':user_id', $user_id);
    $stmt->bindValue(':module_id', $module_id);
    $stmt->bindValue(':question_text', $question_text);
    $stmt->execute();
    return $pdo->lastInsertId();
}

function uploadImages($pdo, $question_id, $images, $uploadDirectory = 'image/') {
    for ($i = 0; $i < count($images['name']); $i++) {
        if ($images['error'][$i] == 0) {
            $upload_file = $uploadDirectory . basename($images['name'][$i]);
            $imageFileType = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));
            $new_file_name = $uploadDirectory . uniqid() . '.' . $imageFileType;

            $check = getimagesize($images['tmp_name'][$i]);
            if ($check !== false) {
                if (move_uploaded_file($images['tmp_name'][$i], $new_file_name)) {
                    $stmt = $pdo->prepare('INSERT INTO image SET question_id = :question_id, image_link = :image_link');
                    $stmt->bindValue(':question_id', $question_id);
                    $stmt->bindValue(':image_link', $new_file_name);
                    $stmt->execute();
                } else {
                    throw new Exception("An error occurred while uploading the image.");
                }
            } else {
                throw new Exception("File is not an image.");
            }
        }
    }
}

// Add Comment
function addComment($pdo, $question_id, $username, $comment_text) {
    $query = "INSERT INTO comments (question_id, username, comment_text) VALUES (:question_id, :username, :comment_text)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'question_id' => $question_id,
        'username' => $username,
        'comment_text' => $comment_text
    ]);
}

// Edit Post
function getQuestionById($pdo, $question_id) {
    $stmt = $pdo->prepare("SELECT q.*, a.username, GROUP_CONCAT(i.image_link) AS images
                           FROM question q
                           JOIN account a ON q.user_id = a.id
                           LEFT JOIN image i ON q.id = i.question_id
                           WHERE q.id = :question_id
                           GROUP BY q.id");
    $stmt->execute(['question_id' => $question_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateQuestion($pdo, $question_id, $module_id, $question_text) {
    $stmt = $pdo->prepare("UPDATE question SET module_id = :module_id, question_text = :question_text WHERE id = :question_id");
    $stmt->execute(['module_id' => $module_id, 'question_text' => $question_text, 'question_id' => $question_id]);
}

function deleteOldImages($pdo, $question_id, $existing_images) {
    $stmt = $pdo->prepare("SELECT image_link FROM image WHERE question_id = :question_id");
    $stmt->execute(['question_id' => $question_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($images as $image) {
        if (!in_array($image['image_link'], $existing_images)) {
            if (file_exists($image['image_link'])) {
                unlink($image['image_link']);
            }
            $stmt = $pdo->prepare("DELETE FROM image WHERE question_id = :question_id AND image_link = :image_link");
            $stmt->execute(['question_id' => $question_id, 'image_link' => $image['image_link']]);
        }
    }
}

function uploadNewImages($pdo, $question_id, $images, $uploadDir = 'image/') {
    foreach ($images['tmp_name'] as $key => $tmp_name) {
        if (!empty($images['name'][$key])) {
            $file_name = basename($images['name'][$key]);
            $file_path = $uploadDir . $file_name;
            move_uploaded_file($tmp_name, $file_path);
            $stmt = $pdo->prepare("INSERT INTO image (question_id, image_link) VALUES (:question_id, :image_link)");
            $stmt->execute(['question_id' => $question_id, 'image_link' => $file_path]);
        }
    }
}

// Delete Post
function getUserIdDel($pdo, $username) {
    $stmt = $pdo->prepare('SELECT id FROM account WHERE username = :username');
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch();
    return $user['id'];
}

function getQuestion($pdo, $question_id, $user_id) {
    $stmt = $pdo->prepare('SELECT * FROM question WHERE id = :id AND user_id = :user_id');
    $stmt->bindValue(':id', $question_id);
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetch();
}

function deleteImages($pdo, $question_id) {
    $stmt = $pdo->prepare('SELECT image_link FROM image WHERE question_id = :question_id');
    $stmt->bindValue(':question_id', $question_id);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($images as $image) {
        if (file_exists($image['image_link'])) {
            unlink($image['image_link']);
        }
    }

    $stmt = $pdo->prepare('DELETE FROM image WHERE question_id = :question_id');
    $stmt->bindValue(':question_id', $question_id);
    $stmt->execute();
}

function deleteQuestion($pdo, $question_id) {
    $stmt = $pdo->prepare('DELETE FROM question WHERE id = :id');
    $stmt->bindValue(':id', $question_id);
    $stmt->execute();
}

// Manage Module
function isAdmin($username) {
    return $username === 'nhanduc';
}

function isModuleExists($pdo, $moduleName) {
    $query = "SELECT COUNT(*) FROM module WHERE module_name = :module_name";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':module_name', $moduleName);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    return $count > 0;
}

function addModule($pdo, $moduleName) {
    $query = "INSERT INTO module (module_name) VALUES (:module_name)";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':module_name', $moduleName);
    $stmt->execute();
}

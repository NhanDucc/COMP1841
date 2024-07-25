<?php
// Index
function checklogoutUser() {
    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Signup
function checkUsernameExists($pdo, $username) {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM account WHERE username = ?");
    $checkStmt->execute([$username]);
    return $checkStmt->fetchColumn();
}

function saveUser($pdo, $fullname, $phonenumber, $email, $gender, $username, $password) {
    $stmt = $pdo->prepare("INSERT INTO account (fullname, phonenumber, email, gender, username, password) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$fullname, $phonenumber, $email, $gender, $username, $password]);
}

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
    $pdo->beginTransaction(); // Bắt đầu một giao dịch

    try {
        // Xóa hình ảnh liên quan đến câu hỏi của người dùng
        $stmt = $pdo->prepare('DELETE image FROM image INNER JOIN question ON image.question_id = question.id WHERE question.user_id = :user_id');
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        // Xóa câu hỏi của người dùng
        $stmt = $pdo->prepare('DELETE FROM question WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        // Xóa tài khoản người dùng
        $stmt = $pdo->prepare('DELETE FROM account WHERE id = :id');
        $stmt->bindValue(':id', $userId);
        $stmt->execute();

        $pdo->commit(); // Commit giao dịch nếu tất cả các bước thành công

        session_destroy(); // Phá hủy phiên làm việc
        echo "<script>
                 window.location.href = 'login.php';
               </script>"; // Chuyển hướng đến trang đăng nhập bằng JavaScript
        exit; // Kết thúc script
    } catch (PDOException $e) {
        $pdo->rollBack(); // Rollback giao dịch nếu có lỗi
        echo "<script>showAlert('Failed to delete account and related data.');</script>"; // Hiển thị thông báo lỗi
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
        echo "<script>showAlert('Profile updated successfully.');</script>"; // Hiển thị thông báo cập nhật thành công
    } else {
        echo "<script>showAlert('Failed to update profile.');</script>"; // Hiển thị thông báo lỗi cập nhật
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
    return $pdo->lastInsertId(); // Return ID of the newly added question
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
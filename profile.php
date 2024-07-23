<?php
session_start(); // Bắt đầu hoặc tiếp tục phiên làm việc

include 'includes/DatabaseConnection.php'; // Bao gồm tệp kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit; // Kết thúc script
}

// Xử lý đăng xuất
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy(); // Phá hủy phiên làm việc (đăng xuất)
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập
    exit; // Kết thúc script
}

try {
    // Lấy chi tiết người dùng từ cơ sở dữ liệu dựa trên tên người dùng trong phiên làm việc
    $stmt = $pdo->prepare('SELECT * FROM account WHERE username = :username');
    $stmt->bindValue(':username', $_SESSION['username']);
    $stmt->execute();
    $user = $stmt->fetch(); // Lưu thông tin người dùng vào biến $user

    // Lấy các câu hỏi của người dùng kèm theo module và hình ảnh liên quan
    $stmt = $pdo->prepare("SELECT q.id, q.question_text, q.time_post, GROUP_CONCAT(i.image_link) AS images, m.module_name 
                           FROM question q 
                           LEFT JOIN image i ON q.id = i.question_id 
                           LEFT JOIN module m ON q.module_id = m.id
                           WHERE q.user_id = :user_id 
                           GROUP BY q.id, m.module_name 
                           ORDER BY q.time_post DESC");
    $stmt->bindValue(':user_id', $user['id']);
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lưu tất cả các câu hỏi vào biến $questions

    // Bao gồm tệp template để hiển thị thông tin người dùng
    include 'templates/profile.html.php';

    // Kiểm tra nếu yêu cầu là POST để xử lý cập nhật hoặc xóa tài khoản
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete']) && $_POST['delete'] == 'delete') {
            // Bắt đầu xử lý xóa tài khoản
            $pdo->beginTransaction(); // Bắt đầu một giao dịch

            try {
                // Xóa hình ảnh liên quan đến câu hỏi của người dùng
                $stmt = $pdo->prepare('DELETE image FROM image INNER JOIN question ON image.question_id = question.id WHERE question.user_id = :user_id');
                $stmt->bindValue(':user_id', $user['id']);
                $stmt->execute();

                // Xóa câu hỏi của người dùng
                $stmt = $pdo->prepare('DELETE FROM question WHERE user_id = :user_id');
                $stmt->bindValue(':user_id', $user['id']);
                $stmt->execute();

                // Xóa tài khoản người dùng
                $stmt = $pdo->prepare('DELETE FROM account WHERE id = :id');
                $stmt->bindValue(':id', $user['id']);
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
        } else {
            // Xử lý cập nhật thông tin người dùng
            $newEmail = $_POST['email'];
            $newPassword = $_POST['password'];

            // Cập nhật email chỉ khi nó không rỗng, nếu không thì giữ nguyên email cũ
            if (!empty($newEmail)) {
                $emailToUpdate = $newEmail;
            } else {
                $emailToUpdate = $user['email'];
            }

            // Cập nhật mật khẩu chỉ khi nó không rỗng
            if (!empty($newPassword)) {
                $stmt = $pdo->prepare('UPDATE account SET email = :email, password = :password WHERE id = :id');
                $stmt->bindValue(':email', $emailToUpdate);
                $stmt->bindValue(':password', $newPassword);
                $stmt->bindValue(':id', $user['id']);
            } else {
                $stmt = $pdo->prepare('UPDATE account SET email = :email WHERE id = :id');
                $stmt->bindValue(':email', $emailToUpdate);
                $stmt->bindValue(':id', $user['id']);
            }

            if ($stmt->execute()) {
                echo "<script>showAlert('Profile updated successfully.');</script>"; // Hiển thị thông báo cập nhật thành công
            } else {
                echo "<script>showAlert('Failed to update profile.');</script>"; // Hiển thị thông báo lỗi cập nhật
            }
        }
    }
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage(); // Hiển thị thông báo lỗi cơ sở dữ liệu
}

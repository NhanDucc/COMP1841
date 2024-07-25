<?php
session_start(); // Bắt đầu hoặc tiếp tục phiên làm việc

include 'includes/DatabaseConnection.php'; // Bao gồm tệp kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập và có tham số 'id' trong GET
if (isset($_SESSION['username']) && isset($_GET['id'])) {
    $question_id = $_GET['id']; // Lấy giá trị của tham số 'id' từ GET

    try {
        // Lấy ID người dùng từ cơ sở dữ liệu dựa trên tên người dùng trong phiên làm việc
        $stmt = $pdo->prepare('SELECT id FROM account WHERE username = :username'); // Chuẩn bị câu truy vấn SQL
        $stmt->bindValue(':username', $_SESSION['username']); // Gán giá trị cho tham số ':username' trong câu truy vấn
        $stmt->execute(); // Thực thi câu truy vấn
        $user = $stmt->fetch(); // Lấy kết quả truy vấn và lưu vào biến $user
        $user_id = $user['id']; // Lưu ID người dùng vào biến $user_id

        // Kiểm tra nếu người dùng là chủ sở hữu của câu hỏi
        $stmt = $pdo->prepare('SELECT * FROM question WHERE id = :id AND user_id = :user_id'); // Chuẩn bị câu truy vấn SQL
        $stmt->bindValue(':id', $question_id); // Gán giá trị cho tham số ':id' trong câu truy vấn
        $stmt->bindValue(':user_id', $user_id); // Gán giá trị cho tham số ':user_id' trong câu truy vấn
        $stmt->execute(); // Thực thi câu truy vấn
        $question = $stmt->fetch(); // Lấy kết quả truy vấn và lưu vào biến $question

        // Nếu tìm thấy câu hỏi thuộc về người dùng hiện tại
        if ($question) {
            // Lấy các liên kết hình ảnh liên quan đến câu hỏi
            $stmt = $pdo->prepare('SELECT image_link FROM image WHERE question_id = :question_id'); // Chuẩn bị câu truy vấn SQL
            $stmt->bindValue(':question_id', $question_id); // Gán giá trị cho tham số ':question_id' trong câu truy vấn
            $stmt->execute(); // Thực thi câu truy vấn
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả kết quả truy vấn và lưu vào biến $images

            // Xóa các tệp hình ảnh từ hệ thống tệp
            foreach ($images as $image) {
                // Kiểm tra nếu tệp hình ảnh tồn tại
                if (file_exists($image['image_link'])) {
                    unlink($image['image_link']); // Xóa tệp hình ảnh
                }
            }

            // Xóa các liên kết hình ảnh từ cơ sở dữ liệu
            $stmt = $pdo->prepare('DELETE FROM image WHERE question_id = :question_id'); // Chuẩn bị câu truy vấn SQL
            $stmt->bindValue(':question_id', $question_id); // Gán giá trị cho tham số ':question_id' trong câu truy vấn
            $stmt->execute(); // Thực thi câu truy vấn

            // Xóa câu hỏi từ cơ sở dữ liệu
            $stmt = $pdo->prepare('DELETE FROM question WHERE id = :id'); // Chuẩn bị câu truy vấn SQL
            $stmt->bindValue(':id', $question_id); // Gán giá trị cho tham số ':id' trong câu truy vấn
            $stmt->execute(); // Thực thi câu truy vấn

            // Trả về phản hồi JSON thành công
            header('Content-Type: application/json'); // Đặt tiêu đề phản hồi là JSON
            echo json_encode(array('success' => true)); // Trả về JSON thông báo thành công
            exit; // Kết thúc script
        } else {
            echo 'Failed to delete question.'; // Hiển thị thông báo lỗi nếu không tìm thấy câu hỏi
        }
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage(); // Hiển thị thông báo lỗi cơ sở dữ liệu nếu có ngoại lệ xảy ra
    }
} else {
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập hoặc không có tham số 'id'
    exit; // Kết thúc script
}

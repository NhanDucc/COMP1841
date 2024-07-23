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
    session_destroy(); // Hủy phiên làm việc
    header('Location: login.php'); // Chuyển hướng đến trang đăng nhập
    exit; // Kết thúc script
}

// Kiểm tra xem có tham số 'id' trong GET không
if (isset($_GET['id'])) {
    $question_id = $_GET['id']; // Lấy giá trị của tham số 'id' từ GET

    try {
        // Lấy thông tin bài đăng dựa trên ID câu hỏi
        $stmt = $pdo->prepare("SELECT q.*, a.username, GROUP_CONCAT(i.image_link) AS images
                               FROM question q
                               JOIN account a ON q.user_id = a.id
                               LEFT JOIN image i ON q.id = i.question_id
                               WHERE q.id = :question_id
                               GROUP BY q.id");
        $stmt->execute(['question_id' => $question_id]); // Thực thi câu truy vấn với tham số 'question_id'
        $question = $stmt->fetch(PDO::FETCH_ASSOC); // Lấy kết quả truy vấn và lưu vào biến $question

        // Kiểm tra nếu tìm thấy câu hỏi và người dùng hiện tại là chủ sở hữu của câu hỏi
        if ($question && $question['username'] == $_SESSION['username']) {
            // Xử lý khi có yêu cầu POST
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $module_id = $_POST['module']; // Lấy giá trị của 'module' từ POST
                $question_text = $_POST['question']; // Lấy giá trị của 'question' từ POST
                $existing_images = isset($_POST['existing_images']) ? explode(',', $_POST['existing_images']) : []; // Lấy danh sách các hình ảnh hiện có

                // Cập nhật câu hỏi trong cơ sở dữ liệu
                $stmt = $pdo->prepare("UPDATE question SET module_id = :module_id, question_text = :question_text WHERE id = :question_id");
                $stmt->execute(['module_id' => $module_id, 'question_text' => $question_text, 'question_id' => $question_id]); // Thực thi câu truy vấn với các tham số

                // Xóa các hình ảnh không còn tồn tại trong existing_images
                $stmt = $pdo->prepare("SELECT image_link FROM image WHERE question_id = :question_id");
                $stmt->execute(['question_id' => $question_id]); // Thực thi câu truy vấn để lấy các hình ảnh liên kết với câu hỏi
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả kết quả truy vấn và lưu vào biến $images
                foreach ($images as $image) {
                    if (!in_array($image['image_link'], $existing_images)) {
                        if (file_exists($image['image_link'])) {
                            unlink($image['image_link']); // Xóa file khỏi hệ thống nếu không tồn tại trong existing_images
                        }
                        $stmt = $pdo->prepare("DELETE FROM image WHERE question_id = :question_id AND image_link = :image_link");
                        $stmt->execute(['question_id' => $question_id, 'image_link' => $image['image_link']]); // Thực thi câu truy vấn để xóa hình ảnh từ cơ sở dữ liệu
                    }
                }

                // Xử lý hình ảnh mới
                if (!empty($_FILES['images']['name'][0])) {
                    $uploadDir = 'image/'; // Thư mục lưu trữ hình ảnh
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        if (!empty($_FILES['images']['name'][$key])) {
                            $file_name = basename($_FILES['images']['name'][$key]); // Lấy tên file
                            $file_path = $uploadDir . $file_name; // Đường dẫn lưu trữ file
                            move_uploaded_file($tmp_name, $file_path); // Di chuyển file tải lên vào thư mục lưu trữ
                            $stmt = $pdo->prepare("INSERT INTO image (question_id, image_link) VALUES (:question_id, :image_link)");
                            $stmt->execute(['question_id' => $question_id, 'image_link' => $file_path]); // Thực thi câu truy vấn để thêm hình ảnh vào cơ sở dữ liệu
                        }
                    }
                }

                // Trả về phản hồi JSON thành công
                echo json_encode(['success' => true, 'message' => 'Question updated successfully']);
                exit; // Kết thúc script
            }

            include 'templates/editpost.html.php'; // Bao gồm tệp HTML để hiển thị giao diện chỉnh sửa bài đăng
        } else {
            // Trả về phản hồi JSON thất bại nếu người dùng không có quyền chỉnh sửa bài đăng
            echo json_encode(['success' => false, 'message' => 'You are not authorized to edit this post.']);
        }
    } catch (PDOException $e) {
        // Trả về phản hồi JSON thất bại nếu có lỗi cơ sở dữ liệu
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Trả về phản hồi JSON thất bại nếu yêu cầu không hợp lệ
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

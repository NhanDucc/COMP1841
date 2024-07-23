<?php
session_start(); // Bắt đầu session để quản lý trạng thái đăng nhập của người dùng
include 'includes/DatabaseConnection.php'; // Bao gồm file kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['username'])) {
    // Nếu người dùng yêu cầu đăng xuất
    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
        session_destroy(); // Hủy bỏ tất cả các session
        header('Location: login.php'); // Chuyển hướng người dùng đến trang đăng nhập
        exit; // Kết thúc kịch bản
    }

    // Kiểm tra phương thức yêu cầu HTTP có phải là POST không
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selected_option = $_POST['module']; // Lấy tùy chọn module được chọn từ form
        // Hiển thị tùy chọn đã chọn
        echo "Option đã chọn: " . $selected_option;
    }

    // Kiểm tra nếu yêu cầu là POST và hành động là 'post' (đăng câu hỏi)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'post') {
        $question_text = $_POST['question'] ?? ''; // Lấy nội dung câu hỏi từ form hoặc để trống nếu không có
        $module_id = $_POST['module'] ?? ''; // Lấy ID module từ form hoặc để trống nếu không có
        $username = $_SESSION['username']; // Lấy tên người dùng từ session
        
        try {
            // Truy vấn để lấy ID của người dùng dựa vào username
            $stmt = $pdo->prepare('SELECT id FROM account WHERE username = :username');
            $stmt->bindValue(':username', $username);  
            $stmt->execute(); // Thực thi truy vấn
            $user = $stmt->fetch(); // Lấy kết quả truy vấn
            $user_id = $user['id']; // Lấy ID người dùng từ kết quả truy vấn

            // Kiểm tra nếu không tìm thấy ID người dùng
            if (!$user_id) {
                echo "Không tìm thấy ID của người dùng.";
            } else {
                // Chuẩn bị truy vấn để lưu câu hỏi vào cơ sở dữ liệu
                $stmt = $pdo->prepare("INSERT INTO question (user_id, module_id, question_text) VALUES (:user_id, :module_id, :question_text)");
                $stmt->bindValue(':user_id', $user_id);
                $stmt->bindValue(':module_id', $module_id);
                $stmt->bindValue(':question_text', $question_text);

                // Kiểm tra nếu truy vấn thực thi thành công
                if ($stmt->execute()) {
                    $question_id = $pdo->lastInsertId(); // Lấy ID của câu hỏi vừa được thêm vào
                    echo "Câu hỏi đã được lưu với ID: " . $question_id;

                    // Xử lý upload ảnh nếu có
                    $images = $_FILES["images"] ?? []; // Lấy dữ liệu các file ảnh từ form
                    $uploadDirectory = 'image/'; // Đường dẫn thư mục lưu ảnh

                    // Lặp qua từng file ảnh để xử lý upload
                    for ($i = 0; $i < count($images['name']); $i++) {
                        if ($images['error'][$i] == 0) { // Kiểm tra nếu không có lỗi upload
                            $upload_file = $uploadDirectory . basename($images['name'][$i]); // Đường dẫn file upload tạm thời
                            $imageFileType = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION)); // Lấy loại file ảnh
                            $new_file_name = $uploadDirectory . uniqid() . '.' . $imageFileType; // Tạo tên file mới duy nhất

                            // Kiểm tra nếu file thực sự là ảnh
                            $check = getimagesize($images['tmp_name'][$i]);
                            if ($check !== false) {
                                // Di chuyển file từ thư mục tạm thời đến thư mục đích
                                if (move_uploaded_file($images['tmp_name'][$i], $new_file_name)) {
                                    // Lưu thông tin ảnh vào cơ sở dữ liệu
                                    $stmt = $pdo->prepare('INSERT INTO image SET question_id = :question_id, image_link = :image_link');
                                    $stmt->bindValue(':question_id', $question_id);
                                    $stmt->bindValue(':image_link', $new_file_name);
                                    $stmt->execute();
                                } else {
                                    echo "Có lỗi xảy ra khi upload ảnh.";
                                }
                            } else {
                                echo "File không phải là ảnh.";
                            }
                        }
                    }

                    // Chuyển hướng người dùng đến trang chủ sau khi hoàn tất
                    header('Location: index.php');
                    exit; // Kết thúc kịch bản
                } else {
                    echo "Lưu câu hỏi thất bại.";
                }
            }
        } catch (PDOException $e) {
            // Xử lý lỗi kết nối cơ sở dữ liệu
            echo 'Lỗi database: ' . $e->getMessage();
        }
    }

    // Lấy tất cả câu hỏi và ảnh liên quan với từ khóa tìm kiếm nếu có
    try {
        $search_term = isset($_GET['search']) ? $_GET['search'] : ''; // Lấy từ khóa tìm kiếm từ URL hoặc để trống nếu không có
        // Truy vấn để lấy tất cả các câu hỏi cùng thông tin liên quan
        $query = "SELECT q.id, q.question_text, q.time_post, a.username, m.module_name, GROUP_CONCAT(i.image_link) AS images 
                  FROM question q 
                  JOIN module m ON q.module_id = m.id
                  JOIN account a ON q.user_id = a.id 
                  LEFT JOIN image i ON q.id = i.question_id ";

        // Nếu có từ khóa tìm kiếm, thêm điều kiện vào truy vấn
        if ($search_term) {
            $query .= "WHERE q.question_text LIKE :search_term ";
        }

        // Sắp xếp kết quả theo thời gian đăng câu hỏi
        $query .= "GROUP BY q.id ORDER BY q.time_post DESC";
        $stmt = $pdo->prepare($query);

        // Nếu có từ khóa tìm kiếm, gán giá trị cho biến trong truy vấn
        if ($search_term) {
            $stmt->bindValue(':search_term', '%' . $search_term . '%');
        }

        $stmt->execute(); // Thực thi truy vấn
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC); // Lấy tất cả kết quả dưới dạng mảng kết hợp

        include 'templates/index.html.php'; // Bao gồm template hiển thị kết quả

    } catch (PDOException $e) {
        // Xử lý lỗi kết nối cơ sở dữ liệu
        echo 'Lỗi database: ' . $e->getMessage();
    }
} else {
    // Nếu người dùng chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header('Location: login.php');
    exit; // Kết thúc kịch bản
}
?>

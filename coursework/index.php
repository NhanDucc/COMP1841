<?php
session_start(); // Bắt đầu session để quản lý trạng thái đăng nhập của người dùng
include 'includes/DatabaseConnection.php'; // Bao gồm file kết nối cơ sở dữ liệu
include 'includes/DatabaseFunctions.php'; // Bao gồm các hàm xử lý cơ sở dữ liệu

if (isset($_SESSION['username'])) {
    // Nếu người dùng yêu cầu đăng xuất
    checklogoutUser();

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
<?php
session_start(); // Bắt đầu hoặc tiếp tục phiên làm việc
include 'includes/DatabaseConnection.php'; // Bao gồm tệp kết nối cơ sở dữ liệu
include 'templates/contact.html.php'; // Bao gồm tệp HTML để hiển thị giao diện liên hệ

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

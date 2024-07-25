<?php
// send_email.php

// Bật hiển thị lỗi để hỗ trợ gỡ lỗi
error_reporting(E_ALL); // Báo cáo tất cả các loại lỗi
ini_set('display_errors', 1); // Hiển thị lỗi

// Kiểm tra phương thức yêu cầu (request method) là POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Làm sạch và xác thực dữ liệu đầu vào
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING); // Lấy và làm sạch giá trị của 'name' từ POST
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL); // Lấy và xác thực giá trị của 'email' từ POST
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING); // Lấy và làm sạch giá trị của 'message' từ POST

    // Kiểm tra tính hợp lệ của dữ liệu
    if ($name && $email && $message) {
        // Đặt địa chỉ email người nhận
        $to = "hidenkouji@gmail.com";
        
        // Đặt tiêu đề email
        $subject = "New Message from $name";
        
        // Đặt các header của email
        $headers = "From: " . $email . "\r\n" .
                   "Reply-To: " . $email . "\r\n" .
                   "X-Mailer: PHP/" . phpversion(); // Thông tin về trình gửi mail
        
        // Đặt nội dung email
        $body = "Name: $name\n\n";
        $body .= "Email: $email\n\n";
        $body .= "Message:\n$message";
        
        // Gửi email
        $check = mail($to, $subject, $body, $headers);
        
        // Kiểm tra xem email đã được gửi thành công chưa
        if ($check) {
            echo "Success: Your message has been sent.";
        } else {
            echo "Error: There was a problem sending your message.";
        }
    } else {
        echo "Error: Please fill out all fields correctly."; // Thông báo lỗi nếu dữ liệu không hợp lệ
    }
} else {
    echo "Error: Invalid request."; // Thông báo lỗi nếu phương thức yêu cầu không phải là POST
}

?>

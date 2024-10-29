<?php

$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "test";



// Tạo kết nối đến cơ sở dữ liệu
$conn = new mysqli($hostname, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Có thể sử dụng $conn để kết nối trong các tệp khác
?>

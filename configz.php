<?php

$hostname = "sql100.infinityfree.com";
$username = "if0_37605318";
$password = "ps3u7HoVlbRL";
$dbname = "if0_37605318_lichhoc";



// Tạo kết nối đến cơ sở dữ liệu
$conn = new mysqli($hostname, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>

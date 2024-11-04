<?php
// URL ZIP của repository trên GitHub (nhánh chính master/main)
$zipUrl = "https://github.com/trantienvn/LichhocPHP/raw/refs/heads/main/repo.zip"; // Thay "username/repo" bằng repo của bạn

// Đường dẫn lưu tạm cho file ZIP tải về (đặt trong thư mục hiện tại)
$zipFile = __DIR__ . "/repo.zip";

// Tải file ZIP từ GitHub về thư mục hiện tại
file_put_contents($zipFile, file_get_contents($zipUrl));

// Mở file ZIP và giải nén vào thư mục hiện tại
$zip = new ZipArchive;
$res = $zip->open($zipFile);
if ($res === TRUE) {
    // Giải nén vào thư mục hiện tại (__DIR__)
    $zip->extractTo(__DIR__);
    $zip->close();
    echo "Giải nén thành công!";
} else {
    echo "Không thể mở file ZIP.";
}

// Xóa file ZIP sau khi giải nén nếu muốn
unlink($zipFile);
?>

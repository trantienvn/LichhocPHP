<?php
// Bắt đầu session nếu chưa có
session_start();

// Xóa toàn bộ session
session_unset();
session_destroy();

// Xóa tất cả các cookie
foreach ($_COOKIE as $name => $value) {
    setcookie($name, '', time() - 3600, '/');
}

// Thêm tiêu đề HTTP để tránh lưu cache
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

// Xóa Local Storage và Session Storage bằng JavaScript
echo "
    <script>
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = 'login';
    </script>
";

exit();
?>

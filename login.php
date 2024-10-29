<?php
session_start();
if (isset($_COOKIE['hash'])) {
    header("Location: /"); // Chuyển hướng nếu đã đăng nhập
    exit();
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Thêm jQuery -->
</head>

<body>
    <h1>Đăng Nhập</h1>
    <form id="loginForm">
        <input type="text" name="username" id="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" id="password" placeholder="Mật khẩu" required>
        <p id="errorMessage" style="color: red;"></p>
        <button type="submit">Đăng Nhập</button>
    </form>
    <script>
        function md5Password(password) {
            return CryptoJS.MD5(password).toString();
        }
        $(document).ready(function () {
            $('#loginForm').submit(function (e) {
                e.preventDefault(); // Ngăn chặn form submit thông thường

                // Lấy dữ liệu từ form
                const usernametxt = $('#username').val();
                const username = usernametxt.toLowerCase();
                const passwordtxt = $('#password').val();
                const password = md5Password(passwordtxt);

                // Gửi yêu cầu AJAX tới API
                $.ajax({
                    url: 'auth.php',
                    type: 'POST',
                    data: { username: username, password: password },
                    dataType: 'json',
                    success: function (response) {
                        if (response.error === false) {
                            // Lưu session nếu đăng nhập thành công
                            window.location.href = '/'; // Chuyển hướng tới trang thời gian biểu
                        } else {
                            const message = response.message ? response.message : '';
                            $('#errorMessage').text(`Lỗi: ${message}`);
                        }
                    },
                    error: function () {
                        $('#errorMessage').text('Có lỗi xảy ra khi gửi yêu cầu. Vui lòng thử lại sau.');
                    }
                });
            });
        });
    </script>
</body>

</html>
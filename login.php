<?php
if (isset($_COOKIE['hash'])) {
  header("Location: /"); // Chuyển hướng nếu đã đăng nhập
  exit();
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng nhập</title>
   <meta
        name="description"
        content="Hệ thống xem lịch học ICTU"
    />
    <meta
        name="keywords"
        content="Hệ thống xem lịch học ICTU"
    />
    <meta
        property="og:description"
        content="Hệ thống xem lịch học ICTU"
    />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,200..1000;1,200..1000&family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playwrite+GB+S:ital,wght@0,100..400;1,100..400&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="login.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Thêm jQuery -->
  <script src="./fetch.js"></script>
</head>

<body>
  <div class="container">
    <form id="loginForm">
      <label for="username">Mã sinh viên:</label>
      <input type="text" id="username" placeholder="Mã sinh viên..." />
      <label for="password">Mật khẩu:</label>
      <input type="password" id="password" placeholder="Mật khẩu..." />
      <p id="errorMessage" style="color: red;"></p>
      <button type="submit">Đăng nhập</button>
    </form>
    <div class="ear-l"></div>
    <div class="ear-r"></div>
    <div class="panda-face">
      <div class="blush-l"></div>
      <div class="blush-r"></div>
      <div class="eye-l">
        <div class="eyeball-l"></div>
      </div>
      <div class="eye-r">
        <div class="eyeball-r"></div>
      </div>
      <div class="nose"></div>
      <div class="mouth"></div>
    </div>
    <div class="hand-l"></div>
    <div class="hand-r"></div>
    <div class="paw-l"></div>
    <div class="paw-r"></div>
  </div>
  <script src="login.js"></script>
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
                        $('#errorMessage').text('Có lỗi xảy ra, có thể máy chủ nhà trường đang lỗi. Vui lòng thử lại sau');
                    }
                });
            });
        });
  </script>
</body>

</html>
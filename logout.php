<?php
setcookie("username", "", time() - 3600, "/");
setcookie("hash", "", time() - 3600, "/");
header("Location: login"); // Chuyển hướng về trang đăng nhập
?>
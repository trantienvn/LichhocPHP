<?php
setcookie("username", "", time() - 3600, "/");
setcookie("hash", "", time() - 3600, "/");
header("Location: login.php"); // Chuyển hướng về trang đăng nhập
?>
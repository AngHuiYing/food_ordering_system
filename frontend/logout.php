<?php
// logout.php
session_start();
session_unset();
session_destroy();

header('Location: login.php'); // 重定向到登录页面
exit();
?>

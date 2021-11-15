<?php 
include_once dirname(__FILE__) . "/settings.php";

// SESSIONクリア
unset($_SESSION[SESSION_BASE_NAME]);

// ログイン画面へ
header("Location: login.php?logout");
exit;



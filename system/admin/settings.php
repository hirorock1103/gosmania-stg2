<?php 
// セッション名
define("SESSION_BASE_NAME", "gosmania");

session_name(constant("SESSION_BASE_NAME"));
session_start();


// アプリケーション設定
include_once dirname(__FILE__) . "/../../common/config.php";

$file = basename($_SERVER['SCRIPT_NAME'], ".php");
if ($file != "login" && $file != "logout") { // ログイン／ログアウト画面以外で
	if(!isset($_SESSION[SESSION_BASE_NAME]['login_info'])) { // SESSION情報なければ、もしくはリリース作業中のアクセスだったら
		header("Location: logout.php?logout");
		exit;
	}
}

//ip制限
$allow_ip = array("119.243.84.173");
if( !in_array($_SERVER["REMOTE_ADDR"], $allow_ip)  ){
	if ($file != "login" && $file != "logout") { // ログイン／ログアウト画面以外で
		header("Location: logout.php?logout");
		exit;
	}
}

// クッキー有効期限セット
//$limit = time() + (60 * 30);// 30min
$limit = time() + (60 * 30 * 24);// debug
setcookie(session_name(), session_id(), $limit, '/');

// 共通で使う配列等々

//Statusの配列
$def_status = get_defined_array(DEF_STATUS);

//必須フラグ
$must_label = '<span class="label label-danger"> 必須 </span>';

<?php 
// セッション名
define("SESSION_BASE_NAME", "gosmania_auth");

session_name(constant("SESSION_BASE_NAME"));
session_start();

// ログイン／ログアウト画面以外でSESSION情報なければログアウトする
$file = basename($_SERVER['SCRIPT_NAME'], ".php");
if ($file != "login" && $file != "logout" ) {
	if(!isset($_SESSION[SESSION_BASE_NAME]['login_info'])){
		header("Location: login.php");
		exit;
	}else{
		$ses['cs_name'] = $_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Name'];
		$ses['cs_id'] = $_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Id'];
		$ses['cs_seq'] = $_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Seq'];
		$ses['cs_timelimit'] = $_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Timelimit'];
	}
}


// クッキー有効期限セット
//$limit = time() + (60 * 30);// 30min
$limit = time() + (60 * 30 * 24);// debug
setcookie(session_name(), session_id(), $limit, '/');

// アプリケーション設定
include_once dirname(__FILE__) . "/../common/config.php";

// 
include_once dirname(__FILE__) . "/functions.php";




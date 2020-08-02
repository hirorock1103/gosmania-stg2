<?php
//
// 本番・開発切替： 本番false 開発true
//
define('APP_DEBUG', true);

//
// 文字コード
//
mb_language("japanese");
mb_internal_encoding("utf-8");
mb_http_input("auto");
mb_http_output("utf-8");


//
// ログ
//
include_once dirname(__FILE__) . "/log.php";

//
// アプリケーション定義
//
if(strpos($_SERVER["REQUEST_URI"],"gosmania/system") !== false){
	include_once dirname(__FILE__) . "/app.php";
}else{
	include_once dirname(__FILE__) . "/app_stg.php";
}

//
// マスタDB
//
include_once dirname(__FILE__) . "/db.php";// $dbh接続

//
// 例外定義
//
include_once dirname(__FILE__) . "/exceptions.php";

//
// 関数定義
//
include_once dirname(__FILE__) . "/functions.php";

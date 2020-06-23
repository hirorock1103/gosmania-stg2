<?php 
// What's this?
// /admin/settings.php からSESSION周りの処理を抜いたもの
// 定数や汎用変数だけを定義している
// 呼び出し元で /admin/functions.php を読み込むことを推奨

// アプリケーション設定
include_once dirname(__FILE__) . "/../common/config.php";

// 共通で使う配列等々

//Statusの配列
$def_status = get_defined_array(DEF_STATUS);

//必須フラグ
$must_label = '<span class="label label-danger"> 必須 </span>';

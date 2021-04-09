<?php 
//画像のパスとファイル名
$fpath = 'admin/image/contents_folder/';
if(isset($_GET['fpath'])) {
    $fpath .= $_GET['fpath']; 
}
$fname = $_GET['fpath'];
//画像のダウンロード
header('Content-Type: application/octet-stream');
header('Content-Length: '.filesize($fpath));
header('Content-disposition: attachment; filename="'.$fname.'"');
readfile($fpath);
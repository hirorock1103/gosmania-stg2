<?php
include_once dirname(__FILE__) . "/settings.php";


$cs_id = (isset($_POST['cs_id']) ? trim_all($_POST['cs_id']) : "");
$cs_zip = (isset($_POST["cs_zip"]) ? trim_all($_POST["cs_zip"]) : "");
$errmsg = '';

// ログイン？
if (!empty($_POST['login'])) {

	$ret = _login($dbh, $cs_id, $cs_zip, $errmsg);

	if($ret) {
		session_regenerate_id(true);
		// TOPへ
		header('Location: ./select.php');
		exit();
		
	}
}


/**********************************************/
// ログイン
/**********************************************/
function _login($dbh, $cs_id, $cs_zip, &$errmsg) {

	if($cs_id == "" || $cs_zip == "") {
		$errmsg = "<br><br>ID、郵便番号を入力してください。";
		return false;
	}
	
	$sql	= "SELECT * FROM Customer ";
	$sql .= "WHERE Cs_Id = :cs_id AND Cs_Zip = :cs_zip ";

	$db = $dbh->prepare($sql);

	$db->bindValue(':cs_id', $cs_id, PDO::PARAM_STR);
	$db->bindValue(':cs_zip', $cs_zip, PDO::PARAM_STR);

	if ($db->execute()) {
		if ($row = $db->fetch(PDO::FETCH_ASSOC)) {
				// SESSION保存
				$_SESSION[SESSION_BASE_NAME]['login_info'] = array();
				$_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Seq']		= $row['Cs_Seq'];
				$_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Id']			= $row['Cs_Id'];
				$_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Name']		= $row['Cs_Name'];
				$_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Timelimit']	= date( "Y年m月",strtotime($row['Cs_Timelimit'])).'末日';
				return $row;
			}
		}
	//1行もとれなかったら
	$errmsg = "<br><br>ID、郵便番号が無効です。";
	return false;
}

?>
<html lang="ja">
<head><?php include_once dirname(__FILE__) . "/head.php"; ?></head>
<body>
<div class="wrap">
<!--<header></header>-->
<form action="" method="post">
	<section class="section-list page-news GOSMANIA">
			<img src="./image/gos_logo2.png" style="width: 120px; margin-left: 130px;" class="img1">
			<img src="./image/gos_logo2.png" style="width: 120px; " class="img2">
				<div class="block-gosmania2">
				<p class="block-tit">ログイン</p>



				<input type="text" placeholder="GOSMANIA会員番号(下5桁)" name="cs_id" value="<?php echo $cs_id;?>" >
				<input type="text" placeholder="登録郵便番号(ハイフン除く7桁)" name="cs_zip" value="<?php echo $cs_zip;?>">
				 <p class="txt-credit txt-login">※住所変更反映にお時間を頂戴する場合がございます。<br>
				 ログインできない場合は、変更前の郵便番号にて認証をお願いいたします。</p>
				<button class="btn-sub" type="submit" name="login" value="auth">認証</button>
				<?php echo $errmsg; ?>
				</div>
		<div class="block-gosmania2--comment"><a class="link-type-1" href="tokutei.php">特定商取引法に関する表記</a></div>
	</section>

</form>


<footer>

</footer>


</div><!-- .wrap -->

</body>
</html>

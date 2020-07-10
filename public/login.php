<?php
include_once dirname(__FILE__) . "/settings.php";

$logout_msg = "";
if(isset($_SESSION['logout-msg'])){
	$logout_msg = $_SESSION['logout-msg'];
}
if(isset($_GET['logout'])){
	$_SESSION['logout-msg'] = "logoutしました。";
	header("location:login.php");
	exit();
}
//login画面ではセッションをクリアする
$_SESSION = array();

$cs_id = (isset($_POST['cs_id']) ? trim_all($_POST['cs_id']) : "");
$cs_zip = (isset($_POST["cs_zip"]) ? trim_all($_POST["cs_zip"]) : "");
$errmsg = '';

// ログイン
if (!empty($_POST['login'])) {

	//全角→変換
	$cs_id = mb_convert_kana($cs_id, "n");
	$cs_zip = mb_convert_kana($cs_zip, "n");
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
		$errmsg = "<br><br>GOSMANIA会員番号、登録郵便番号を入力してください。";
		return false;
	}else{
		//桁数などのチェック
		if(!preg_match('/^([0-9]{5})$/', $cs_id)){
			$errmsg = "<br><br>GOSMANIA会員番号は下5桁の入力をお願いします。";
			return false;
		}		
		if(!preg_match('/^([0-9]{7})$/', $cs_zip)){
			$errmsg = "<br><br>郵便番号はハイフンなし7桁の入力をお願いします。";
			return false;
		}	
	}

	//cs_zipをハイフンありに変換
	$cs_zip = substr($cs_zip,0,3) . "-" . substr($cs_zip,3);
	
	$sql	= "SELECT * FROM Customer ";
	$sql .= "WHERE Cs_Id like :cs_id AND Cs_Zip = :cs_zip ";

	$db = $dbh->prepare($sql);

	$db->bindValue(':cs_id', "%".$cs_id, PDO::PARAM_STR);
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
	$errmsg = "<br><br>GOSMANIA会員番号、登録郵便番号が無効です。";
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
			<img src="./image/gos_logo2.png" class="img2">
				<div class="block-gosmania2">
				<p class="block-tit">ログイン</p>



				<input type="text" maxlength="5"   placeholder="GOSMANIA会員番号(下5桁)" name="cs_id" value="<?php echo $cs_id;?>" >
				<input type="text" maxlength="7" placeholder="登録郵便番号(ハイフン除く7桁)" name="cs_zip" value="<?php echo $cs_zip;?>">
				 <p class="txt-credit txt-login">※住所変更反映にお時間を頂戴する場合がございます。<br>
				 ログインできない場合は、変更前の郵便番号にて認証をお願いいたします。</p>
				<button class="btn-sub" type="submit" name="login" value="auth">認証</button>
				<?php if(!empty($errmsg)){  ?>
				<span class="err-msg"><?php echo $errmsg; ?></span>
				<?php } ?>
				</div>
		<div class="block-gosmania2--comment"><a class="link-type-1" href="tokutei.php">特定商取引法に関する表記</a></div>
	</section>

</form>


<footer>

</footer>


</div><!-- .wrap -->

</body>
</html>

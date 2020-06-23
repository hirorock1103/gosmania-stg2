<?php

include_once dirname(__FILE__) . "/settings.php"; 

$login_id = (isset($_POST['login_id']) ? trim_all($_POST['login_id']) : "");
$login_pw = (isset($_POST["login_pw"]) ? trim_all($_POST["login_pw"]) : "");
$errmsg = array();


// ログイン？
if (!empty($_POST['login'])) {

	$ret = _login($dbh, $login_id, $login_pw, $errmsg);
	if($ret) {
		session_regenerate_id(true);
		// TOPへ
		header('Location: ./');
		exit();
		
	}
}


/**********************************************/
// ログイン
/**********************************************/
function _login($dbh, $login_id, $login_pw, &$errmsg) {

	if( $login_id == "" || $login_pw == "" ) {
		$errmsg[] = "ログインID、パスワードを入力してください。";
		return false;
	}
	
	$sql	= "SELECT * FROM Admin ";
	$sql .= "WHERE Ad_Id = :login_id AND Ad_Invalid = :valid ";
	$db = $dbh->prepare($sql);
	$db->bindValue(':login_id', $login_id, PDO::PARAM_STR);
	$db->bindValue(':valid', 0, PDO::PARAM_INT);

	if ($db->execute()) {
		if ($row = $db->fetch(PDO::FETCH_ASSOC)) {
			// ハッシュパスワードに合致する？
			if (password_verify($login_pw, $row['Ad_Password'])) { 
				// SESSION保存
				$_SESSION[SESSION_BASE_NAME]['login_info'] = array();
				$_SESSION[SESSION_BASE_NAME]['login_info']['Ad_Seq']			= $row['Ad_Seq'];
				$_SESSION[SESSION_BASE_NAME]['login_info']['Ad_Id']				= $row['Ad_Id'];
				$_SESSION[SESSION_BASE_NAME]['login_info']['Ad_Name']			= $row['Ad_Name'];
				$_SESSION[SESSION_BASE_NAME]['login_info']['Ad_NameKana']		= $row['Ad_NameKana'];
				return $row;
			}
		}
	}
	$errmsg[] = "ログインID、またはパスワードが無効です。";
	return false;
}



?>
<!DOCTYPE html>
<html>
<?php include 'header.php'; ?>
</head>
	<body style="text-align:center;">
		<div id="login"class="wrapper" style="min-height:100vh;" >
			<!-- Main content -->
			<section class="login">
				<div class="row">
					<a href="#" class="logo">
						<img src="image/login_logo.png" width="100" style="margin-bottom:30px">
					</a>
					<form action="" name="frm_login" method="post">
					<!-- <form action="login.php" name="frm_login" method="post"> -->
						<input name="login_id" placeholder="ログインID" value="<?php echo h($login_id); ?>" class="input_text" type="text" required style="background: url('image/login_id.png') no-repeat;padding-left:55px;"/>
						<br clear="both">
						<input name="login_pw" placeholder="パスワード" value="<?php echo h($login_pw); ?>"	class="input_text" type="password" required style="background: url('image/login_pw.png') no-repeat;padding-left:55px;"/>
						<br clear="both">
						<input name="login" type="submit" value=" ログイン " class="btn import_btn_search" style="margin:10px 20px; width:250px; padding:6px 20px;" />
					</form>
					<?php foreach ($errmsg as $msg) { ?>
						<div class="fc_red"><?php echo h($msg); ?></div>
					<?php } ?>
				</div>
			</section><!-- /.content -->
		</div><!-- ./wrapper -->
	</body>
</html>

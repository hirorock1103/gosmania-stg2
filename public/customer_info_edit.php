<?php
include_once dirname(__FILE__) . "/settings.php";
$def_informationSend = ['希望しない','希望する'];

$customer = $error = []; // 同時に空配列で初期化

if(isset($_POST['confirm']) && !empty($_POST['confirm'])) {
	$customer = params();

	$error = validate($customer);
	// echo '<pre>';
	// var_dump($_POST, $customer, $error);exit;

}else if(isset($_POST['register']) && !empty($_POST['register'])) {
	$customer = params();

	$error = validate($customer);

	$customer['Ci_Creator'] = $ses['cs_name'];
	$result = insert_into_customer_info($dbh, $customer);

	if($result==false) {
		$error['general'] = '登録に失敗しました。';
		$customer = fetch_customer_info_record($dbh, $ses['cs_id']);
	}else {
		// 更新成功
	}
}else {
	// 初回GETアクセス
	$customer = fetch_customer_info_record($dbh, $ses['cs_id']);
	$customer['Ci_MailAddressConfirm'] = $customer['Ci_MailAddress'];
}


// 表示モード
if(!empty($error) || $_SERVER['REQUEST_METHOD'] == 'GET') {
	// エラーがあるか初回ロード
	$display_mode = 'edit';
}else if(isset($_POST['confirm']) && !empty($_POST['confirm'])) {
	$display_mode = 'confirm';
}else if(isset($_POST['register']) && !empty($_POST['register'])) {
	// 完了画面へ
	header('Location: ./complete.php?status=' . rawurlencode(base64_encode('cs_info_update')) );
	exit();
}

function fetch_customer_info_record($dbh, $Cs_Id) {
	$sql = "SELECT * FROM CustomerInfo WHERE Cs_Id = :Cs_Id ORDER BY Ci_Seq DESC LIMIT 1";
	$db = $dbh->prepare($sql);
	$db->bindValue(':Cs_Id', $Cs_Id, PDO::PARAM_STR);
	$db->execute();
	return $db->fetch(PDO::FETCH_ASSOC);
}

function params() {
	$data = [];
	$data['Cs_Id'] = $_SESSION['gosmania_auth']['login_info']['Cs_Id'];

	$data['Ci_MailAddress']					 = filter_input(INPUT_POST, 'mail_address') 				 ? filter_input(INPUT_POST, 'mail_address') : NULL;
	$data['Ci_MailAddressConfirm']	 = filter_input(INPUT_POST, 'mail_address_confirm')	 ? filter_input(INPUT_POST, 'mail_address_confirm') : NULL;
	$data['Ci_Phone']								 = filter_input(INPUT_POST, 'phone_number') 				 ? filter_input(INPUT_POST, 'phone_number') : NULL;
	$data['Ci_InformationSend']			 = filter_input(INPUT_POST, 'n1') !== NULL					 ? intval(filter_input(INPUT_POST, 'n1')) : -1;

	return $data;
}

function validate($data) {
	$error = [];

	$disp_names = [
		'Ci_MailAddress' => 'メールアドレス',
		'Ci_MailAddressConfirm' => 'メールアドレス確認',
		'Ci_InformationSend' => 'メール配信',
		'Ci_Phone' => '連絡がつく電話番号'
	];

	// 必須チェック
	foreach($data as $key => $value) {
		if($value === NULL) {
			$error[$key] = $disp_names[$key] . 'は必須です。';
		}else if($key == 'Ci_InformationSend' && $value == -1) {
			$error[$key] = $disp_names[$key] . 'は必須です。';
		}
	}

	// 個別チェック
	// メールアドレス
	if($data['Ci_MailAddress'] != $data['Ci_MailAddressConfirm']) {
		$error['Ci_MailAddressConfirm'] = 'メールアドレスが一致しません。';
	}else if(!filter_var($data['Ci_MailAddress'], FILTER_VALIDATE_EMAIL)) {
		$error['Ci_MailAddress'] = 'メールアドレスの形式が不正です。';
	}

	// 通知送付
	if($data['Ci_InformationSend'] != 0 && $data['Ci_InformationSend'] != 1) {
		$error['Ci_InformationSend'] = 'メール配信の形式が不正です。';
	}


	// 連絡がつく電話番号
	if(empty($data['Ci_Phone'])){
		$ret['Ci_Phone'] = "連絡先を入力してください。";
	}else{
		if (!preg_match("/^[0-9]+$/", $data['Ci_Phone'])) {
			$ret['Ci_Phone'] = "電話番号は数値のみ入力をお願いします。";
		}

		if( empty($ret['Ci_Phone']) &&  strlen($data['Ci_Phone']) < 10 || strlen($data['Ci_Phone']) > 11){
			$ret['Ci_Phone'] = "電話番号は10桁か11桁での入力となります";
		}
	}


	return $error;
}

function insert_into_customer_info($dbh, $data){
	// INSERTした結果を返す
	// まずINSERT
	$sql = "INSERT INTO CustomerInfo (
		Cs_Id,
		Ci_MailAddress,
		Ci_Phone,
		Ci_InformationSend,
		Ci_Creator,
		Ci_Creatdate
	)VALUE(
		:Cs_Id,
		:Ci_MailAddress,
		:Ci_Phone,
		:Ci_InformationSend,
		:Ci_Creator,
		NOW()
	)";
	$db = $dbh->prepare($sql);
	$db->bindValue(':Cs_Id',								 $data['Cs_Id'],								 PDO::PARAM_STR);
	$db->bindValue(':Ci_MailAddress',				 $data['Ci_MailAddress'],				 PDO::PARAM_STR);
	$db->bindValue(':Ci_Phone', 						 $data['Ci_Phone'],							 PDO::PARAM_STR);
	$db->bindValue(':Ci_InformationSend',		 $data['Ci_InformationSend'],		 PDO::PARAM_INT);
	$db->bindValue(':Ci_Creator',						 $data['Ci_Creator'],						 PDO::PARAM_STR);
	$db->execute();
	$new_seq = intval($dbh->lastInsertId());

	return find_record($dbh, 'CustomerInfo', 'Ci_Seq', $new_seq, 'desc');
}

?>
<html lang="ja">
<head><?php include_once dirname(__FILE__) . "/head.php"; ?></head>
<body>

<div class="wrap">
	<?php include_once dirname(__FILE__) . "/header.php"; ?>

	<form method="POST">
		<?php switch($display_mode) { 
			case 'edit' :?>
				<section class="section-list page-news GOSMANIA">
					<p class="credit-tit" style="margin-bottom:40px;">以下必要事項をご入力の上、<span><br></span>確認ボタンを押してください。</p>
					<p class="credit-tit">お客様情報を入力してください</p>

					<?php if(!empty($error)) { ?>
						<div style="color:red;">
							<strong>エラー</strong><br />
							<?php if(isset($error['general'])) { ?>
								<p style="color:red;"><?php echo htmlspecialchars($error['general']);?></p>
							<?php } ?>
						</div>
					<?php } ?>
					<table class="entry_form" >
						<tbody>
							<tr>
								<th>メールアドレス<span>必須</span></th>
								<td>
									<input type="email" style="border-radius: 3px; padding: 10px;" name="mail_address" value="<?php echo htmlspecialchars($customer['Ci_MailAddress']);?>" placeholder="例）sample@mail.com">
									<?php if(isset($error['Ci_MailAddress']) && !empty($error['Ci_MailAddress']) ){ ?>
										<p style="color: red;"><?php echo htmlspecialchars($error['Ci_MailAddress']);?></p>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<th>メールアドレス(確認)<span>必須</span></th>
								<td>
									<input type="email" oncopy="return false" onpaste="return false" oncontextmenu="return false"    style="border-radius: 3px; padding: 10px;" name="mail_address_confirm" value="<?php echo htmlspecialchars($customer['Ci_MailAddressConfirm']);?>" placeholder="例）sample@mail.com">
									<?php if(isset($error['Ci_MailAddressConfirm']) && !empty($error['Ci_MailAddressConfirm']) ){ ?>
										<p style="color: red;"><?php echo htmlspecialchars($error['Ci_MailAddressConfirm']);?></p>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<th>メール配信<span>必須</span></th>
								<td class="radioArea">
									<input type="radio" name="n1" value="1" id="r1" <?php echo $customer['Ci_InformationSend'] === 1 ? 'checked' : '';?>>
									<label for="r1">希望する</label>
									<input type="radio" name="n1" value="0" id="r2" <?php echo $customer['Ci_InformationSend'] === 1 ? '' : 'checked';?>>
									<label for="r2">希望しない</label>
									<span class="float_box">年会費決済完了時、GOSMANIA会員有効期限・クレジットカード有効期限が近くなりましたら、ご案内メールをお送りいたします。</span>
									<span class="float_box">※必ず「gospellers.tv」(ドメイン)を受信できるように設定をお願いいたします。</span>
									<span class="float_box">※配信を希望されない場合でも、重要なお知らせについて配信する場合がございます。</span>
									<?php if(isset($error['Ci_InformationSend']) && !empty($error['Ci_InformationSend']) ){ ?>
										<p style="color: red;"><?php echo htmlspecialchars($error['Ci_InformationSend']);?></p>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<th>連絡がつく電話番号<span>必須</span></th>
								<td>
									<input
										type="text"
										style="border-radius: 3px; padding: 10px;"
										name="phone_number"
										value="<?php echo htmlspecialchars($customer['Ci_Phone']);?>"
										placeholder="例）1234567890"
										maxlength="13"
									/>
									<?php if(isset($error['Ci_Phone']) && !empty($error['Ci_Phone']) ){ ?>
										<p style="color: red;"><?php echo htmlspecialchars($error['Ci_Phone']);?></p>
									<?php } ?>
								</td>
							</tr>

						</tbody>
					</table>
					<div id="aplly_kind00" class="app btn">
						<button type="submit" name="confirm" value="confirm" class="btn-sub">確認</button>
					</div>
				</form>
			</section>
			<?php break;

		case 'confirm': ?>
			<section class="section-list page-news GOSMANIA">
				<p class="credit-tit" style="margin-bottom:40px;">以下の内容で更新します。<br>よろしければ画面下の更新ボタンを<span><br></span>押して、更新を完了させてください。</p>
				<p class="credit-tit">お客様情報</p>

				<table class="entry_form" style="">
					<tbody>
						<tr>
							<th>メールアドレス</th>
							<td><?php echo htmlspecialchars($customer['Ci_MailAddress']); ?></td>
						</tr>
						<tr>
							<th>メールアドレス(確認)</th>
							<td><?php echo htmlspecialchars($customer['Ci_MailAddress']); ?></td>
						</tr>
						<tr>
							<th>メール配信</th>
							<td>
								<?php echo htmlspecialchars($def_informationSend[$customer['Ci_InformationSend']]);?>
							</td>
						</tr>
						<tr>
							<th>連絡がつく電話番号</th>
							<td><?php echo htmlspecialchars($customer['Ci_Phone']); ?></td>
						</tr>
						
					</tbody>
				</table>
			
				<form action="credit_done.html" method="post">
				<div id="aplly_kind00" class="app btn">
					<input type="hidden" name="mail_address" value="<?php echo htmlspecialchars($customer['Ci_MailAddress']); ?>">
					<input type="hidden" name="mail_address_confirm" value="<?php echo htmlspecialchars($customer['Ci_MailAddress']); ?>">
					<input type="hidden" name="n1" value="<?php echo htmlspecialchars($customer['Ci_InformationSend']); ?>">
					<input type="hidden" name="phone_number" value="<?php echo htmlspecialchars($customer['Ci_Phone']); ?>">
					<input type="button" name="back"class="btn-sub return" onclick="history.back()" value="戻る" style="margin-right:20px;">
					<input type="submit"name="register" class="btn-sub entry" value="更新" >

				</div>

			</section>
			<?php break;
		case 'register': break;
		default: ?>
			<p style="color: red;">問題が発生しました。</p>
			<?php break;
	} // end switch ?>

	<footer></footer>
</div><!-- .wrap -->
<pre><?php var_dump($_POST, $customer);?></pre>
</body>
</html>

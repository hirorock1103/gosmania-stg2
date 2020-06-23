<?php
include_once dirname(__FILE__) . "/settings.php";

// GMO定義 --------------------------------------------------
//define("GMO_API_TOKEN_AUTH_JS", "https://static.mul-pay.jp/ext/js/token.js");// token取得JS
define("GMO_API_TOKEN_AUTH_JS", "https://pt01.mul-pay.jp/ext/js/token.js");// token取得JS

//define("GMO_API_SITE_ID", "mst2000019418");
define("GMO_API_SITE_ID", "tsite00039116"); //テスト環境

//define("GMO_API_SITE_PASS", "fcbmanzy");
define("GMO_API_SITE_PASS", "d8k7xrmz"); //　テスト環境

//define("GMO_API_TOKEN_AUTH_SHOP_ID", "9200000464142");//暫定でinstimeのshopIDを設定。下に切り替える。
define("GMO_API_TOKEN_AUTH_SHOP_ID", "tshop00044681"); //テスト環境

//define("GMO_API_MEMBER_SEARCH_URL", "https://p01.mul-pay.jp/payment/SearchMember.idPass");// 会員参照
//define("GMO_API_MEMBER_REGIST_URL", "https://p01.mul-pay.jp/payment/SaveMember.idPass");
//define("GMO_API_MEMBER_UPDATE_URL", "https://p01.mul-pay.jp/payment/UpdateMember.idPass");
//define("GMO_API_CARD_REGIST_URL", "https://p01.mul-pay.jp/payment/SaveCard.idPass");
//テスト環境
define("GMO_API_MEMBER_SEARCH_URL", "https://pt01.mul-pay.jp/payment/SearchMember.idPass");// 会員参照
define("GMO_API_MEMBER_REGIST_URL", "https://pt01.mul-pay.jp/payment/SaveMember.idPass");
define("GMO_API_MEMBER_UPDATE_URL", "https://pt01.mul-pay.jp/payment/UpdateMember.idPass");
define("GMO_API_CARD_REGIST_URL", "https://pt01.mul-pay.jp/payment/SaveCard.idPass");
//------------------------------------------------------------


// 定義
define("PAYMENT_REGIST_LIMIT", 1440);// 24時間

$def_card_brand = array(
	"VISA"		=> "VISA",
	"MASTER"	=> "MasterCard",
	"JCB"		=> "JCB",
	"AMEX"		=> "American Express",
	"DINERS"	=> "Diners Club",
);

$def_informationSend = array("希望しない","希望する");

$st_year = (int)date('Y');

//GMO連携以外の処理を単体確認したい時にtrue
$debug = false;

if(isset($_POST) && !empty($_POST)){
	$data		 = $_POST;
	$token		 = (isset($data['token']) ? $data['token'] : '');
	
	$data = _adjustParams($dbh, $data);

	$validation = _validation($dbh, $data);

	if( (!empty($validation) && $debug == false ) || (isset($data['action']) && $data['action'] == 'back') ){ //validationに引っかかるか、確認画面で戻る押した時
		$mode = 'edit';
		
	}else if( !isset($data['action']) || $data['action'] != 'confirm'){ //更新はbuttonのnameもsubmitも無し
		$mode = 'complete';
		// $ret = _gmo_reg_member($dbh, $ses['cs_id'], NULL, $errmsg);
		// if (!$ret && $debug == false) { //会員登録が失敗したら
		// 	//var_dump($errmsg.'<br>Line:58');
		// 	var_dump($errmsg);
		// 	$mode = 'confirm';
		// 	exit;
		// }else{
		$gmo_card_seq = GetFirstPaymentInfoCardSeq($dbh, $ses['cs_id']);
		// $ret = _gmo_reg_card($dbh, $ses['cs_id'], $data['card_name'], $token, $errmsg);
		$ret = _gmo_reg_card2($dbh, $ses['cs_id'], NULL, $data['card_limit'], $token, $gmo_card_seq, $errmsg);
		if (!$ret && $debug == false) { //カード登録が失敗したら　
			var_dump($errmsg,'<br>',$token.'<br>Line:63');
			$mode = 'confirm';
		}else{
			
			// 完了画面へ
			header('Location: ./complete.php?status=' . rawurlencode(base64_encode('credit_update')) );
			exit();
		}
		// }
	}else{
		$mode = 'confirm';
		// var_dump("confirm");
	}
}else{ //一番最初
	$mode = 'edit';
}

/**********************************************/
// バリデーション
/**********************************************/
function _validation($dbh, $data){
	
	$ret = [];
	
	// var_dump($data);
	
	// Token取得済みならJS側でバリデーション完了しているため、次の処理へ
	// if (isset($data['token']) && $data['token'] != "") { 今回はクレカ登録だけではないのでtokenがOKならOKではない
		// $ret = true;
	// } else {
		
		$mail_str = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
		
		// // メールアドレス
		// if (!isset($data['Ci_MailAddress']) || !$data['Ci_MailAddress']) {
		// 	$ret['Ci_MailAddress'] = "メールアドレスを入力してください。";
		// }elseif (!isset($data['Ci_MailAddress2nd']) || $data['Ci_MailAddress2nd'] != $data['Ci_MailAddress']) {
		// 	$ret['Ci_MailAddress'] = "確認用のメールアドレスと入力内容が異なります。";
		// }elseif(!preg_match($mail_str, $data['Ci_MailAddress'])){
		// 	$ret['Ci_MailAddress'] = "不正なメールアドレスの入力です。";
		// }
		
		// // 連絡がつく電話番号
		// if (!preg_match("/[0-9]/", $data['Ci_Phone'])) {
		// 	$ret['Ci_Phone'] = "連絡先を入力してください。";
		// }else if(strlen($data['Ci_Phone']) < 12 || strlen($data['Ci_Phone']) > 13){
		// 	$ret['Ci_Phone'] = "不正な電話番号の入力です。";
		// }
		
		// クレジットカードブランド
		if (!isset($data['card_brand']) || !$data['card_brand']) {
			$ret['card_brand'] = "カード種別を選択してください。";
		}
		
		// クレジットカードブランド
		if (!isset($data['card_brand']) || !$data['card_brand']) {
			$ret['card_brand'] = "カード種別を選択してください。";
		}

		// カード番号
		if (!isset($data['card_number']) || !$data['card_number'] || !preg_match('/^[0-9]+$/', $data['card_number'])) {
			$ret['card_number'] = "カード番号を半角数字で入力してください。";
		}

		// カード有効期限
		if (!isset($data['card_limit_y']) || !$data['card_limit_y'] || !isset($data['card_limit_m']) || !$data['card_limit_m']) {
			$ret['card_limit'] = "カード有効期限を選択してください。";
		}

		// // カード名義 (半角英数字)
		// if (!isset($data['card_name']) || !$data['card_name'] || !preg_match('/^[A-Za-z0-9,.\-\/ ]+$/', $data['card_name'])) {
		// 	$ret['card_name'] = "カード名義を数字/アルファベット(大文字/小文字)/△(半角スペース) ,(カンマ) .(ピリオド) -(ハイフ）/(スラッシュ)で入力してください。";
		// }else if($data['card_name_1'] == '' || $data['card_name_2'] == ''){
		// 	$ret['card_name'] = "カード名義を入力してください。";
		// }

		// セキュリティコード
		if (!isset($data['card_code']) || !$data['card_code'] || !preg_match('/^[0-9]+$/', $data['card_code']) || (strlen($data['card_code']) != 3 && strlen($data['card_code']) != 4)) {
			$ret['card_code'] = "セキュリティコードを半角数字(3～4桁)で入力してください。";
		}
	// }
	
	return $ret;
}

/**********************************************/
// POST値整形
/**********************************************/
function _adjustParams($dbh, $data){
	
	// if( isset($data['card_name_1']) && isset($data['card_name_1']) ) {
	// 	//姓名結合&半角カナ変換
	// 	$data['card_name'] = mb_convert_kana($data['card_name_1'].' '.$data['card_name_2'], "a");
	// }
	
	if( isset($data['card_limit_y']) && isset($data['card_limit_m']) ) {
		//年月結合&半角数字変換
		$data['card_limit'] = mb_convert_kana($data['card_limit_y'].sprintf('%02d', $data['card_limit_m']), "n");
	}
	
	if( isset($data['Ci_Phone1']) && isset($data['Ci_Phone2']) && isset($data['Ci_Phone3'])) {
		//連絡先電話番号
		$data['Ci_Phone'] = $data['Ci_Phone1'].'-'.$data['Ci_Phone2'].'-'.$data['Ci_Phone3'];
	}
	
	$ret = $data;
	
	return $ret;
}


/**********************************************/
//エラー表示
/**********************************************/
function validate_alert($error, $_key){
	// 全体変数の $error ではなく各カラムごとのエラー
	if(isset($error[$_key]) && !empty($error[$_key])){
		echo '<div class="validate_alert">';
			echo '<div>'.$error[$_key].'</div>';
		echo '</div>';
	}
}

// var_dump($mode,$_POST);





?>
<html lang="ja">
<head><?php include_once dirname(__FILE__) . "/head.php"; ?></head>
<body>
<div class="wrap">
<?php include_once dirname(__FILE__) . "/header.php"; ?>
	<section class="section-list page-news GOSMANIA">
		<form action="" method="post" name="frm_payment">
			<input type="hidden" name="token" value="" />
			<?php if($mode == 'edit'){ //編集画面 ?>
				<p class="credit-tit" style="margin-bottom:40px;">以下必要事項をご入力の上、<span><br></span>確認ボタンを押してください。</p>
				<p class="credit-tit">クレジットカード情報を<span><br></span>入力してください</p>

				<p class="txt-credit">設定したクレジットカードはGOSMANIA年会費決済にご利用いただけます。<br>
					※クレジットカード情報は、カード決済代行会社(GMOペイメントゲートウェイ株式会社)で安全に保存されます。
				</p>
			<?php }else{ ?>
				<p class="credit-tit" style="margin-bottom:40px;">以下の内容で更新します。<br>よろしければ画面下の更新ボタンを<span><br></span>押して、更新を完了させてください。</p>
				<p class="credit-tit">クレジットカード情報</p>

				<p class="txt-credit">設定したクレジットカードはGOSMANIA年会費決済にご利用いただけます。<br>
					※クレジットカード情報は、カード決済代行会社(GMOペイメントゲートウェイ株式会社)で安全に保存されます。
				</p>
				<span style="color:red;" ><?php echo isset($errmsg[0]) ? '<br>'.$errmsg[0] : ''; ?></span>
			<?php } ?>
			<table class="entry_form">
				<tbody>
					<?php if($mode == 'edit'){ //編集画面 ?>
						<tr>
							<th>カード会社<span>必須</span></th>
							<td>
								<ul>
									<li>
										<select name="card_brand" style="width:200px; padding: 10px; border-radius: 3px;">
											<?php foreach ($def_card_brand as $key => $val) { ?>
											<option value="<?php echo $key; ?>" <?php echo isset($data) && $key == $data['card_brand'] ? 'selected' : ''; ?> ><?php echo $val; ?></option>
											<?php } ?>
											<option value="-">その他</option>
										</select>
									</li>
								</ul>

								<span class="cardbrand">※VISA,Master,JCB,American Express,Dinersがご利用いただけます。</span>


								<?php echo isset($validation['card_brand']) ? '<span style="color: red;">'.$validation['card_brand'].'</span>' : ''; ?>
							</td>
						</tr>
						<tr>
							<th>カード番号<span>必須</span></th>
							<td>
								<input type="text" style="border-radius: 3px; padding: 10px;" name="card_number" placeholder="1111222233334444" value="<?php echo isset($data['card_number']) ? $data['card_number'] : '';?>">
								<?php echo isset($validation['card_number']) ? '<span style="color: red;">'.$validation['card_number'].'</span>' : ''; ?>
							</td>
						</tr>
						<tr>
							<th>セキュリティコード<span>必須</span></th>
							<td>
								<input type="text" style="border-radius: 3px; padding: 10px;" class="width_short float_left" name="card_code" placeholder="000" value="<?php echo isset($data['card_code']) ? $data['card_code'] : '';?>">
								<span class="float_box">※クレジットカード裏面の署名欄にあるコードの下3桁です。<br>
								American Expressについては表面のクレジットカード番号右上に記載されている4桁です。</span>
								<?php echo isset($validation['card_code']) ? '<span style="color: red;">'.$validation['card_code'].'</span>' : ''; ?>
							</td>
						</tr>
						<tr>
							<th>有効期限<span>必須</span></th>
							<td>
								<ul>
									<li>
										<select name="card_limit_y" style="border-radius: 3px; padding: 10px;">
											<option value="">---</option>
											<?php for ($i = $st_year; $i < $st_year + 10; $i++) { ?>
											<option value="<?php echo $i; ?>" <?php echo isset($data) && $i == $data['card_limit_y'] ? 'selected' : ''; ?> ><?php echo $i; ?></option>
											<?php } ?>
										</select>年
									</li>
									<li>
										<select name="card_limit_m" style="border-radius: 3px; padding: 10px;">
											<option value="">---</option>
											<?php for ($i = 1; $i < 13; $i++) { ?>
											<option value="<?php echo sprintf('%02d', $i); ?>" <?php echo isset($data) && $i == (int)$data['card_limit_m'] ? 'selected' : ''; ?> ><?php echo $i; ?></option>
											<?php } ?>
										</select>月
									</li>
								</ul>
								<?php echo isset($validation['card_limit']) ? '<span style="color: red;">'.$validation['card_limit'].'</span>' : ''; ?>
							</td>
						</tr>
					<?php }else{ ?>
						<tr>
							<th>カード会社</th>
							<td><?php echo $data['card_brand']; ?></td>
							<input type="hidden" name="card_brand" value="<?php echo isset($data['card_brand']) ? $data['card_brand'] : '';?>">
						</tr>
						<tr>
							<th>カード番号</th>
							<td><?php echo $data['card_number']; ?></td>
							<input type="hidden" name="card_number" value="<?php echo isset($data['card_number']) ? $data['card_number'] : '';?>">
						</tr>
						<tr>
							<th>セキュリティコード</th>
							<td><?php echo $data['card_code']; ?></td>
							<input type="hidden" name="card_code" value="<?php echo isset($data['card_code']) ? $data['card_code'] : '';?>">
						</tr>
						<tr>
							<th>有効期限</th>
							<td><?php echo $data['card_limit']; ?></td>
							<input type="hidden" name="card_limit" value="<?php echo isset($data['card_limit']) ? $data['card_limit'] : '';?>">
							<input type="hidden" name="card_limit_y" value="<?php echo isset($data['card_limit_y']) ? $data['card_limit_y'] : '';?>">
							<input type="hidden" name="card_limit_m" value="<?php echo isset($data['card_limit_m']) ? $data['card_limit_m'] : '';?>">
						</tr>
					<?php } ?>
				</tbody>
			</table>
		<div id="aplly_kind00" class="app btn">
			<?php if($mode == 'edit'){ //編集画面 ?>
				<button type="submit" name="action" value="confirm" class="btn-sub" >確認</button>
			<?php }else{ ?>
				<button type="submit" name="action" value="back" class="btn-sub" >戻る</button>
				<!-- <button type="button" name="action" value="complete" class="btn-sub" id="btn_submit" >登録</button> -->
				<button class="btn-sub" type="button" id="btn_submit">登録</button>
				<span class="loading"></span>
			<?php } ?>
		</div>
	</section>
</form>
<footer></footer>
</div><!-- .wrap -->
</body>
</html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="<?php echo GMO_API_TOKEN_AUTH_JS; ?>"></script>
<script type="text/javascript">
$(function(){
	var _col = ['<?php echo implode("','", array_keys($data)); ?>'];
	/**********************************/
	// 登録押下
	/**********************************/
	$("#btn_submit").on('click', function(){
		$('#tmp_card_number').val( $('#card_number').val() );//期間限定のクレカ番号一時保存

		var err = false;
		var param = new Object();
		// 入力チェック
		for (key in _col) {
			var val = $('[name="' + _col[key] + '"]').val();
			if ( ( !val || (val && val.trim() == "") ) && _col[key] != 'token' && _col[key].indexOf('Ci_') == -1 ) {
				err = true;// 未入力あり
				alert('未入力情報：' + _col[key] );
				break;
			} else if (_col[key] == 'card_number' && !val.match(/^[0-9]+$/)) {
				err = true;
				alert('カード番号不正');
				break;
			} else if (_col[key] == 'card_name' && !val.match(/^[a-zA-Z0-9.,\/\-\ ]+$/)) {
				err = true;
				alert('カード名義不正');
				break;
			} else if (_col[key] == 'card_code' && (!val.match(/^[0-9]+$/) || val.length < 3 || val.length > 4)) {
				err = true;
				alert('セキュリティコード不正');
				break;
			} else {
				param[_col[key]] = val;
			}
		}
		console.log(err);	
		// エラーなければトークン取得
		if (err == false) {
			
			dispLoading('カード登録中・・');
			// Token取得
			Multipayment.init("<?php echo GMO_API_TOKEN_AUTH_SHOP_ID; ?>");
			Multipayment.getToken({
				cardno : param['card_number'],
				expire : param['card_limit_y'] + param['card_limit_m'],
				securitycode : param['card_code'],
				holdername : param['card_name'],
				//tokennumber : tokennumber
			}, _recvToken);

			// Token受領後にサブミットするためここで終了
			return false;
		}

		// エラーの場合、一度送信しPHP側のバリデーションではじく
		$('form[name="frm_payment"]').submit();

		return false;
	});
});
/**********************************/
// Token受領処理
/**********************************/
function _recvToken(response)
{
	console.log(response);

	if (response.resultCode != "000") {
		alert("カード照会中にエラーが発生しました。\nエラーコード:" + response.resultCode);
		removeLoading();

	} else {
		// カード情報は念のため値を除去
		// $('input[name="card_number"]').val(response.tokenObject.maskedCardNo);// マスク番号に変更
		$('select[name="card_limit_y"]').val("");
		$('select[name="card_limit_m"]').val("");
		// $('input[name="card_code"]').val("");
		// tokenフィールド設定
		$('input[name="token"]').val(response.tokenObject.token)
		// 次の画面へ
		$('form[name="frm_payment"]').submit();
	}
}
/***************************************/
// FUNCTION: ローディング
/***************************************/
function dispLoading(msg){
	var dispMsg = "<div class='loadingMsg'>" + msg + "</div>";
	// ローディング画像が表示されていない場合のみ出力
	if($("#loading").length == 0){
		$("body").append("<div id='loading'>" + dispMsg + "</div>");
	}
	return ;
}
function removeLoading(){
	$("#loading").remove();
}

</script>


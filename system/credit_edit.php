<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/admin/cron_functions/send_mail_functions.php";
include_once dirname(__FILE__) . "/class/Validator.class.php";


// 定義
define("PAYMENT_REGIST_LIMIT", 1440);// 24時間

$def_card_brand = array(
	"VISA"		=> "VISA",
	"MASTER"	=> "Mastercard",
	"JCB"		=> "JCB",
	"AMEX"		=> "American Express",
	"DINERS"	=> "Diners Club",
);

$def_informationSend = array("希望しない","希望する");

$st_year = (int)date('Y');

//GMO連携以外の処理を単体確認したい時にtrue
$debug = false;

if(isset($_POST) && !empty($_POST)){
	$data       = $_POST;
	$token      = (isset($data['token']) ? $data['token'] : '');
	$page_token = (isset($data['page_token']) ? $data['page_token'] : '');
	
	$data = _adjustParams($dbh, $data);
	$validator = new Validator($dbh);
	$validation = $validator->validate($data, "credit-edit");

        if(empty($page_token) || $_SESSION['page_token'] != $page_token){
                //page tokenが不正の場合は処理を中断
                unset($_SESSION['page_token']);
                header('Location: ./select.php');
                exit();
        }



	if( (!empty($validation) && $debug == false ) || (isset($data['action']) && $data['action'] == 'back') ){ //validationに引っかかるか、確認画面で戻る押した時
		$mode = 'edit';
		
	}else if( !isset($data['action']) || $data['action'] != 'confirm'){ //更新はbuttonのnameもsubmitも無し
		$mode = 'complete';
		$gmo_card_seq = GetFirstPaymentInfoCardSeq($dbh, $ses['cs_id']);
		// $ret = _gmo_reg_card($dbh, $ses['cs_id'], $data['card_name'], $token, $errmsg);
		$ret = _gmo_reg_card2($dbh, $ses['cs_id'], NULL, $data['card_limit'], $token, $gmo_card_seq, $errmsg);
		if (!$ret && $debug == false) { //カード登録が失敗したら　
			$mode = 'confirm';
		}else{
			//page token delete
			unset($_SESSION['page_token']);


			//独自本文フォーマット取得
			$sendMail = getSendMailData($dbh, $sm_type = 5);
			$customer = getSendMailTargetByCsId($dbh, $sm_type = 5, $ses['cs_id']);
			$result   = executeSendMailtoTarget2($dbh, $sm_type = 5, $data, $customer);

			// 完了画面へ
			header('Location: ./complete.php?status=' . rawurlencode(base64_encode('credit_update')) );
			exit();
		}
	}else{
		$mode = 'confirm';
	}
}else{ //一番最初
        //page_token
        $page_token = bin2hex(openssl_random_pseudo_bytes(32));
        $_SESSION['page_token'] = $page_token;

	$mode = 'edit';

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



?>
<!DOCTYPE html>
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
				<span style="color:red;" >
				<?php 
					if(isset($errmsg[0])){
						echo '<br>'.$errmsg[0];
						//特定のエラーコード含んでいる場合はエラーmsg表示
						$errors = (json_decode(GMO_ERROR_CODE, true));
						foreach($errors as $error_code => $error_msg ){
							if(strpos($errmsg[0],$error_code) !== false){
								echo '<br>'.$error_msg;
							}	
						}
					}
				?>
				</span>
			<?php } ?>
			<table class="entry_form">
				<tbody>
					<?php if($mode == 'edit'){ //編集画面 ?>
						<tr>
							<th>カード会社<span>必須</span></th>
							<td>
								<ul class="card_com_ul">
									<li>
										<select name="card_brand" style="width:200px; padding: 10px; border-radius: 3px;">
											<?php foreach ($def_card_brand as $key => $val) { ?>
											<option value="<?php echo $key; ?>" <?php echo isset($data) && $key == $data['card_brand'] ? 'selected' : ''; ?> ><?php echo $val; ?></option>
											<?php } ?>
										</select>
										<span class="comment-type1 chousei-1">※VISA・Master・JCB・American Express・Dinersがご利用いただけます。</span>
									</li>
								</ul>



								<?php echo isset($validation['card_brand']) ? '<p class="error-msg">'.$validation['card_brand'].'</p>' : ''; ?>
							</td>
						</tr>
						<tr>
							<th>カード番号<span>必須</span></th>
							<td>
								<input type="text" style="border-radius: 3px; padding: 10px;" name="card_number" placeholder="例）1111222233334444" value="<?php echo isset($data['card_number']) ? $data['card_number'] : '';?>">
								<?php echo isset($validation['card_number']) ? '<p class="error-msg">'.$validation['card_number'].'</p>' : ''; ?>
							</td>
						</tr>
						<tr>
							<th>セキュリティコード<span>必須</span></th>
							<td>
								<input type="text" style="border-radius: 3px; padding: 10px;" class="width_short" name="card_code" placeholder="例）000" value="<?php echo isset($data['card_code']) ? $data['card_code'] : '';?>">
								<p class="comment-type1-area">
								<span class="comment-type1">※クレジットカード裏面の署名欄にあるコードの下3桁です。</span><br>
								<span class="comment-type1">American Expressは表面のクレジットカード番号右上に記載されている4桁です。</span>
								</p>
								<?php echo isset($validation['card_code']) ? '<p class="clear error-msg" >'.$validation['card_code'].'</p>' : ''; ?>
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
											<option value="<?php echo $i; ?>" <?php echo isset($data['card_limit_y']) && $i == $data['card_limit_y'] ? 'selected' : ''; ?> ><?php echo $i; ?></option>
											<?php } ?>
										</select>&nbsp;&nbsp;年
									</li>
									<li>
										<select name="card_limit_m" style="border-radius: 3px; padding: 10px;">
											<option value="">---</option>
											<?php for ($i = 1; $i < 13; $i++) { ?>
											<option value="<?php echo sprintf('%02d', $i); ?>" <?php echo isset($data['card_limit_m']) && $i == (int)$data['card_limit_m'] ? 'selected' : ''; ?> ><?php echo $i; ?></option>
											<?php } ?>
										</select>&nbsp;&nbsp;月
									</li>
								</ul>
								<?php echo isset($validation['card_limit']) ? '<p class="error-msg">'.$validation['card_limit'].'</p>' : ''; ?>
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
				<input type="hidden" name="page_token" value="<?php echo $page_token;  ?>" />
			<?php }else{ ?>
				<button type="submit" name="action" value="back" class="btn-sub return" style="margin-right:20px;" >戻る</button>
				<!-- <button type="button" name="action" value="complete" class="btn-sub" id="btn_submit" >登録</button> -->
				<input type="hidden" name="page_token" value="<?php echo $page_token;  ?>" />
				<button class="btn-sub entry" type="button" id="btn_submit">更新</button>
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


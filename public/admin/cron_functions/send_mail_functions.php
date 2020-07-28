<?php
include_once dirname(__FILE__) . "/../functions.php";
// 呼び出し元でsettingsを読む必要あり
// include_once dirname(__FILE__) . "/../settings.php"; // cronの場合SESSIONの設定でコケるためコメント

/**
 * SendMailテーブルのSm_Typeカラムをキーにメール送信対象のユーザーを抽出する
 * @param PDOObject $dbh
 * @param int $Sm_Type
 * @param Array $Customers = [
 * 	'Cs_Id' => [Customer + CustomerInfo + last(PaymentInfo)],
 * 	'Cs_Id' => [Customer + CustomerInfo + last(PaymentInfo)],
 * 	,,,,
 * ]
 * // CustomerテーブルのID => Seq
 */
function getSendMailTargetUsers($dbh, $Sm_Type, $option_data = array()) {
	$Customers = [];

	// メール種別ごとにユーザーの抽出方法が異なる
	// 対象ユーザーを絞り込む場合は 各case 内を修飾していく
	switch ($Sm_Type) {
		case 1:
			// Customerテーブル
			$sql = "SELECT * FROM Customer
			WHERE Cs_SendMail = 1";

			$db = $dbh->prepare($sql);
			$db->execute();
			
			while($row = $db->fetch(PDO::FETCH_ASSOC)) {
				$Customers[$row['Cs_Id']] = $row;
			}

			// CustomerInfoテーブル
			$sql = "select I.* from CustomerInfo as I inner join Customer as C on C.Cs_Id = I.Cs_Id where Ci_Seq in (SELECT max(Ci_Seq) FROM `CustomerInfo` group by Cs_Id) and Ci_InformationSend = 1";
			$db = $dbh->prepare($sql);
			$db->execute();
			$tmp = array();
			while($row = $db->fetch(PDO::FETCH_ASSOC)) {
				$tmp[$row['Cs_Id']] = $row;
			}
			//group1 と比較して group2にないものは除外する
			foreach($Customers as $cs_id => $row){
				if(array_key_exists($cs_id, $tmp) == true){
					$Customers[$cs_id]['Ci_Seq']              = $tmp[$cs_id]['Ci_Seq'];
					$Customers[$cs_id]['Ci_MailAddress']     = $tmp[$cs_id]['Ci_MailAddress'];
					$Customers[$cs_id]['Ci_Mhone']           = $tmp[$cs_id]['Ci_Phone'];
					$Customers[$cs_id]['Ci_InformationSend'] = $tmp[$cs_id]['Ci_InformationSend'];
				}else{
					unset($Customers[$cs_id]);
				}
			}

			// PaymentInfoテーブル
			$sql = "select I.* from PaymentInfo as I inner join Customer as C on C.Cs_Id = I.gmo_id where I.seq in (SELECT max(seq) FROM `PaymentInfo` group by gmo_id)";
			$db = $dbh->prepare($sql);
			$db->execute();
			$tmp = array();
			while($row = $db->fetch(PDO::FETCH_ASSOC)) {
				$tmp[$row['gmo_id']] = $row;
			}
			//group2 と比較して group3にないものは除外する
			foreach($Customers as $cs_id => $row){
				if(array_key_exists($cs_id, $tmp) == true){
					//期限までの残月数
					$target1 = date("Y-m-01");
					$target2 = date("Y-m-01" , strtotime($tmp[$cs_id]['card_limitdate']."01"));
					
					$date1 = strtotime($target1);
					$date2 = strtotime($target2);

					$month1=date("Y",$date1)*12+date("m",$date1);
					$month2=date("Y",$date2)*12+date("m",$date2);

					$diff = $month2 - $month1;
					if($diff == 0){
						$Customers[$cs_id]['card_limitmonth'] = $diff;
						$Customers[$cs_id]['card_limitdate'] = $tmp[$cs_id]['card_limitdate'];
					}else{
						unset($Customers[$cs_id]);
					}

				}else{
					unset($Customers[$cs_id]);
				}
			}
		break;
		case 2:
			// Customerテーブル
			$sql = "SELECT * FROM Customer";

			$db = $dbh->prepare($sql);
			$db->execute();
			
			while($row = $db->fetch(PDO::FETCH_ASSOC)) {
				//期限までの残月数
				$target1 = date("Y-m-01");
				$target2 = $row['Cs_Timelimit']; 
				
				$date1 = strtotime($target1);
				$date2 = strtotime($target2);

				$month1=date("Y",$date1)*12+date("m",$date1);
				$month2=date("Y",$date2)*12+date("m",$date2);

				$diff = $month2 - $month1;

				if($diff < 2 && $diff > 0){
					$Customers[$row['Cs_Id']] = $row;
					$Customers[$row['Cs_Id']]['member_limitmonth'] = $diff;
					$Customers[$row['Cs_Id']]['card_limitdate'] = "";
				}

			}

			// CustomerInfoテーブル
			$sql = "select I.* from CustomerInfo as I inner join Customer as C on C.Cs_Id = I.Cs_Id where Ci_Seq in (SELECT max(Ci_Seq) FROM `CustomerInfo` group by Cs_Id) and Ci_InformationSend = 1";
			$db = $dbh->prepare($sql);
			$db->execute();
			$tmp = array();
			while($row = $db->fetch(PDO::FETCH_ASSOC)) {
				$tmp[$row['Cs_Id']] = $row;
			}
			//group1 と比較して group2にないものは除外する
			foreach($Customers as $cs_id => $row){
				if(array_key_exists($cs_id, $tmp) == true){
					$Customers[$cs_id]['Ci_Seq']              = $tmp[$cs_id]['Ci_Seq'];
					$Customers[$cs_id]['Ci_MailAddress']     = $tmp[$cs_id]['Ci_MailAddress'];
					$Customers[$cs_id]['Ci_Mhone']           = $tmp[$cs_id]['Ci_Phone'];
					$Customers[$cs_id]['Ci_InformationSend'] = $tmp[$cs_id]['Ci_InformationSend'];
				}else{
					unset($Customers[$cs_id]);
				}
			}


		break;
		case 3:
			// CustomerInfoテーブル
			$sql = "select I.*, G.result, G.ym, C.Cs_Name, C.Cs_Timelimit from CustomerInfo as I
				inner join GmoResult  as G on G.Cs_Id = I.Cs_Id
				inner join Customer  as C on G.Cs_Id = C.Cs_Id
				where G.ym = date_format(Now(), '%Y%m') and  Ci_Seq in (SELECT max(Ci_Seq) FROM `CustomerInfo` group by Cs_Id) and Ci_InformationSend = 1";
			$db = $dbh->prepare($sql);
			$db->execute();
			while($row = $db->fetch(PDO::FETCH_ASSOC)) {
				$Customers[$row['Cs_Id']]                       = $row;
				$Customers[$row['Cs_Id']]['Cs_Timelimit']       = $row['Cs_Timelimit'];
				$Customers[$row['Cs_Id']]['member_limitmonth']  = "";
				$Customers[$row['Cs_Id']]['card_limitdate']     = "";
				$Customers[$row['Cs_Id']]['Ci_Seq']             = "";
				$Customers[$row['Cs_Id']]['Ci_MailAddress']     = "";
				$Customers[$row['Cs_Id']]['Ci_Mhone']           = "";
				$Customers[$row['Cs_Id']]['Ci_InformationSend'] = "";
				$Customers[$row['Cs_Id']]['Ci_Seq']             = $row['Ci_Seq'];
				$Customers[$row['Cs_Id']]['Ci_MailAddress']     = $row['Ci_MailAddress'];
				$Customers[$row['Cs_Id']]['Ci_Mhone']           = $row['Ci_Phone'];
				$Customers[$row['Cs_Id']]['Ci_InformationSend'] = $row['Ci_InformationSend'];

			}

		break;
		default:
			$Customers = [];
	}
	return $Customers;
}
/*
 * Sm_type 4 , 5専用
 *
 */
function getSendMailTargetByCsId($dbh, $Sm_Type, $Cs_Id, $option_data = array()) {
	$Customers = [];

	// メール種別ごとにユーザーの抽出方法が異なる
	// 対象ユーザーを絞り込む場合は 各case 内を修飾していく
	switch ($Sm_Type) {
		case 4:
		case 5:
			// CustomerInfoテーブル
			$sql = "select I.*, C.Cs_Name, C.Cs_Timelimit from CustomerInfo as I
				inner join Customer  as C on I.Cs_Id = C.Cs_Id
				where Ci_Seq in (SELECT max(Ci_Seq) FROM `CustomerInfo` group by Cs_Id Having Cs_Id = :Cs_Id) and Ci_InformationSend = 1 limit 1";
			$db = $dbh->prepare($sql);
			$db->bindValue("Cs_Id", $Cs_Id, PDO::PARAM_STR);
			$db->execute();
			while($row = $db->fetch(PDO::FETCH_ASSOC)) {
				$Customers[$row['Cs_Id']]                       = $row;
				$Customers[$row['Cs_Id']]['Cs_Timelimit']       = $row['Cs_Timelimit'];
				$Customers[$row['Cs_Id']]['member_limitmonth']  = "";
				$Customers[$row['Cs_Id']]['card_limitdate']     = "";
				$Customers[$row['Cs_Id']]['Ci_Seq']             = "";
				$Customers[$row['Cs_Id']]['Ci_MailAddress']     = "";
				$Customers[$row['Cs_Id']]['Ci_Mhone']           = "";
				$Customers[$row['Cs_Id']]['Ci_InformationSend'] = "";
				$Customers[$row['Cs_Id']]['Ci_Seq']             = $row['Ci_Seq'];
				$Customers[$row['Cs_Id']]['Ci_MailAddress']     = $row['Ci_MailAddress'];
				$Customers[$row['Cs_Id']]['Ci_Mhone']           = $row['Ci_Phone'];
				$Customers[$row['Cs_Id']]['Ci_InformationSend'] = $row['Ci_InformationSend'];
			}

		break;
		default:
			$Customers = [];
	}
	return $Customers;
}


/**
 * $Sm_Type と $Customers に基づいてメールを順次送信
 * @param PDOObject $dbh
 * @param int $Sm_Type 0:クレカ, 1: その他？
 * @param Array $Customers = [
 * 	'Cs_Id' => [Customer + last(CustomerInfo) + last(PaymentInfo)],
 * 	'Cs_Id' => [Customer + last(CustomerInfo) + last(PaymentInfo)],
 * 	,,,,
 * ]
 */
function executeSendMailtoTarget($dbh, $Sm_Type, $Customers) {
	try{
		$sendCount = 0;

		// メールデータを取得 + メール設定
		$sendMail = getSendMailData($dbh, $Sm_Type);
		ini_set("mbstring.internal_encoding","UTF-8");
		mb_language("uni");
		$mailHeader = "From: ".mb_encode_mimeheader('GOSMANIAシステム') ."<gosmania_system@gospellers.tv>\nReply-To: gosmania_system@gospellers.tv";


		// メール送信対象でループ
		foreach($Customers as $Cs_Id => $customer) {

			// 顧客データにメールアドレスが無い場合、メール送信希望しない場合、カード情報がない場合はスキップ
			if( $Sm_Type == 1  ){
				if(!isset($customer['Ci_MailAddress']) ||
					empty($customer['Ci_MailAddress']) ||
					$customer['Ci_InformationSend'] != 1 ||
					!isset($customer['card_limitdate']) ||
					empty($customer['card_limitdate'])
				) {
					continue;
				}
			}else{
				if(!isset($customer['Ci_MailAddress']) ||
					empty($customer['Ci_MailAddress']) ||
					$customer['Ci_InformationSend'] != 1 ||
					!isset($customer['card_limitdate'])
				) {
					continue;
				}
			
			}

			// 顧客情報に基づいてメール本文を生成
			$mailContent = generateMailContent($sendMail, $customer);


			// メール送信実行
			$sendCount += mb_send_mail(
				$customer['Ci_MailAddress'],
				$sendMail['Sm_Subject'],
				$mailContent,
				$mailHeader
			);
		}

		// 成功時のログ保存
		if(isset($_SESSION) && !empty($_SESSION['gosmania']['login_info']['Ad_Id'])) {
			$log_text = date('Ymd H:i:s') . "\tlogin_id: " . $_SESSION['gosmania']['login_info']['Ad_Id'] . "\t" . $sendCount .'件のメールを送信成功' . "\n";
		}else {
			// 自動送信CRONなど実行者がいない場合
			$log_text = date('Ymd H:i:s') . "\tlogin_id: CRON\t" . $sendCount .'件のメールを送信成功' . "\n";
		}
		file_put_contents(
			dirname(__FILE__) . '/sendmail_log',
			$log_text,
			FILE_APPEND
		);

		//管理者にメール送信
		sendNoticeMailToAdmin($dbh, $sendCount, $sendMail['Sm_Subject']);

		# 例外を投げる場合 → ログがメッセージと共に記録される
		// if(true) {
		// 	throw new Exception('テスト例外');
		// }

	}catch(Exception $e) {
		if(isset($_SESSION) && !empty($_SESSION['gosmania']['login_info']['Ad_Id'])) {
			$log_text = date('Ymd H:i:s') . "\tlogin_id: " . $_SESSION['gosmania']['login_info']['Ad_Id'] . "\t" .$e->getMessage() . "\n";
		}else {
			// 自動送信CRONなど実行者がいない場合
			$log_text = date('Ymd H:i:s') . "\tlogin_id: CRON\t" . $sendCount .'件のメールを送信成功' . "\n";
		}
		file_put_contents(
			dirname(__FILE__) . '/sendmail_log',
			$log_text,
			FILE_APPEND
		);
		return 'FAILED';
	}
	return 'SUCCESS';
}
/*
 * クレカ更新・登録専用！メール本文を指定
 */
function executeSendMailtoTarget2($dbh, $Sm_Type, $data,  $Customers) {
	if($Sm_Type != 4 && $Sm_Type != 5){
		return false;
	}
	try{
		$sendCount = 0;

		// メールデータを取得 + メール設定
		$sendMail = getSendMailData($dbh, $Sm_Type);
		ini_set("mbstring.internal_encoding","UTF-8");
		mb_language("uni");
		$mailHeader = "From: ".mb_encode_mimeheader('GOSMANIAシステム') ."<gosmania_system@gospellers.tv>\nReply-To: gosmania_system@gospellers.tv";

		// 顧客情報に基づいてメール本文を生成
		foreach($Customers as $Cs_Id => $customer) {

			// 顧客情報に基づいてメール本文を生成
			$mailContent = generateMailContent2($sendMail, $customer, $data);

			// メール送信実行
			$sendCount += mb_send_mail(
				$customer['Ci_MailAddress'],
				$sendMail['Sm_Subject'],
				$mailContent,
				$mailHeader
			);
		}

		// 成功時のログ保存
		if(isset($_SESSION) && !empty($_SESSION['gosmania']['login_info']['Ad_Id'])) {
			$log_text = date('Ymd H:i:s') . "\tlogin_id: " . $_SESSION['gosmania']['login_info']['Ad_Id'] . "\t" . $sendCount .'件のメールを送信成功' . "\n";
		}else {
			// 自動送信CRONなど実行者がいない場合
			$log_text = date('Ymd H:i:s') . "\tlogin_id: CRON\t" . $sendCount .'件のメールを送信成功' . "\n";
		}
		file_put_contents(
			dirname(__FILE__) . '/sendmail_log',
			$log_text,
			FILE_APPEND
		);

		//管理者にメール送信
		//sendNoticeMailToAdmin($dbh, $sendCount, $sendMail['Sm_Subject']);

		# 例外を投げる場合 → ログがメッセージと共に記録される
		// if(true) {
		// 	throw new Exception('テスト例外');
		// }

	}catch(Exception $e) {
		if(isset($_SESSION) && !empty($_SESSION['gosmania']['login_info']['Ad_Id'])) {
			$log_text = date('Ymd H:i:s') . "\tlogin_id: " . $_SESSION['gosmania']['login_info']['Ad_Id'] . "\t" .$e->getMessage() . "\n";
		}else {
			// 自動送信CRONなど実行者がいない場合
			$log_text = date('Ymd H:i:s') . "\tlogin_id: CRON\t" . $sendCount .'件のメールを送信成功' . "\n";
		}
		file_put_contents(
			dirname(__FILE__) . '/sendmail_log',
			$log_text,
			FILE_APPEND
		);
		return 'FAILED';
	}
	return 'SUCCESS';
}


/**
 * SendMail テーブルから$Sm_Typeに合致し、有効な1件のデータを返す
 * @param PDOObject $dbh
 * @param int $Sm_Type
 * @return Array [DB Row]
 */
function getSendMailData($dbh, $Sm_Type) {
	$sql = "SELECT * FROM SendMail
	WHERE Sm_type = :Sm_Type
	AND Sm_IsUsing = 1
	LIMIT 1";
	$db = $dbh->prepare($sql);
	$db->bindValue(':Sm_Type', $Sm_Type, PDO::PARAM_INT);
	$db->execute();
	return $db->fetch(PDO::FETCH_ASSOC);
}

/**
 * SendMailテーブルのデータと顧客データに基づいてメール本文テキストを生成
 * @param Array $send_mail = SendMail テーブルの1レコード
 * @param Array $cusomer = [Customer + last(CustomerInfo) + last(PaymentInfo)]
 * @return string 埋め込み済のメールテキスト
 */
function generateMailContent($send_mail, $customer) {
	$return_text = $send_mail['Sm_Content'];

	// 会員ID
	$return_text = str_replace('{ID}', $customer['Cs_Id'], $return_text);

	// 氏名
	$return_text = str_replace('{NAME}', $customer['Cs_Name'], $return_text);

	// 会員 
	$limit_date = new DateTimeImmutable($customer['Cs_Timelimit']);
	$return_text = str_replace('{M_LIMIT}', $limit_date->format('Y年m月'), $return_text);

	// 西暦を含む年月日
	$return_text = str_replace('{DATE_YMD}', date('Y年m月d日'), $return_text);

	// 月日
	$return_text = str_replace('{DATE_MD}', date('m月d日'), $return_text);

	// クレジットカード有効期限
	if( isset($customer['card_limitdate']) && !empty( $customer['card_limitdate'] ) ){
		$limit_date = new DateTimeImmutable($customer['card_limitdate']);
		$return_text = str_replace('{LIMIT}', $limit_date->format('Y年m月'), $return_text);
	}else{
		$return_text = str_replace('{LIMIT}', "-※{LIMIT}は使用できません-", $return_text);
	}

	//以下は計算処理なので元になるデータがなければ空で返す
	/**
	 *{LIMIT-CALC-1-15}
	 *{M_LIMIT-CALC-1-15}
	*/
	if( isset($customer['Cs_Timelimit']) && !empty( $customer['Cs_Timelimit'] ) ){
		$limit_date = new DateTimeImmutable($customer['Cs_Timelimit']);
		//会員有効期限を同年同月の場合
		if( isset($customer['card_limitdate']) && !empty( $customer['card_limitdate'] ) ){
			//check
			$card_limit_date = new DateTimeImmutable($customer['card_limitdate']);
			if($card_limit_date->format('Ym') == $limit_date->format('Ym')){
				//同年同月の場合、期限を来年にする	
				$return_text = str_replace('{M_LIMIT-CALC-1-15}', $limit_date->modify('+11 month')->format('Y年m月15日'), $return_text);
			}
		}
		$return_text = str_replace('{M_LIMIT-CALC-1-15}', $limit_date->modify('first day of last month')->format('Y年m月15日'), $return_text);
	}else{
		$return_text = str_replace('{M_LIMIT-CALC-1-15}', "-※{M_LIMIT-CALC-1-15}は使用できません-", $return_text);
	}


	return $return_text;
}
/*
 * クレカ情報登録・更新専用
 *
 */
function generateMailContent2($send_mail, $customer, $data) {
	$return_text = $send_mail['Sm_Content'];

	// 会員ID
	$return_text = str_replace('{ID}', $customer['Cs_Id'], $return_text);

	// 氏名
	$return_text = str_replace('{NAME}', $customer['Cs_Name'], $return_text);

	// 会員 
	$limit_date = new DateTimeImmutable($customer['Cs_Timelimit']);
	$return_text = str_replace('{M_LIMIT}', $limit_date->format('Y年m月'), $return_text);

	// 西暦を含む年月日
	$return_text = str_replace('{DATE_YMD}', date('Y年m月d日'), $return_text);

	// 月日
	$return_text = str_replace('{DATE_MD}', date('m月d日'), $return_text);

	// カード会社 
	$return_text = str_replace('{CARD_CORP}', $data['card_brand'], $return_text);

	// カード番号
	// 強制マスク化
	$data['card_number'] = substr_replace($data['card_number'], "***************", 0,15);
	$return_text = str_replace('{CARD_NUMBER}', $data['card_number'], $return_text);
	
	//　セキュリティコード 
	// 強制マスク化
	$data['card_code'] = substr_replace($data['card_code'], "**", 0,2);
	$return_text = str_replace('{CARD_SECURITY}', $data['card_code'], $return_text);

	// クレジットカード有効期限
	if( isset($data['card_limit']) && !empty( $data['card_limit'] ) ){
		$limit_date = new DateTimeImmutable($data['card_limit']."01");
		$return_text = str_replace('{LIMIT}', $limit_date->format('Y年m月'), $return_text);
	}else{
		$return_text = str_replace('{LIMIT}', "-※{LIMIT}は使用できません-", $return_text);
	}


	return $return_text;
}

/**
 * 管理者に通知メール送信
 * @param PDOobject $dbh
 * @param int $successCount = 送信成功した件数
 * @param string $mailDetail = メールの概要
 * @return void
 */
function sendNoticeMailToAdmin($dbh, $successCount = 0, $mailDetail = '') {
	// 送信対象管理者を取得
	
	$condition['Ad_Invalid'] = ['placeholder' => 'Ad_Invalid' , 'value' => 0, 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$admins = GetListCommon($dbh, $condition, null, 'Admin', 'Ad_Seq');

	ini_set("mbstring.internal_encoding","UTF-8");
	mb_language("uni");
	$mailHeader = "From: ".mb_encode_mimeheader('GOSMANIAシステム') ."<info@gosmania.amb-dev.com>\nReply-To: info@gosmania.amb-dev.com";
	$mailText = date('Y-m-d H:i:s') . "\n" . $successCount . "件のメールを送信しました。\nメール件名: " . $mailDetail;
	// メール送信実行
	foreach($admins as $Ad_Seq => $admin) {
		if(filter_var($admin['Ad_MailAddress'], FILTER_VALIDATE_EMAIL)) {

			mb_send_mail(
				$admin['Ad_MailAddress'],
				"GOSMANIAシステムでメール送信完了",
				$mailText,
				$mailHeader
			);
		}
	}
}

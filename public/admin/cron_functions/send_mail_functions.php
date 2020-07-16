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
function getSendMailTargetUsers($dbh, $Sm_Type) {
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
			$sql = "select I.* from CustomerInfo as I inner join Customer as C on C.Cs_Id = I.Cs_Id where Ci_Seq in (SELECT min(Ci_Seq) FROM `CustomerInfo` group by Cs_Id) and Ci_InformationSend = 1";
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
			$sql = "select I.* from PaymentInfo as I inner join Customer as C on C.Cs_Id = I.gmo_id where I.seq in (SELECT min(seq) FROM `PaymentInfo` group by gmo_id)";
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
					if($diff < 3){
						$Customers[$cs_id]['card_limitmonth'] = $diff;
						$Customers[$cs_id]['card_limitdate'] = $tmp[$cs_id]['card_limitdate'];
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

				if($diff < 3){
					$Customers[$row['Cs_Id']] = $row;
					$Customers[$row['Cs_Id']]['member_limitmonth'] = $diff;
					$Customers[$row['Cs_Id']]['card_limitdate'] = "";
				}

			}

			// CustomerInfoテーブル
			$sql = "select I.* from CustomerInfo as I inner join Customer as C on C.Cs_Id = I.Cs_Id where Ci_Seq in (SELECT min(Ci_Seq) FROM `CustomerInfo` group by Cs_Id) and Ci_InformationSend = 1";
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
		$mailHeader = "From: ".mb_encode_mimeheader('GOSMANIA事務局') ."<info@gosmania.amb-dev.com>\nReply-To: info@gosmania.amb-dev.com";

		// メール送信対象でループ
		foreach($Customers as $Cs_Id => $customer) {

			// 顧客データにメールアドレスが無い場合、メール送信希望しない場合、カード情報がない場合はスキップ
			if(!isset($customer['Ci_MailAddress']) ||
				empty($customer['Ci_MailAddress']) ||
				$customer['Ci_InformationSend'] != 1 ||
				!isset($customer['card_limitdate']) ||
				empty($customer['card_limitdate'])
			) {
				continue;
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

<?php
// php send_credit_notice_mail.php
// で実行される
// 特に問題がなければ何も出力せず終了


try{
	include_once dirname(__FILE__) . "/cron_settings.php";

	include_once dirname(__FILE__) . "/../public/admin/cron_functions/send_mail_functions.php";


	$Sm_Type = 0; // メール種別
	$customer_list = getSendMailTargetUsers($dbh, $Sm_Type );
	$result = executeSendMailtoTarget($dbh, $Sm_Type, $customer_list);

	if($result == 'SUCCESS') {
		$log_text = date('Ymd H:i:s') . "\tlogin_id: \t[CRON]自動送信メールを送信成功" . "\n";
	}else {
		$log_text = "\n" . date('Ymd H:i:s') . "\tlogin_id: \t[CRON]自動送信メールを送信失敗" . "\n\n";
	}
	// 実行後のログ保存
	file_put_contents(
		dirname(__FILE__) . '/logs/sendmail_log',
		$log_text,
		FILE_APPEND
	);
}catch (Exception $e) {
	echo $e->getMessage();
}
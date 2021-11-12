<?php

/**
validationを行うクラス
**/
class Validator {
	
	private $dbh = null;
	public function __construct($dbh){
		$this->dbh = $dbh;
	}
	
	public function validate($data, $page){
		$error = array();
	
		if($page == "customer-info" || $page == "entry"){
			$mail_str = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";

			// メールアドレス
			if (!isset($data['Ci_MailAddress']) || !$data['Ci_MailAddress']) {
				$error['Ci_MailAddress'] = "メールアドレスを入力してください。";
			}elseif (!isset($data['Ci_MailAddressConfirm']) || $data['Ci_MailAddressConfirm'] != $data['Ci_MailAddress']) {
				$error['Ci_MailAddress'] = "確認用のメールアドレスと入力内容が異なります。";
			}elseif(!preg_match($mail_str, $data['Ci_MailAddress'])){
				$error['Ci_MailAddress'] = "不正なメールアドレスの入力です。";
			}
		}

		if($page == "customer-info" || $page == "entry"){
			// 連絡がつく電話番号
			if(empty($data['Ci_Phone'])){
				$error['Ci_Phone'] = "連絡先を入力してください。";
			}else{
				if( strlen($data['Ci_Phone']) == 10 ||  strlen($data['Ci_Phone']) == 11){
					if (!preg_match("/^[0-9]+$/", $data['Ci_Phone'])) {
						$error['Ci_Phone'] = "電話番号は数値のみ入力をお願いします。";
					}

				}else if( strlen($data['Ci_Phone']) == 12 ||  strlen($data['Ci_Phone']) == 13){
					if (!preg_match("/^[\-0-9]+$/", $data['Ci_Phone'])) {
						$error['Ci_Phone'] = "電話番号は数値と半角ハイフンのみ入力をお願いします。";
					}
				}else{
					//length error
					$error['Ci_Phone'] = "電話番号は10桁か11桁での入力となります。";
				}
			}
		}

		if($page == "entry" || $page == "credit-edit"){
			// クレジットカードブランド
			if (!isset($data['card_brand']) || !$data['card_brand']) {
				$error['card_brand'] = "カード種別を選択してください。";
			}

			// クレジットカードブランド
			if (!isset($data['card_brand']) || !$data['card_brand']) {
				$error['card_brand'] = "カード種別を選択してください。";
			}

			// カード番号
			if (!isset($data['card_number']) || !$data['card_number'] || !preg_match('/^[0-9]+$/', $data['card_number'])) {
				$error['card_number'] = "カード番号を半角数字で入力してください。";
			}

			// カード有効期限
			if (!isset($data['card_limit_y']) || !$data['card_limit_y'] || !isset($data['card_limit_m']) || !$data['card_limit_m']) {
				$error['card_limit'] = "カード有効期限を選択してください。";
			}

			// セキュリティコード
			if (!isset($data['card_code']) || !$data['card_code'] || !preg_match('/^[0-9]+$/', $data['card_code']) || (strlen($data['card_code']) != 3 && strlen($data['card_code']) != 4)) {
				$error['card_code'] = "セキュリティコードを半角数字(3～4桁)で入力してください。";
			}
		}
		return $error;
	}
	
	

}


?>

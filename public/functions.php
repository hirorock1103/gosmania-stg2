<?php 

/**********************************************/
// 支払情報（クレカ）保存
/**********************************************/
function _save_payment_info2($dbh, $apply_id, $member_name, $card_limit, $token, $arr_res, $response, $errmsg)
{

	global $tmp_card_number;

	$param = array(
		'apply_seq',
		'gmo_id',
		'token',
		'card_seq',
		'card_limitdate',
		//'card_number',
		//'tmp_card_number',
		//'card_name',
		'card_response',
	);
	$sql = "INSERT INTO PaymentInfo (" . implode(',', $param) . ") VALUES (:" . implode(',:', $param) . ")";
	//echo $sql;

	$db = $dbh->prepare($sql);
	$db->bindValue(":apply_seq", (int)str_replace('WLIT', '', $apply_id), PDO::PARAM_INT);
	$db->bindValue(":gmo_id", $apply_id, PDO::PARAM_STR);
	$db->bindValue(":token", $token, PDO::PARAM_STR);
	$db->bindValue(":card_seq", $arr_res['CardSeq'], PDO::PARAM_STR);
	$db->bindValue(":card_limitdate", $card_limit, PDO::PARAM_STR);
	// $db->bindValue(":card_number", $arr_res['CardNo'], PDO::PARAM_STR);
	// $db->bindValue(":tmp_card_number", $tmp_card_number, PDO::PARAM_STR);//期間限定の一時保存クレカ番号保存機能
	// $db->bindValue(":card_name", $member_name, PDO::PARAM_STR);
	$db->bindValue(":card_response", $response, PDO::PARAM_STR);
	$ret = $db->execute();

	return ;
}


/**********************************************/
// GMOカード登録
/**********************************************/
function _gmo_reg_card2($dbh, $apply_id, $member_name, $card_limit, $token, $card_seq, &$errmsg)
{

	$ret = false;

	try {
		// ■カード登録
		$curl = curl_init();
		$post_body = array(
			'SiteID'			=> GMO_API_SITE_ID,
			'SitePass'		=> GMO_API_SITE_PASS,
			'MemberID'		=> $apply_id,
			'DefaultFlag'	=> 1,// 継続課金
			'HolderName'	=> $member_name,
			'Token'				=> $token,
		);
		if(FALSE !== $card_seq) {
			// GetFirstPaymentInfoCardSeq() の返り値で、見つからないときはfalseが入っている
			$post_body['CardSeq'] = $card_seq;
		}
		$body_param = http_build_query($post_body);
		$header_param = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($body_param),
			'Cache-Control: no-cache',
		);
		$curl_option = array(
			CURLOPT_URL => GMO_API_CARD_REGIST_URL,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => $header_param,
			CURLOPT_POSTFIELDS => $body_param,
			// 以下デバッグ用
			//CURLOPT_HEADER => true,
			//CURLINFO_HEADER_OUT => true,
		);
		curl_setopt_array($curl, $curl_option);

		_log($dbh, $body_param, "", "", 1);// log

		// POST
		$response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		_log($dbh, ($status . ' ' . $response), "", "", 0);// log

		if ($status == 200) {
			$arr_res = _parse_response($response);// ex)ErrCode=E01&ErrInfo=E01390002
			// カード登録／更新チェック
			if (array_key_exists('CardSeq', $arr_res) && $arr_res['CardSeq'] != "") {
				// クレカ情報DB登録（INSERTエラーでもAPIは成功しているため成功扱いにする）
//				_save_payment_info($dbh, $apply_id, $member_name, $token, $arr_res, $response, $errmsg);
				_save_payment_info2($dbh, $apply_id, $member_name, $card_limit, $token, $arr_res, $response, $errmsg);				
				
				$ret = true;
			} else {
				throw new Exception('GMOカード登録／更新エラー ' . $arr_res['ErrInfo']);
			}
		} else {
			throw new Exception('GMOカード登録／更新 ステータスエラー ' . $status);
		}
		@curl_close($curl);


	} catch (Exception $e) {
		@curl_close($curl);
		//print('PDOException:' . $e->getMessage());
		$errmsg[] = $e->getMessage();
	}

	return $ret;
}

/**********************************************/
// GMO会員登録／更新
/**********************************************/
function _gmo_reg_member($dbh, $apply_id, $member_name, &$errmsg)
{
	$ret = false;
	$member_exist = false;

	try {
		// ■会員参照
		$curl = curl_init();
		$body = array(
			'SiteID'	=> GMO_API_SITE_ID,
			'SitePass'	=> GMO_API_SITE_PASS,
			'MemberID'	=> $apply_id,
		);
		if($member_name !== NULL) {
			$body['MemberName'] = $member_name;
		}
		$body_param = http_build_query($body);
		$header_param = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($body_param),
			'Cache-Control: no-cache',
		);
		$curl_option = array(
			CURLOPT_URL => GMO_API_MEMBER_SEARCH_URL,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => $header_param,
			CURLOPT_POSTFIELDS => $body_param,
			// 以下デバッグ用
			//CURLOPT_HEADER => true,
			//CURLINFO_HEADER_OUT => true,
		);
		curl_setopt_array($curl, $curl_option);

		_log($dbh, $body_param, "", "", 1);// log

		// POST
		$response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		
		//インサートエラー回避何でこんなことしなきゃいけないのかは不明。。。
		$response_arr = _parse_response($response);
		$response_str = '';
		foreach($response_arr as $key => $val){
			$response_str .= $key.'='.$val.'&';
		}
		$response_str = rtrim($response_str,'&');

		_log($dbh, ($status . ' ' . $response_str), "", "", 0);// log

		if ($status == 200) {
			$arr_res = _parse_response($response);// ex)ErrCode=E01&ErrInfo=E01390002
			// 会員有無チェック
			if (array_key_exists('MemberID', $arr_res) && $arr_res['MemberID'] == $apply_id) {
				$member_exist = true;// 会員あり
			} else if (array_key_exists('ErrInfo', $arr_res) && strpos($arr_res['ErrInfo'], 'E01390002') !== false) {
				$member_exist = false;// 会員なし
			} else {
				throw new Exception('GMO会員参照エラー ' . $arr_res['ErrInfo']);
			}
		} else {
			throw new Exception('GMO会員参照 ステータスエラー ' . status);
		}
		@curl_close($curl);
		
		// var_dump($member_exist,$arr_res);




		// ■会員登録／更新
		if ($member_exist) {
			$url = GMO_API_MEMBER_UPDATE_URL;
		} else {
			$url = GMO_API_MEMBER_REGIST_URL;
		}

		$curl = curl_init();
		/* 会員参照と同様のためコメントアウト
		$body_param = http_build_query(
			array(
				'SiteID'		=> GMO_API_SITE_ID,
				'SitePass'	=> GMO_API_SITE_PASS,
				'MemberID'	=> $apply_id,
				'MemberName'=> $member_name
			)
		);
		$header_param = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($body_param),
			'Cache-Control: no-cache',
		);
		$curl_option = array(
			CURLOPT_URL => $url,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => $header_param,
			CURLOPT_POSTFIELDS => $body_param,
			// 以下デバッグ用
			//CURLOPT_HEADER => true,
			//CURLINFO_HEADER_OUT => true,
		);
		*/

		$curl_option[CURLOPT_URL] = $url;// 接続先URL変更
		curl_setopt_array($curl, $curl_option);

		_log($dbh, $body_param, "", "", 1);// log

		// POST
		$response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		_log($dbh, ($status . ' ' . $response), "", "", 0);// log

		if ($status == 200) {
			$arr_res = _parse_response($response);// ex)ErrCode=E01|E01|E01|E01|E01&ErrInfo=E01010001|E01020001|E01030002|E01040001|E01060001
			// 会員登録／更新チェック
			if (array_key_exists('MemberID', $arr_res) && $arr_res['MemberID'] == $apply_id) {
				$ret = true;
			} else {
				throw new Exception('GMO会員登録／更新エラー ' . isset($arr_res['ErrInfo']) ? $arr_res['ErrInfo'] : '');
			}
		} else {
			throw new Exception('GMO会員登録／更新 ステータスエラー ' . $status);
		}
		@curl_close($curl);


	} catch (Exception $e) {
		@curl_close($curl);
		//print('PDOException:' . $e->getMessage());
		$errmsg[] = $e->getMessage();
	}

	return $ret;
}


/**********************************************/
// GMOカード登録
/**********************************************/
function _gmo_reg_card($dbh, $apply_id, $member_name, $token, &$errmsg)
{

	$ret = false;

	try {
		// ■カード登録
		$curl = curl_init();
		$body_param = http_build_query(
			array(
				'SiteID'			=> GMO_API_SITE_ID,
				'SitePass'		=> GMO_API_SITE_PASS,
				'MemberID'		=> $apply_id,
				'DefaultFlag'	=> 1,// 継続課金
				'HolderName'	=> $member_name,
				'Token'				=> $token,
			)
		);
		$header_param = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($body_param),
			'Cache-Control: no-cache',
		);
		$curl_option = array(
			CURLOPT_URL => GMO_API_CARD_REGIST_URL,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => $header_param,
			CURLOPT_POSTFIELDS => $body_param,
			// 以下デバッグ用
			//CURLOPT_HEADER => true,
			//CURLINFO_HEADER_OUT => true,
		);
		curl_setopt_array($curl, $curl_option);

		_log($dbh, $body_param, "", "", 1);// log

		// POST
		$response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		_log($dbh, ($status . ' ' . $response), "", "", 0);// log

		if ($status == 200) {
			$arr_res = _parse_response($response);// ex)ErrCode=E01&ErrInfo=E01390002
			// カード登録／更新チェック
			if (array_key_exists('CardSeq', $arr_res) && $arr_res['CardSeq'] != "") {
				// クレカ情報DB登録（INSERTエラーでもAPIは成功しているため成功扱いにする）
				_save_payment_info($dbh, $apply_id, $member_name, $token, $arr_res, $response, $errmsg);
				$ret = true;
			} else {
				throw new Exception('GMOカード登録／更新エラー ' . $arr_res['ErrInfo']);
			}
		} else {
			throw new Exception('GMOカード登録／更新 ステータスエラー ' . $status);
		}
		@curl_close($curl);


	} catch (Exception $e) {
		@curl_close($curl);
		//print('PDOException:' . $e->getMessage());
		$errmsg[] = $e->getMessage();
	}

	return $ret;
}


/**********************************************/
// GMOレスポンス分解
/**********************************************/
function _parse_response($response)
{
	$ret = array();

	$arr = explode('&', $response);// ErrCode=E01&ErrInfo=E01390002
	foreach ($arr as $res) {
		list($key, $val) = explode('=', $res);
		$ret[$key] = $val;
	}

	return $ret;
}



/**********************************************/
// GMOログ
/**********************************************/
function _log($dbh, $post, $get, $sess, $dir)
{
	$sql = "INSERT INTO PaymentLog (`post`, `get`, `sess`, `dir`) VALUES (:post, :get, :sess, :dir)";
	$db = $dbh->prepare($sql);
	$db->bindValue(":post", $post, PDO::PARAM_STR);
	$db->bindValue(":get", $get, PDO::PARAM_STR);
	$db->bindValue(":sess", $sess, PDO::PARAM_STR);
	$db->bindValue(":dir", $dir, PDO::PARAM_STR);
	$ret = $db->execute();
	return ;
}


/**********************************************/
// 支払情報（クレカ）保存
/**********************************************/
function _save_payment_info($dbh, $apply_id, $member_name, $token, $arr_res, $response, $errmsg)
{

	global $tmp_card_number;

	$param = array(
		'apply_seq',
		'gmo_id',
		'token',
		'card_seq',
		//'card_number',
		//'tmp_card_number',
		//'card_name',
		'card_response',
	);
	$sql = "INSERT INTO PaymentInfo (" . implode(',', $param) . ") VALUES (:" . implode(',:', $param) . ")";
	//echo $sql;

	$db = $dbh->prepare($sql);
	$db->bindValue(":apply_seq", (int)str_replace('WLIT', '', $apply_id), PDO::PARAM_INT);
	$db->bindValue(":gmo_id", $apply_id, PDO::PARAM_STR);
	$db->bindValue(":token", $token, PDO::PARAM_STR);
	$db->bindValue(":card_seq", $arr_res['CardSeq'], PDO::PARAM_STR);
	// $db->bindValue(":card_number", $arr_res['CardNo'], PDO::PARAM_STR);
	// $db->bindValue(":tmp_card_number", $tmp_card_number, PDO::PARAM_STR);//期間限定の一時保存クレカ番号保存機能
	// $db->bindValue(":card_name", $member_name, PDO::PARAM_STR);
	$db->bindValue(":card_response", $response, PDO::PARAM_STR);
	$ret = $db->execute();

	return ;
}


function InsCustomerInfo($dbh,$params){
	$sql = "INSERT INTO CustomerInfo (Cs_Id, Ci_MailAddress, Ci_Phone, Ci_InformationSend, Ci_Creator, Ci_Creatdate) ";
	$sql .= "VALUES (:Cs_Id, :Ci_MailAddress, :Ci_Phone, :Ci_InformationSend, :Ci_Creator, NOW() ) ";
	$db = $dbh->prepare($sql);
	$db->bindValue(":Cs_Id", $_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Id'], PDO::PARAM_STR);
	$db->bindValue(":Ci_MailAddress", $params['Ci_MailAddress'], PDO::PARAM_STR);
	$db->bindValue(":Ci_Phone", $params['Ci_Phone'], PDO::PARAM_STR);
	$db->bindValue(":Ci_InformationSend", $params['Ci_InformationSend'], PDO::PARAM_STR);
	$db->bindValue(":Ci_Creator", $_SESSION[SESSION_BASE_NAME]['login_info']['Cs_Name'], PDO::PARAM_STR);
	$ret = $db->execute();
	return ;
}

/**
 * ユーザーが最初にPaymentInfoに登録したレコードのGMO card_seq を返す
 * @param PDOobject $dbh
 * @param string $gmo_id = '00-00001'
 * @return Mixed int=GMOカード登録番号 or bool FALSE=not found
 */
function GetFirstPaymentInfoCardSeq($dbh, $gmo_id) {
	$sql = "SELECT card_seq FROM PaymentInfo
	WHERE gmo_id = :gmo_id
	ORDER BY seq ASC LIMIT 1";
	$db = $dbh->prepare($sql);
	$db->bindValue(':gmo_id', $gmo_id, PDO::PARAM_STR);
	$db->execute();
	return $db->fetch(PDO::FETCH_COLUMN);
}

/**
 * 該当する1件を取得
 * @param PDObject $dbh
 * @param string $table_name
 * @param string $column_name 主キーカラムの名前
 * @param int $seq
 * @param string $order = 並び順 'desc'指定で最新1件にする
 * @return array 1行のデータ
 */
function find_record($dbh, $table_name, $primary_column_name, $seq, $order = NULL ) {
	$sql = "SELECT * FROM ". $table_name . " WHERE " . $primary_column_name . " = :seq";
	if($order!== NULL) {
		$sql .= " ORDER BY " . $primary_column_name . " DESC ";
	}
	$sql .= "LIMIT 1";
	$db = $dbh->prepare($sql);
	$db->bindValue(':seq', $seq, PDO::PARAM_INT);
	$db->execute();
	return $db->fetch(PDO::FETCH_ASSOC);
}

/**
 * 該当する1件を取得
 * @param PDObject $dbh
 * @param string $table_name
 * @param string $column_name 主キーカラムの名前
 * @param int $seq
 * @param string $order = 並び順 'desc'指定で最新1件にする
 * @return array 1行のデータ
 */
function find_record_by($dbh, $table_name, $primary_column_name, $search_column, $search_value, $order = NULL ) {
	$sql = "SELECT * FROM ". $table_name . " WHERE " . $search_column . " = :search_value";
	if($order!== NULL) {
		$sql .= " ORDER BY " . $primary_column_name . " DESC ";
	}
	$sql .= "LIMIT 1";
	$db = $dbh->prepare($sql);
	$db->bindValue(':search_value', $search_value, PDO::PARAM_INT);
	$db->execute();
	return $db->fetch(PDO::FETCH_ASSOC);
}
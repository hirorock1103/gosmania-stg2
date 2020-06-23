<?php
// 日付妥当チェック
function is_valid_date($year, $month, $day){
		if(
				!isset($year) || 
				!isset($month) || 
				!isset($day) || 
				!preg_match('/^[0-9]+$/', $year) ||
				!preg_match('/^[0-9]+$/', $month) ||		
				!preg_match('/^[0-9]+$/', $day) ||
				!checkdate($month, $day, $year)
		){
				return false;
		}
		return true;
}
function is_valid_date_string($date){
		 try{
				if(
						date("Y-m-d", strtotime($date)) == $date || date("Y/m/d", strtotime($date)) == $date || date("Ymd", strtotime($date)) == $date ||
						date("Y-n-d", strtotime($date)) == $date || date("Y/n/d", strtotime($date)) == $date || date("Ynd", strtotime($date)) == $date ||
						date("Y-m-j", strtotime($date)) == $date || date("Y/m/j", strtotime($date)) == $date || date("Ymj", strtotime($date)) == $date ||
						date("Y-n-j", strtotime($date)) == $date || date("Y/n/j", strtotime($date)) == $date || date("Ynj", strtotime($date)) == $date
				){
						return true;
				}
		} catch (Exception $ex) {
				return false;
		}
		return false;
}
// 時刻形式チェック
function is_valid_time_string($time){
		 try{
				$tm = explode(":", $time);
				if(
						date("H:i:s", strtotime($time)) == $time || date("G:i:s", strtotime($time)) == $time || 
						date("H:i", strtotime($time)) == $time || date("G:i", strtotime($time)) == $time || 
						(count($tm) == 3 && preg_match('/^[0-9]+$/', $tm[0]) && preg_match('/^[0-9]+$/', $tm[1]) && preg_match('/^[0-9]+$/', (int)$tm[2])) ||
						(count($tm) == 2 && preg_match('/^[0-9]+$/', $tm[0]) && preg_match('/^[0-9]+$/', $tm[1])) 
				){
						return true;
				} else {
					return false;
				}
		} catch (Exception $ex) {
				return false;
		}
		return false;
}

// メールアドレス妥当チェック
function is_valid_mail_address($email){
		if(
				!empty($email) &&
				filter_var($email, FILTER_VALIDATE_EMAIL)
		){
				$m = explode("@", $email);
				if(!empty($m) && count($m) == 2){
						$domain = array_pop($m);
						if(!empty($domain) && (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA'))){
								return true;
						}
				}
		}
		return false;
}
function is_valid_mail_address_str($email){
		return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ひらがなのみ許可チェック
function is_valid_hiragana($str, $space = false){
		if($space){
				return preg_match('/^[ぁ-ゟ　 ]+$/u', $str);
		}else{
				return preg_match('/^[ぁ-ゟ]+$/u', $str);
		}
}

// 全角カタカナのみ許可チェック
function is_valid_katakana($str, $space = false){
		if($space){
				return preg_match('/^[ァ-ヿ　 ]+$/u', $str);
		}else{
				return preg_match('/^[ァ-ヿ]+$/u', $str);
		}
}

// 半角カタカナのみ許可チェック
function is_valid_hankaku_katakana($str, $space = false){
		if($space){
				return preg_match('/^[ｦ-ﾟ　 ]+$/u', $str);
		}else{
				return preg_match('/^[ｦ-ﾟ]+$/u', $str);
		}
}

// 半角英数のみ許可チェック
function is_valid_hankaku_eisu($str, $space = false){
		if($space){
				return preg_match('/^[a-zA-Z0-9 ]+$/', $str);
		}else{
				return preg_match('/^[a-zA-Z0-9]+$/', $str);
		}
}
// 日付フォーマット
function my_date_format($date_string, $time = false){
		if(isset($date_string) && trim($date_string) != "" && !(strpos($date_string, "0000") !== false)){
				return date($time ? 'Y/m/d H:i:s' : 'Y/m/d', strtotime($date_string));
		}
		return "";
}

// 日付フォーマット
function my_date_format_by_int($date_int, $time = false){
		if(!empty($date_int) && preg_match('/^[0-9]+$/', $date_int)){
				$yyyymmdd = substr($date_int, 0, 4)."/".substr($date_int, 4, 2)."/".substr($date_int, 6, 2);
				$hhiiss = "";
				if($time){
						$hhiiss = " ".substr($date_int, 8, 2).":".substr($date_int, 10, 2).":".substr($date_int, 12, 2);
				}
				return $yyyymmdd.$hhiiss;
		}
		return "";
}

// 時刻フォーマット(HH:MM)
function my_time_format($time_string, $format = 'H:i'){
		if(isset($time_string) && trim($time_string) != ""){
			return date($format, strtotime('2000/01/01 ' . $time_string));
		}
		return "";
}

// 郵便番号フォーマット
function zip_code_format($zip_code){
		return (!empty($zip_code) && preg_match('/^[0-9]+$/', $zip_code) && strlen($zip_code) == 7 ? substr($zip_code, 0, 3)."-".substr($zip_code, 3) : "");
}

// アップロードファイルチェック
function validate_upload_file($file, $required = false, $valid_ext_list = array()){
		$result = array();
		
		if (
				!isset($file['error']) ||
				!is_int($file['error'])
		) {
				throw new UploadException('不正なアップロードです。');
		}

		switch ($file['error']) {
				case UPLOAD_ERR_OK: // OK
						break;
				case UPLOAD_ERR_NO_FILE:	 // ファイル未選択
						if($required){
								throw new UploadException('ファイルが選択されていません。');
						}else{
								return $result;
						}
				case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
				case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
						throw new UploadException('ファイルサイズが大きすぎます。');
				default:
						throw new UploadException('アップロード中にエラーが発生しました。');
		}

		if (!empty($file['name'])) {
				$file_name = $file['name'];
				$pos = strrpos($file_name, '.');
				if($pos !== false){
						$ext = substr($file_name, $pos + 1);
						if(!in_array($ext,	$valid_ext_list, true)){
								throw new UploadException('アップロードできないファイルです。');
						}else{
								$result["ext"] = $ext;
						}
				} else {
						throw new UploadException('アップロードできないファイルです。');
				}
		} else {
				throw new UploadException('アップロードできないファイルです。');
		}
		
		return $result;
}

// アップロードファイル保存
function save_upload_file($directory_path, $file_tmp_name, $file_name){
		$up_file_path = $directory_path."/".$file_name;
		if(!file_exists($directory_path)){
				mkdir($directory_path, 0757);
				chmod($directory_path, 0757);
		}else if(file_exists($up_file_path)){
				unlink($up_file_path);
		}
		if(move_uploaded_file($_FILES[$file_tmp_name]["tmp_name"], $up_file_path)) {
				chmod($up_file_path, 0666);
		}else{
				throw new UploadException('アップロードファイルが保存できません。');
		}
}

// 定数定義 JSON項目対応
function get_defined_array($json_str){
		return json_decode($json_str, true);
}
function get_defined_name($json_str, $key){
		$defined_array = get_defined_array($json_str);
		if(isset($key) && array_key_exists($key, $defined_array)){
				return $defined_array[$key];
		}
		return "";
}

// ハイフン削除
function remove_hyphen($str){
		$defined_hyphen = get_defined_array(HYPHENS);
		foreach($defined_hyphen as $hyphen){
				$str = mb_ereg_replace($hyphen, '', $str);
		}
		return $str;
}

// 全角ハイフン変換
function replace_zenkaku_hyphen($str){
		$defined_hyphen = get_defined_array(HYPHENS);
		foreach($defined_hyphen as $hyphen){
				$str = mb_ereg_replace($hyphen, '－', $str);
		}
		return $str;
}

// 半角ハイフン変換
function replace_hankaku_hyphen($str){
		$defined_hyphen = get_defined_array(HYPHENS);
		foreach($defined_hyphen as $hyphen){
				$str = mb_ereg_replace($hyphen, '-', $str);
		}
		return $str;
}

// 全角変換
function replace_zenkaku($str){
		$str = mb_convert_kana($str, 'KVAS', 'UTF-8');
		return replace_zenkaku_hyphen($str);
}

// 半角変換
function replace_hankaku($str){
		$str = mb_convert_kana($str, 'kvas', 'UTF-8');
		return replace_hankaku_hyphen($str);
}

// 全角カタカナ変換(ひらがな -> カタカナ)
function replace_zenkaku_katakana($str){
		$str = mb_convert_kana($str, 'KVCAS', 'UTF-8');
		return replace_zenkaku_hyphen($str);
}

// 全角ひらがな変換(カタカナ -> ひらがな)
function replace_zenkaku_hiragana($str){
		$str = mb_convert_kana($str, 'KVHcAS', 'UTF-8');
		return replace_zenkaku_hyphen($str);
}

// 西暦→和暦への文字列変換処理(共通)
function conv_wareki($year)
{
	$year_str = "";
	if(isset($year) && $year >= 1989) {
		$year_str	=	$year."年(平成".($year - 1988)."年)";
	}else if(isset($year) && $year >= 1926 && $year <= 1988) {
		$year_str	=	$year."年(昭和".($year - 1925)."年)";
	}else if(isset($year) && $year >= 1912 && $year <= 1925) {
		$year_str	=	$year."年(大正".($year - 1911)."年)";
	}else{
		$year_str	=	$year."年(明治".($year - 1867)."年)";
	}
	return $year_str;
}

// コード作成
function create_code($id, $suffix = "", $prefix = "", $digit = 8){
		if(intval($digit) > 0){
				return $suffix.sprintf('%0'.intval($digit).'d', $id).$prefix;
		}else{
				return $suffix.$id.$prefix;
		}
}

// 省略表示
function omit_str($str, $length = 0, $prefix = true) {
		return ($length > 0 && mb_strlen($str) > $length ? mb_substr($str, 0, $length).($prefix ? "..." : "") : $str);
}

// 全角空白対応trim
function trim_all($str) {
		$str = trim($str);
		$str = preg_replace('/^[ 　]+/u', '', $str);
		$str = preg_replace('/[ 　]+$/u', '', $str);
		return trim($str);
}
function trim_into_all($str) {
		return preg_replace('/[ 　]/u', '', $str);
}

// 画像ファイルのMIME TYPE
function get_mime_type($file){
		$mime_type = "";
		$type = exif_imagetype($file);
		switch($type){
				case IMAGETYPE_GIF:
				$mime_type = "image/gif";
				break;
				case IMAGETYPE_JPEG:
				$mime_type = "image/jpg";
				break;
				case IMAGETYPE_PNG:
				$mime_type = "image/png";
				break;
				default:
				$mime_type = "";
		}
		return $mime_type;
}

// 秒数を時分秒に変換
function format_seconds($seconds){
	$hours = floor($seconds / 3600);
	$minutes = floor(($seconds / 60) % 60);
	$seconds = $seconds % 60;
	
	return array("hours" => sprintf("%d", $hours), "minutes" => sprintf("%d", $minutes), "seconds" => sprintf("%d", $seconds), );
}

// 乱数取得
function get_random_number(){
	return sha1(time().mt_rand());
}

// エスケープ
function h($str)
{
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


// floatをH:iに変換
function format_time($h){
	$time_str = (string)$h;
	$time_str_arr = explode('.',$time_str);
	$min = '00';
	$percentage = [1 => 1, 2 => 10, 3 => 100, 4 => 1000 , 5 => 10000];
	if(isset($time_str_arr[1])){
		$min = $time_str_arr[1]*6/$percentage[strlen($time_str_arr[1])];
		$min = round($min);
		if($min > 10){
			$min = sprintf('%02d', $min);
		}else if($min == 60 ){
			$min = '00';
		}
	}
	return $time_str_arr[0].'時間'.$min.'分';
	
}

/***************************************/
// FORMの正当性チェック
/***************************************/
function fncCheckFormDigit($num1, $num2) 
{
	if ($num1 != $num2) {
		header("Location: form_error.php");
		exit;
		//return false;
	}
	
	return true;
}

/***************************************/
// メール送信
/***************************************/
function fncSendMail($to, $title, $txt)
{
	// 申込者に届く差出人
	$fromAddress		= constant('MAILADDRESS_INFO');
	$returnAddress	= constant('MAILADDRESS_INFO');
	
	$header		=	"From: ".$fromAddress."\nReply-To: ".$returnAddress."\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
	$para			=	"-f".$fromAddress;
	
	// 送信
	if($to != ""){
		mb_send_mail($to, $title, $txt, $header, $para);
	}
	
	return true;
}


/**********************************************/
// PAGER設定
/**********************************************/
function GetPager($cur_page, $data_total, $pager_total, &$min_page, &$max_page)
{
	$page_div_num = (int)floor(PAGER_DISP_NUM / 2);// 中央から両隣何枠表示するか（例：5枠表示の場合、中央が２、両隣は２となる）
	$min_page = 0;
	$max_page = 0;

	// pagerの表示枠を設定
	if ($cur_page - $page_div_num < 0) {
		$min_page = 0;
		$max_page = PAGER_DISP_NUM - 1;
		if ($max_page >= $pager_total) {
			$max_page = $pager_total - 1;
		}
	} else if ($cur_page + $page_div_num >= $pager_total) {
		$max_page = $pager_total - 1;
		$min_page = $max_page - PAGER_DISP_NUM + 1;
		if ($min_page < 0) {
			$min_page = 0;
		}
	} else {
		$min_page = $cur_page - $page_div_num;
		$max_page = $cur_page + $page_div_num;
	}
	
	return ;
}

/*********VB以外からのアクセスを拒否して404っぽく表示する ********/
function invalid_IP_brocking(){
	if('119.243.84.173' !== $_SERVER['REMOTE_ADDR']){
		header("HTTP/1.1 404 Not Found"); 
		echo '<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL /tools/ was not found on this server.</p></body></html>';
		exit;
	}
}



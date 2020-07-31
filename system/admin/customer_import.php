<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";


//var_dump($_POST);
//var_dump($_FILES);


$frm_status    = "INPUT";
$errmsg        = array();
$completemsg   = array();
$arr_input     = array();
$arr_input_err = array();


// CSVカラム
$cols = array(
	'Cs_ID'        => array('name' => "顧客番号",		'must' => 1, 'type' => "text",	 'valid' => [],	 'memo' => "重複不可です。", 'ex' => "00-00009"),
	'Cs_Name'      => array('name' => "顧客名", 		'must' => 1, 'type' => "text",	 'valid' => [],	 'memo' => "", 'ex' => "テスト　太郎"),
	'Cs_Zip'       => array('name' => "郵便番号",		'must' => 1, 'type' => "number", 'valid' => [],	 'memo' => "ハイフンを付けてください。", 'ex' => "177-0001"),
	'Cs_Timelimit' => array('name' => "会員有効期限",	'must' => 1, 'type' => "date",	 'valid' => [],	 'memo' => "「yyyy/mm/dd」で入力してください。", 'ex' => "2020/01/01"),
	'Cs_SendMail'  => array('name' => "支払方法",	'must' => 1, 'type' => "number",	 'valid' => [],	 'memo' => "0:クレカ以外 1:クレジットカード <br>※会員状態が在会員かつ支払方法がクレカの会員様を1とする。", 'ex' => "1"),
);
//var_dump($cols);


// アップロード？
if (isset($_POST['frm_submit'])) {
	// アップロードされたファイルを保存
	$filedir      = "csv_upload";
	$filename     = "";
	$filefullname = "";
	$ret          = _saveFile($filedir, $filename, $errmsg);
	if ($ret) {
		$filefullname = $filedir."/".$filename;
	}
	
	// ファイルタイプチェック
	if ($ret) {
		if (pathinfo($filefullname, PATHINFO_EXTENSION) != "csv") {
			$ret = false;
			$errmsg[] = "CSVファイルをアップロードしてください。";
		}
	}
	
	// 文字コードチェック
	if ($ret) {
		$encoding = _encoding($filefullname);
		if (!$encoding) {
			$ret = false;
			$errmsg[] = "文字コードはUTF-8またはShift-JISで保存してください。";
		}
	}
	
	// CSVデータチェック
	if ($ret) {
		$ret = _validation($dbh, $filefullname, $encoding, $cols, $arr_shop, $errmsg);
	}
	
	// 顧客情報DB保存
	if ($ret) {
		$ret = _save($dbh, $filefullname, $encoding, $cols, $arr_shop, $errmsg);
	}
	
	// 登録成功ならリダイレクト
	if ($ret) {
		header("Location: customer_import.php?complete");
		exit;
		
	}
	
	
// 完了？
} else if (isset($_GET['complete'])) {
	$cnt = $_SESSION[SESSION_BASE_NAME]['customer_info']['csv_cnt'];
	//unset($_SESSION[SESSION_BASE_NAME]['customer_info']['csv_cnt']);
	$completemsg[] = number_format($cnt) . "件の認証用データを一括登録しました。";
}







/**********************************************/
// ファイル保存
/**********************************************/
function _saveFile($filedir, &$filename, &$errmsg)
{
	try {
		// ファイルあり？
		if (!isset($_FILES['csv_file']) || !isset($_FILES["csv_file"]["tmp_name"]) || !$_FILES["csv_file"]["tmp_name"]) {
			$errmsg[] = "ファイルが見つかりません。";
			return false;
		}
		
		// ファイル保存
		$tmp_name = $_FILES["csv_file"]["tmp_name"];
		$filename = "CUSTOMER_" . date("Ymd_His-") . basename($_FILES["csv_file"]["name"]);
		if (!move_uploaded_file($tmp_name, "$filedir/$filename")) {
			$errmsg[] = "アップロードファイル「" . $filename . "」を保存できませんでした。";
			return false;
		}
		
	} catch (Exception $e) {
		$errmsg[] = $e->getMessage();
		return false;
	}
	
	return true;
}


/**********************************************/
// バリデーション
/**********************************************/
function _validation(&$dbh, $filefullname, $encoding, &$cols, &$arr_shop, &$errmsg)
{
	global $def_status;
	
	$row = 1;
	$datacnt = 0;
	$colnum = count($cols);
	
	// ファイル読み込み
	$file = new SplFileObject($filefullname); 
	$file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE); 
	
	$id_list = [];
	
	// １行ごとに判定
	foreach ($file as $data) { //行
		// SJISの場合、UTF8に変換
		if ($encoding != "UTF-8") {
			mb_convert_variables('UTF-8', 'SJIS-win', $data);
		}
		
		// 1行目のヘッダ？
/* 		if ($row == 1) {
			$header_txt = "";
			foreach ($cols as $col_name => $col_info) {
				$header_txt .= $col_info['name'];
			}
			$row++;
			continue; // ヘッダにつきスキップ
		} */
		
		// カラム数
		if (count($data) != $colnum) {
			$errmsg[] = '列数が'.$colnum.'ではありません('.count($data).'列)。';
			$row++;
			continue;
		}
		
		// データ数
		$datacnt++;
		
		// カラムバリデーション
		foreach ($cols as $col_name => $col_info) { //列
			$i = _i($col_name, $cols);
			
			// 必須？
			if ($col_info['must']) {
				if ($data[$i] == "") {
					$errmsg[] = $row . "行目：".$col_info['name']."は必須です。";
				}
			}
			
			if ($data[$i] !== "") {
				// 個別バリデーション
				$col_validation = $col_info['valid'];
				
				if ($col_name == 'Cs_ID' && isset($id_list[$data[$i]])) {
					$errmsg[] = $row . "行目：".$col_info['name']."の値が重複しています。";
				}else{
					if($col_name == 'Cs_ID'){
						if(!preg_match('/[0-9]{2}-[0-9]{5}/', $data[$i])){
							$errmsg[] = $row . "行目：".$data[$i]."は会員番号(00-00000)の正しい形式ではありません。";
						}	
					}
				}
				$id_list[$data[$i]] = $data[$i];
				
				// 数値バリデーション
				if ($col_info['type'] == "int") {
					$num = replace_hankaku($data[$i]);
					$num = str_replace(array(' ',','), '', $num);
					if (!is_numeric($num)) {
						$errmsg[] = $row . "行目：".$col_info['name']."は数字を入力してください。";
					} else {
						foreach ($col_validation as $check_type => $check_value) {
							if ($check_type == 'min') {
								if ($num < $check_value) {
									$errmsg[] = $row . "行目：".$col_info['name']."は".$check_value."以上の値を入力してください。";
								}
							} else if ($check_type == 'max') {
								if ($num > $check_value) {
									$errmsg[] = $row . "行目：".$col_info['name']."は".$check_value."以下の値を入力してください。";
								}
							}
						}
					}
				}
				
				// 日付バリデーション
				if ($col_info['type'] == "date") {
					if (!is_valid_date_string($data[$i])) {
						$errmsg[] = $row . "行目：".$col_info['name']."は日付を入力してください。";
					}
				}
				
				// 時刻バリデーション
				if ($col_info['type'] == "time") {
					if (!is_valid_time_string($data[$i])) {
						$errmsg[] = $row . "行目：".$col_info['name']."は時刻（HH:MM形式）を入力してください。";
					}
				}
				
				// 数字（ハイフン可）バリデーション
				if ($col_info['type'] == "number") {
					$num = replace_hankaku($data[$i]);
					if (!preg_match('/^[0-9-]+$/', $num)) {
						$errmsg[] = $row . "行目：".$col_info['name']."は数字とハイフンで入力してください。";
					} else {
						foreach ($col_validation as $check_type => $check_value) {
							if ($check_type == 'minlen') {
								if (mb_strlen($data[$i]) < (int)$check_value) {
									$errmsg[] = $row . "行目：".$col_info['name']."は".$check_value."文字以上で入力してください。";
								}
							} else if ($check_type == 'maxlen') {
								if (mb_strlen($data[$i]) > (int)$check_value) {
									$errmsg[] = $row . "行目：".$col_info['name']."は".$check_value."文字以下で入力してください。";
								}
							}
						}
					}
				}
				
				// 文字列？
				if ($col_info['type'] == "text") {
					$len = mb_strlen($data[$i]);
					foreach ($col_validation as $check_type => $check_value) {
						if ($check_type == 'minlen') {
							if ($len < (int)$check_value) {
								$errmsg[] = $row . "行目：".$col_info['name']."は".$check_value."文字以上で入力してください。";
							}
						} else if ($check_type == 'maxlen') {
							if ($len > (int)$check_value) {
								$errmsg[] = $row . "行目：".$col_info['name']."は".$check_value."文字以下で入力してください。";
							}
						}
					}
				}
			}
		}
		
		
		// エラーが100を超えたら終了
		if (count($errmsg) > 100) {
			$errmsg[] = "エラー件数が100を超えたため、アップロードを中止しました。";
			break;
		}
		
		$row++;
	}
	
	if ($datacnt == 0) {
		$errmsg[] = "登録データが見つかりません。";
	}
	//fclose($fp);
	
	return (count($errmsg) == 0 ? true : false);
}


/**********************************************/
// DB保存
/**********************************************/
function _save(&$dbh, $filefullname, $encoding, &$cols, &$arr_shop, &$err_msg)
{
	define("CLIENT_INSERT_LEN", 100);// 100件ごとにSQL発行
	
	$row = 0;
	
	$creator = $_SESSION[SESSION_BASE_NAME]['login_info']['Ad_Name'];
	
	$sql_head = "INSERT INTO Customer (";
	foreach ($cols as $key => $val) {
		$sql_head .= $key . ", ";
	}
	$sql_head .= "Cs_Creator ) VALUES ";
	$sql_val	= "";
	
	try {
		// トランザクションを開始する。オートコミットがオフになる
		$dbh->beginTransaction();
		
		$truncate = 'TRUNCATE Customer';
		$stmt = $dbh->prepare($truncate);
		$stmt->execute();
		
		// ファイル読み込み
		$file = new SplFileObject($filefullname); 
		$file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE); 
		
		// １行ごとに判定
		foreach ($file as $data) {
			// SJISの場合、UTF8に変換
			if ($encoding != "UTF-8") {
				mb_convert_variables('UTF-8', 'SJIS-win', $data);
			}
			
			// 1行目のヘッダ？
/* 			if ($row === 0) {
				$header_txt = "";
				foreach ($cols as $col_name => $col_info) {
					$header_txt .= $col_info['name'];
				}
				$row++;
				continue; // ヘッダにつきスキップ
			} */
			
			$row++;
			
			$sql_val .= "(";
			$sp_seq = 0;
			
			// カラムバリデーション
			foreach ($cols as $col_name => $col_info) {
				$i = _i($col_name, $cols);
				
				if ($col_info['type'] == "int") {
					$num = 0;
					if ($data[$i]) {
						$num = replace_hankaku($data[$i]);
						$num = str_replace(array('-',','), '', $num);
					}
					$sql_val .= "" . $num . ",";
					
				// 日付？
				} else if ($col_info['type'] == "date") {
					if ($data[$i]) {
						$sql_val .= "'" . $data[$i] . "',";
					} else {
						$sql_val .= "NULL,";
					}
					
				// 数字型？
				} else if ($col_info['type'] == "number") {
					$num = replace_hankaku($data[$i]);
					$sql_val .= "'" . $num . "',";
					
				// 文字列？
				} else if ($col_info['type'] == "text") {
					$sql_val .= "'" . $data[$i] . "',";
					
				// テーブル参照カラム？
				} else if ($col_info['type'] == "ref_key") {
					$num = 0;
					if ($data[$i]) {
						$num = replace_hankaku($data[$i]);
					}
					$sql_val .= "" . $num . ",";
					
				// その他
				} else {
					$sql_val .= "'" . $data[$i] . "',";
				}
			}
			$sql_val .= "'" . $creator . "'"; //Cs_Creator
			$sql_val .= "),";
			
			// n行単位にSQL発行
			if ($row % CLIENT_INSERT_LEN == 0) {
				// SQL
				$sql = $sql_head . $sql_val = rtrim($sql_val, ",") . ";";
				//echo $sql;
				$db = $dbh->prepare($sql);
				$db->execute();
				
				$sql_val = "";
				
			}
		}
		
		// 残りのSQLを実行
		if ($sql_val) {
			// SQL
			$sql = $sql_head . $sql_val = rtrim($sql_val, ",") . ";";
			$db = $dbh->prepare($sql);
			$db->execute();
		}
		
		// 成功ならコミット
		$dbh->commit();
		
		// 登録件数をSESSION保存
		$_SESSION[SESSION_BASE_NAME]['customer_info']['csv_cnt'] = $row;
		
		
	} catch (Exception $e) {
		$dbh->rollBack();
		$err_msg[] = "DB登録に失敗しました。" . $e->getMessage();
		
	}
	
	
	return (count($err_msg) == 0 ? true : false);
}


/**********************************************/
// 文字コード判定
/**********************************************/
function _encoding($filefullname)
{
	$ret = "";
	
	// 全体ではなく0～1024までを取得
	$contents = file_get_contents($filefullname, NULL, NULL, 0, 1024);
	
	// エンコーディング
	$encodings = array('UTF-8', 'SJIS', 'EUC');
	$enc = mb_detect_encoding($contents, $encodings);
	if ($enc == 'UTF-8' || $enc == 'SJIS') {
		$ret = $enc;
	}
	
	return $ret;
}


/**********************************************/
// INDEX番号取得
/**********************************************/
function _i($key, &$cols) {
	$i = 0;
	
	foreach ($cols as $name => $val) {
		if ($key == $name) {
			break;
		}
		$i++;
	}
	
	return $i;
}


/**********************************************/
// ID重複チェック
/**********************************************/
function _id_search($id, &$haystack)
{
	$ret = FALSE;
	
	foreach ($haystack as $key => $val) {
		if ($id == $val['Sp_Id']) {
			$ret = $key;
		}
	}
	
	return $ret;
}


/**********************************************/
// ID重複チェック
/**********************************************/
function _array_exists($id, &$arr_shop)
{
	$ret = FALSE;
	
	foreach ($arr_shop as $key => $val) {
		if ($id == $val['Sp_Id']) {
			$ret = $key;
			break;
		}
	}
	
	return $ret;
}




?>
<html>
<?php include 'header.php'; ?>
</head>
<body class="" style="">
	<div class="">
		<!-- Content Wrapper. Contains page content -->
		<div class="content_wrapper">
			<h3 class="detail-title">ログイン会員情報データ一括登録</h3>
			<!-- Content Header (Page header) -->
			<section class="content-header"></section>
			<!-- Main content -->
			<section class="content">

				<!-- メッセージ -->
				<?php if (isset($completemsg) && count($completemsg) > 0) { ?>
					<div class="alert alert-success">
						<p>
							<?php
								foreach ($completemsg as $msg) {
									echo "<strong>" . $msg . "</strong><br />";
								}
							?>
						</p>
					</div>
				<?php } ?>
				<?php if (isset($errmsg) && count($errmsg) > 0){ ?>
					<div class="alert alert-danger ">
						<p>
							<?php
								foreach ($errmsg as $msg) {
									echo "<strong>" . $msg . "</strong><br />";
								}
							?>
						</p>
					</div>
				<?php } ?>
				<!-- メッセージ -->

				<div class="flex-area mt10">
					<div class="f" >
						<form enctype="multipart/form-data" action="customer_import.php" accept-charset="" method="post">
							<div class="table" style="margin-bottom: 10px;">
								<div class="tr">
								<div class="th">CSVファイルのアップロード</div>
								</div>
								<div class="tr">
									<div class="td"><input type="file" name="csv_file" id="selectFileSample1" /></div>
									<div class="td"><input type="submit" name="frm_submit" class="btn btn-success" value="アップロード" style="margin-left:10px;" ></div>
								</div>
								<div class="tr">
									<div class="td">
										ファイル形式：CSV（半角カンマ区切り）<br>
										文字エンコード：Shift-JIS<br>
										<br>
										<a href="files/template.csv" class="" style="color:#03F; text-decoration:underline;" download>記入用テンプレートのダウンロードはこちら</a>（Shift-JIS版）<br/>
										<br>
										※１行目は必ずカラムヘッダを記載してください。<br>
										※入力内容は右表をご参照ください。
									</div>
								</div>
							</div><!-- <div class="table"> -->
						</form>

						<button type="submit" name="frm_close"class="btn" onClick="window.close();" >閉じる</button>
					</div>


					<div class="f">
						<div class="table-flex">
							<div class="tr">
								<div class="th">ファイル書式</div>
							</div>
							<div class="tr">
								<div class="th">項目</div>
								<div class="th">説明</div>
								<div class="th">例</div>
							</div>

<?php foreach ($cols as $col => $val) { ?>
							<div class="tr">
								<div class="td"><?php echo $val['name']; ?></div>
								<div class="td"><?php echo $val['memo']; ?></div>
								<div class="td"><?php echo $val['ex']; ?></div>
							</div>
<?php } ?>

						</div>
					</div>
				</div>
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->
	</div><!-- ./wrapper -->
	<?php include 'script.php';?>

	<!-- ダイアログ -->
	<div class="modal fade" id="modal_shop" style="display: none;" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header label-brown" style="color:#fff;">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title">勤務地コード</h4>
				</div>
				<div class="modal-body">
					<table class='table table-radius' style="width:85%; margin:0 auto 20px;">
						<tr style="background-color:#f0f0f0;">
							<td class="default text-center" style="white-space:nowrap;">勤務地コード</td>
							<td class="default text-center" style="">勤務地</td>
							<td class="default text-center" style="">所属会社</td>
							<td class="default text-center" style="">基本入り時刻</td>
							<td class="default text-center" style="">基本終了時刻</td>
							<td class="default text-center" style="">基本勤務時間</td>
						</tr>
						<?php foreach ($arr_shop as $key => $val) { ?>
						<tr>
							<td class="default  col-sm-2" style="white-space:nowrap;"><?php echo h($val['Sp_Id']); ?></td>
							<td class="default  col-sm-2" style="white-space:nowrap;"><?php echo h(mb_strimwidth($val['Sp_Name'], 0, 40, "...")); ?></td>
							<td class="default  col-sm-2" style="white-space:nowrap;"><?php echo h(mb_strimwidth($val['Cs_Name'], 0, 30, "...")); ?></td>
							<td class="default  col-sm-2" style="white-space:nowrap;"><?php echo h($val['Sp_Starttime']); ?></td>
							<td class="default  col-sm-2" style="white-space:nowrap;"><?php echo h($val['Sp_Endtime']); ?></td>
							<td class="default  col-sm-2" style="white-space:nowrap;"><?php echo format_time($val['Sp_WorkHours']); ?></td>
						</tr>
						<?php } ?>
					</table>
					<div class="text-center col-md-12"><button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button></div>
					<div class="clearfix"></div>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div>

</body>
</html>

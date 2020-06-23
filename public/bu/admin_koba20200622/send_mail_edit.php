<?php 
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

//初期化 各種定義
$mail_types = JSON_DECODE(SEND_MAIL_TYPE, true);
$mail_is_using_flags = JSON_DECODE(SEND_MAIL_IS_USING, true);

//エラーメッセージ
$err = [];

//入力パラメータ
$data = [];

$table_ary = [];

//html ・・・ HTML上に描画するカラム key = name
$table_ary['html'] = [
	'Sm_Type' => [
		'col_name' => 'メール種別',
		'must' => $must_label,
		'type' => 'select',
		'arr' => $mail_types,
		'placeholder' => ''
	],
	'Sm_Subject' => [
		'col_name' => 'メール件名',
		'must' => $must_label,
		'type' => 'string',
		'arr' => [] ,
		'placeholder' => ''
	],
	'Sm_Content' => [
		'col_name' => 'メール本文',
		'must' => '',
		'type' => 'textarea',
		'arr' => [] ,
		'placeholder' => ''
	],
	// 使用中カラムは一覧ページで一つだけ選択して切り替え(このページでは更新できない)
	// 'Sm_IsUsing' => [
	// 	'col_name' => '使用中',
	// 	'must' => $must_label,
	// 	'type' => 'radio',
	// 	'arr' => $mail_is_using_flags,
	// 	'placeholder' => '半角英数字'
	// ],
	'Sm_Invalid' => [
		'col_name' => 'ステータス',
		'must' => $must_label,
		'type' => 'radio',
		'arr' => $def_status ,
		'placeholder' => ''
	],
	'Sm_Seq' => [
		'col_name' => 'Seq',
		'must' => '',
		'type' => 'hidden',
		'arr' => [] ,
		'placeholder' => ''
	]
];

//db ・・・ 実際にクエリに使うカラム  key = column(物理名)
$table_ary['db'] = [
	'Sm_Type' => [
		'default' => 0,
		'type' => PDO::PARAM_INT
	],
	'Sm_Subject' => [
		'default' => 'not null',
		'type' => PDO::PARAM_STR
	],
	'Sm_Content' => [
		'default' => null,
		'type' => PDO::PARAM_STR
	],
	'Sm_Invalid' => [
		'default' => 0,
		'type' => PDO::PARAM_INT
	],
	'Sm_Seq' => [
		'default' => 'not null',
		'type' => PDO::PARAM_INT
	]
];

/*
//ページ遷移処理
各処理やテーブルに合わせて編集しないといけない箇所は下記3点です。
１．_validationの処理
２．_paramの処理
３．GetListCommonに渡す$conditionの内容
*/

if(isset($_POST) && !empty($_POST)){
	$data = $_POST;
	if(isset($data['submit']) && $data['submit'] == '確認') {

		//バリデーション
		$err = _validation($dbh,$data,$table_ary);

	}else if(isset($data['submit']) && $data['submit'] == '更新') {
		
		//引数整形
		$params = _param($data, $table_ary);

		//insert処理
		// echo '<pre>';
		// var_dump($params['param']);exit;
		UpdateCommon($dbh, 'SendMail', $params['param'], $params['condition'] );

		//select処理
		$condition['Sm_Seq'] = ['placeholder' => 'Sm_Seq' , 'value' => $data['Sm_Seq'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
		$data = GetListCommon($dbh, $condition, null, 'SendMail', 'Sm_Seq')[$data['Sm_Seq']];
		$data['submit'] = '更新';
		
	}else if(isset($data['back'])){ //戻るでサブミットされたPOST値で$dataを作る

	}else{ //一番最初に来たとき(戻るでサブミットされたPOST値ではなく、selectして$dataを作る)
		//select処理
		$condition['Sm_Seq'] = ['placeholder' => 'Sm_Seq' , 'value' => $data['Sm_Seq'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
		$fetch = GetListCommon($dbh, $condition, null, 'SendMail', 'Sm_Seq');

		if(isset($fetch[$data['Sm_Seq']])) {
			$data = $fetch[$data['Sm_Seq']];
		}else {
			// 取得失敗
			$err[] = 'クレカメール情報が取得できませんでした。';
		}
	}
}

function _validation($dbh,$data,$table_ary){

	$ret = [];

	foreach ($data as $column => $value){
		//必須チェック
		if(isset($table_ary['html'][$column]) && $table_ary['html'][$column]['must'] != '' && $value == ''){
			$ret[$column] = $table_ary['html'][$column]['col_name'].'が空白です。';
			continue; //とにかくなんか引っかかったらそこでそのカラムは終わり
		}
		if(isset($table_ary['html'][$column]) && 
			($table_ary['html'][$column]['type'] == 'select' || $table_ary['html'][$column]['type'] == 'radio' ) &&
			!isset($table_ary['html'][$column]['arr'][intval($value)])
		) {
			// 'arr'にある項目以外は選択不可
			$ret[$column] = $table_ary['html'][$column]['col_name']. 'の値が不正です。';
			continue;
		}

		//個別チェック(型)
		if($column == 'Sm_Subject' && mb_strlen($value) > 255) {
			$ret[$column] = $table_ary['html'][$column]['col_name'].'の文字数が多すぎます。';
		}

		//個別チェック(重複)
		// if( $column == 'Ad_Id' ){
		// 	$condition['Ad_Id']  = ['placeholder' => 'Ad_Id' , 'value' => $value, 'type' => PDO::PARAM_STR, 'method' => ' ='];
		// 	$condition['Ad_Seq'] = ['placeholder' => 'Ad_Seq', 'value' => $data['Ad_Seq'], 'type' => PDO::PARAM_INT, 'method' => ' !=']; //編集の時は重複対象から自レコードを除外
		// 	$admin_list = GetListCommon($dbh, $condition, ['Ad_Id','Ad_Seq'], 'Admin', 'Ad_Id');
		// 	if(isset($admin_list[$value])) {
		// 		$ret[$column] = 'このIDは、既に使われています。別の値にしてください。';
		// 		continue;
		// 	}
		// }
	}
	return $ret;
}


function _param($data, $table_ary){

	$params['param'] = [];
	$params['condition'] = [];

	foreach($table_ary['db'] as $column => $table_info) {
		if($column != 'Sm_Seq'){ //主key等

			if(isset($data[$column]) && $data[$column] != "" ){ //POSTに定義されてて、空白じゃない(要はちゃんとしたパラメータ)
				$params['param'][$column] = ['value' => $data[$column], 'type' => $table_info['type']];

				//固有の処理

			}else if(isset($data[$column]) && empty($data[$column]) && $table_info['default'] != 'not null'){ //POSTに定義されてて、空白だけどnull(or 0)がOKのやつ
				$params['param'][$column] = ['value' => $data[$column], 'type' => $table_info['type']]; //型エラーを防ぐためにDBのdefault値に差し替え
			}

			// 他の可能性(POSTに定義されてないDBカラム、POSTに定義されてて空白のnot nullカラム)

		}else{
			// POSTデータは全てstringなのでintにキャスト
			$params['condition'][$column] = ['value' => intval($data[$column]), 'type' => $table_info['type']];
		}
	}
	return $params;
}

?>
<html>
<?php include 'header.php'; ?>
</head>
<body class="skin-blue" style="">
	<div class="wrapper">
		<!-- Content Wrapper. Contains page content -->
		<div class="content_wrapper">
			<h3 class="detail-title">クレカメール更新</h3>
			<!-- Content Header (Page header) -->
			<section class="content-header"></section>
			<!-- Main content -->
			<section class="content">
				<?php if(!empty($err) && $data['submit'] != '確認' && $data['submit'] != '更新' ) { ?>
					<div class="alert alert-danger ">
						<p><strong>下記のメッセージをご確認の上、入力画面にお戻りください。</strong><br /></p>
							<?php foreach($err as $message) {
									echo '<p>' . $message . '</p>';
							} ?>
						</div>
						<form action="/admin/send_mail_detail.php" name="back_detail" method="POST" id="back_detail">
							<input type="hidden" name="Sm_Seq" value="<?php echo filter_input(INPUT_POST, 'Sm_Seq'); ?>">
							<button type="submit" name="back" class="btn" form="back_detail" >戻る</button>
						</form>
					</div>
				<?php } else { ?>
					<?php if (isset($data['submit']) && $data['submit'] == '更新' ) { ?>
					<div class="alert alert-success">
						<p><strong>更新に成功致しました。</strong><br /></p>
					</div>
					<?php }else if (isset($data['submit']) && $data['submit'] == '確認' && $err != [] ){ ?>
						<div class="alert alert-danger ">
							<p><strong>下記のメッセージをご確認の上、入力画面にお戻りください。</strong><br /></p>
						</div>
					<?php } ?>
					<form action="" name="frm_admin" method="POST">
						<div class="flex-area" style="padding-bottom: 0;">
							<div class="f" >
								<?php if ( isset($data['submit']) && ( $data['submit'] == '確認' || $data['submit'] == '更新' ) ) { ?>
									<?php include 'template_detail.php'; ?>
								<?php }else{ ?>
									<?php include 'template_edit.php'; ?>
									</div>
									<div class="f">
										<div class="table" style="margin-bottom: 10px;">
											<div class="tr"><div class="th">本文に埋め込める文字列</div></div>
											<div class="tr">
												<div class="td">メール本文中に以下に示す文字列を記述することで、変動する内容(会員氏名, 日付など)をメールに埋め込む事ができます。<br>
													以下に埋め込むテキストと意味、表示例を示します。左列の文字列をコピーして本文中に貼り付けてください。
												</div>
											</div>
											<div class="tr">
												<div class="th"><span style="font-weight: bold;">埋め込みテキスト</span></div>
												<div class="td"><span style="font-weight: bold;">意味</span></div>
												<div class="td"><span style="font-weight: bold;">表示例</span></div>
											</div>
											<div class="tr">
												<div class="th">{ID}</div>
												<div class="td">会員ID</div>
												<div class="td">CSV_TEST001</div>
											</div>
											<div class="tr">
												<div class="th">{NAME}</div>
												<div class="td">会員氏名</div>
												<div class="td">テスト　太郎</div>
											</div>
											<div class="tr">
												<div class="th">{DATE_YMD}</div>
												<div class="td">送信時点の西暦を含む年月日</div>
												<div class="td">2020年1月1日</div>
											</div>
											<div class="tr">
												<div class="th">{DATE_MD}</div>
												<div class="td">送信時点の月日</div>
												<div class="td">1月1日</div>
											</div>
											<div class="tr">
												<div class="th">{LIMIT}</div>
												<div class="td">クレジットカード有効期限</div>
												<div class="td">2020年11月</div>
											</div>
										</div>
								<?php } ?>
							</div>
						</div>
						<?php if(isset($data['submit']) && $data['submit'] == '確認' && empty($err) ) { //確認画面かつエラーが無い時 ?>
							<button type="submit" name="back" class="btn" >戻る</button>
							<input type="submit" name="submit" class="btn btn-success" value="更新" style="margin-left:10px;" >
						<?php }else if(isset($data['submit']) && $data['submit'] == '確認' && !empty($err) ) { //確認画面だがエラーがある時?>
							<button type="submit" name="back"class="btn" >戻る</button>
						<?php }else if(isset($data['submit']) && $data['submit'] == '更新' ){ //更新画面 ?>
							<button type="submit" name="back" class="btn" form="back_detail" >戻る</button><!-- 完了画面の戻るは、admin_detail.php行き -->
						<?php }else{ //完了画面?>
							<button type="submit" name="back" class="btn" form="back_detail" >戻る</button><!-- 入力画面の戻るは、admin_detail.php行き -->
							<input type="submit" name="submit" class="btn btn-success" value="確認" style="margin-left:10px;" >
						<?php } ?>
					</form>
					<form action="/admin/send_mail_detail.php" name="back_detail" method="POST" id="back_detail"></form>
				<?php } ?>
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->
	</div><!-- ./wrapper -->
	<?php include 'script.php';?>
</body>
</html>

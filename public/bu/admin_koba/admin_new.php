<?php 
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

//初期化 各種定義
//エラーメッセージ
$err = [];

//入力パラメータ
$data = [];

//Statusの配列
$def_status = get_defined_array(DEF_STATUS);

//必須フラグ
$must_label = '<span class="label label-danger"> 必須 </span>';

//テーブルタグの描画配列
$table_ary = [];

//html ・・・ HTML上に描画するカラム key = name
$table_ary['html']['Ad_Id']				 = ['col_name' => 'ログインID',	 'must' => $must_label,	'type' => 'string','arr' => [] ,			'placeholder' => '半角英数字'];
$table_ary['html']['Ad_Name']			 = ['col_name' => '氏名',		 'must' => $must_label,	'type' => 'string','arr' => [] ,			'placeholder' => ''];
$table_ary['html']['Ad_NameKana']		 = ['col_name' => '氏名カナ',		 'must' => '',			'type' => 'string','arr' => [] ,			'placeholder' => ''];
$table_ary['html']['Ad_Password_Text']	 = ['col_name' => 'ログインPW',	 'must' => $must_label,	'type' => 'string','arr' => [] ,			'placeholder' => '半角英数字'];
$table_ary['html']['Ad_Invalid']		 = ['col_name' => 'ステータス',	 'must' => $must_label,	'type' => 'radio', 'arr' => $def_status ,	'placeholder' => ''];
$table_ary['html']['Ad_Unco']			 = ['col_name' => 'ウンコ',		 'must' => '',			'type' => 'hidden','arr' => [] ,			'placeholder' => '']; //実験

//db ・・・ 実際にクエリに使うカラム  key = column(物理名)
$table_ary['db']['Ad_Id']				 = ['default' => 'not null','type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_Name']				 = ['default' => null,		'type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_NameKana']			 = ['default' => null,		'type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_Password_Text']	 = ['default' => null,		'type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_Password']			 = ['default' => null,		'type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_Invalid']			 = ['default' => 0,			'type' => PDO::PARAM_INT ];


// ページ遷移
if(isset($_POST) && !empty($_POST)){
	$data = $_POST;
	if(isset($data['submit']) && $data['submit'] == '確認') {
		//バリデーション
		$err = _validation($dbh,$data,$table_ary);
	}else if(isset($data['submit']) && $data['submit'] == '登録') {
		
		//引数整形
		$params = _param($data, $table_ary);
//		var_dump($params);
		
		//insert処理
		$ins_ret = InsertCommon($dbh, 'Admin', $params, 'Ad_Seq');
		
		//select処理
		$condition['Ad_Seq'] = ['placeholder' => 'Ad_Seq' , 'value' => $ins_ret, 'type' => PDO::PARAM_INT, 'method' => ' ='];
		$data = GetListCommon($dbh, $condition, null, 'Admin', 'Ad_Seq')[$ins_ret];
		$data['submit'] = '登録';
		
	}else if(isset($data['back'])){
		
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
		//個別チェック
		if( $column == 'Ad_Password_Text' && !is_valid_hankaku_eisu($value) ){
			$ret[$column] = $table_ary['html'][$column]['col_name'].'は、半角英数字のみにしてください。';
			continue;
		}

		if($column == 'Ad_Id' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$ret[$column] = $table_ary['html'][$column]['col_name'] . 'は、メールアドレスの形式ではありません。';
			continue;
		}

		//IDの重複チェック
		if( $column == 'Ad_Id' ){
			$condition['Ad_Id'] = ['placeholder' => 'Ad_Id' , 'value' => $value, 'type' => PDO::PARAM_STR, 'method' => ' ='];
			$admin_list = GetListCommon($dbh, $condition, ['Ad_Id','Ad_Seq'], 'Admin', 'Ad_Id');
			if(isset($admin_list[$value])) {
				$ret[$column] = 'このIDは、既に使われています。別の値にしてください。';
			}
		}
		
	}
	return $ret;
}


function _param($data, $table_ary){
	
	$params = [];
	
	foreach($table_ary['db'] as $column => $table_info) {
		if(!isset($data[$column]) || empty($data[$column]) ){
			$params[$column] = ['value' => $table_info['default'], 'type' => $table_info['type']];
		}else{
			$params[$column] = ['value' => $data[$column], 'type' => $table_info['type']];
		}
		//個別設定
		if($column == 'Ad_Password'){
			$params[$column] = ['value' => password_hash($data['Ad_Password_Text'], PASSWORD_BCRYPT), 'type' => $table_info['type']];
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
			<h3 class="detail-title">管理者登録</h3>
			<!-- Content Header (Page header) -->
			<section class="content-header"></section>
			<!-- Main content -->
			<section class="content">
				<?php if (isset($data['submit']) && $data['submit'] == '登録' ) { ?>
				<div class="alert alert-success">
					<p><strong>登録に成功致しました。</strong><br /></p>
				</div>
				<?php }else if (isset($data['submit']) && $data['submit'] == '確認' && $err != [] ){ ?>
					<div class="alert alert-danger ">
						<p><strong>下記のメッセージをご確認の上、入力画面にお戻りください。</strong><br /></p>
					</div>
				<?php } ?>
				<form action="" name="frm_admin" method="POST">
					<div class="flex-area">
						<div class="f" >
							<?php if ( isset($data['submit']) && ( $data['submit'] == '確認' || $data['submit'] == '登録' ) ) { ?>
								<?php include 'template_detail.php'; ?>
							<?php }else{ ?>
								<?php include 'template_new.php'; ?>
							<?php } ?>
						<?php if(isset($data['submit']) && $data['submit'] == '確認' && empty($err) ) { //確認画面かつエラーが無い時 ?>
							<button type="submit" name="back"class="btn" >戻る</button>
							<input type="submit" name="submit" class="btn btn-success" value="登録" style="margin-left:10px;" >
						<?php }else if(isset($data['submit']) && $data['submit'] == '確認' && !empty($err) ) { //確認画面だがエラーがある時?>
							<button type="submit" name="back"class="btn" >戻る</button>
						<?php }else if(isset($data['submit']) && $data['submit'] == '登録' ){ //登録画面 ?>
							<button type="button" name="frm_close"class="btn" onClick="window.close();" >閉じる</button>
						<?php }else{ //完了画面?>
							<button type="button" name="frm_close"class="btn" onClick="window.close();" >閉じる</button>
							<input type="submit" name="submit" class="btn btn-success" value="確認" style="margin-left:10px;" >
						<?php } ?>
						</div>
					</div>
				</form>
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->
	</div><!-- ./wrapper -->
	<?php include 'script.php';?>
</body>
</html>

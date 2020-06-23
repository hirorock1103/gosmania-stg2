<?php 
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

//初期化 各種定義
//エラーメッセージ
$err = [];

//入力パラメータ
$data = [];

$table_ary = [];

//html ・・・ HTML上に描画するカラム key = name
$table_ary['html']['Ad_Id']							 = ['col_name' => 'ログインID',		 'must' => $must_label,	 'type' => 'string' , 'arr' => [] ,			'placeholder' => '半角英数字'];
$table_ary['html']['Ad_Name']						 = ['col_name' => '氏名',					 'must' => $must_label,	 'type' => 'string' , 'arr' => [] ,			'placeholder' => ''];
$table_ary['html']['Ad_NameKana']				 = ['col_name' => '氏名カナ',				'must' => '',					 'type' => 'string' , 'arr' => [] ,			'placeholder' => ''];
$table_ary['html']['Ad_Password_Text']	 = ['col_name' => 'ログインPW',	 		'must' => $must_label, 'type' => 'string' , 'arr' => [] ,			'placeholder' => '半角英数字'];
$table_ary['html']['Ad_MailAddress']		 = ['col_name' => 'メールアドレス',	 'must' => '',					 'type' => 'string' , 'arr' => [] ,			'placeholder' => '半角英数字'];
$table_ary['html']['Ad_Invalid']				 = ['col_name' => 'ステータス',			 'must' => $must_label, 'type' => 'radio', 'arr' => $def_status ,	'placeholder' => ''];
$table_ary['html']['Ad_Seq']						 = ['col_name' => 'SEQ',					 'must' => '',					 'type' => 'hidden', 'arr' => [] ,			'placeholder' => ''];

//db ・・・ 実際にクエリに使うカラム  key = column(物理名)
$table_ary['db']['Ad_Id']						 = ['default' => 'not null','type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_Name']					 = ['default' => null,			'type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_NameKana']			 = ['default' => null,			'type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_Password_Text'] = ['default' => null,			'type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_Password']			 = ['default' => null,			'type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_MailAddress']	 = ['default' => 'not null','type' => PDO::PARAM_STR ];
$table_ary['db']['Ad_Invalid']			 = ['default' => 0,					'type' => PDO::PARAM_INT ];
$table_ary['db']['Ad_Seq']					 = ['default' => 'not null','type' => PDO::PARAM_INT ];


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
		UpdateCommon($dbh, 'Admin', $params['param'], $params['condition'] );
		
		//select処理
		$condition['Ad_Seq'] = ['placeholder' => 'Ad_Seq' , 'value' => $data['Ad_Seq'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
		$data = GetListCommon($dbh, $condition, null, 'Admin', 'Ad_Seq')[$data['Ad_Seq']];
		$data['submit'] = '更新';
		
	}else if(isset($data['back'])){ //戻るでサブミットされたPOST値で$dataを作る
	
	}else{ //一番最初に来たとき(戻るでサブミットされたPOST値ではなく、selectして$dataを作る)
		//select処理
		$condition['Ad_Seq'] = ['placeholder' => 'Ad_Seq' , 'value' => $data['Ad_Seq'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
		$data = GetListCommon($dbh, $condition, null, 'Admin', 'Ad_Seq')[$data['Ad_Seq']];
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
		//個別チェック(型)
		if($column == 'Ad_Password_Text' && !is_valid_hankaku_eisu($value) ){
			$ret[$column] = $table_ary['html'][$column]['col_name'].'は、半角英数字のみにしてください。';
			continue;
		}

		if($column  == 'Ad_MailAddress' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$ret[$column] = $table_ary['html'][$column]['col_name'] . 'は、メールアドレスの形式ではありません。';
			continue;
		}
		
		//個別チェック(重複)
		if( $column == 'Ad_Id' ){
			$condition['Ad_Id']  = ['placeholder' => 'Ad_Id' , 'value' => $value, 'type' => PDO::PARAM_STR, 'method' => ' ='];
			$condition['Ad_Seq'] = ['placeholder' => 'Ad_Seq', 'value' => $data['Ad_Seq'], 'type' => PDO::PARAM_INT, 'method' => ' !=']; //編集の時は重複対象から自レコードを除外
			$admin_list = GetListCommon($dbh, $condition, ['Ad_Id','Ad_Seq'], 'Admin', 'Ad_Id');
			if(isset($admin_list[$value])) {
				$ret[$column] = 'このIDは、既に使われています。別の値にしてください。';
				continue;
			}
		}else if( $column == 'Ad_MailAddress' ){
			$condition['Ad_MailAddress'] = ['placeholder' => 'Ad_MailAddress' , 'value' => $value, 'type' => PDO::PARAM_STR, 'method' => ' ='];
			$admin_list = GetListCommon($dbh, $condition, ['Ad_MailAddress','Ad_Seq'], 'Admin', 'Ad_MailAddress');
			if(isset($admin_list[$value])) {
				$ret[$column] = 'このメールアドレスは、既に使われています。別の値にしてください。';
			}
		}
	}
	return $ret;
}


function _param($data, $table_ary){
	
	$params['param'] = [];
	$params['condition'] = [];
	
	foreach($table_ary['db'] as $column => $table_info) {
		if($column != 'Ad_Seq'){ //主key等
			
			if(isset($data[$column]) && !empty($data[$column]) ){ //POSTに定義されてて、空白じゃない(要はちゃんとしたパラメータ)
				$params['param'][$column] = ['value' => $data[$column], 'type' => $table_info['type']];
				
				//固有の処理
				if($column == 'Ad_Password_Text'){
					$params['param']['Ad_Password'] = ['value' => password_hash($data['Ad_Password_Text'], PASSWORD_BCRYPT), 'type' => $table_info['type']];
				}
			
			}else if(isset($data[$column]) && empty($data[$column]) && $table_info['default'] != 'not null'){ //POSTに定義されてて、空白だけどnull(or 0)がOKのやつ
				$params['param'][$column] = ['value' => $data[$column], 'type' => $table_info['type']]; //型エラーを防ぐためにDBのdefault値に差し替え
			}
			
			// 他の可能性(POSTに定義されてないDBカラム、POSTに定義されてて空白のnot nullカラム)
			
			
			
		}else{
			$params['condition'][$column] = ['value' => $data[$column], 'type' => $table_info['type']];
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
			<h3 class="detail-title">管理者更新</h3>
			<!-- Content Header (Page header) -->
			<section class="content-header"></section>
			<!-- Main content -->
			<section class="content">
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
					<div class="flex-area">
						<div class="f" >
							<?php if ( isset($data['submit']) && ( $data['submit'] == '確認' || $data['submit'] == '更新' ) ) { ?>
								<?php include 'template_detail.php'; ?>
							<?php }else{ ?>
								<?php include 'template_edit.php'; ?>
							<?php } ?>
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
						</div>
					</div>
				</form>
				<form action="admin_detail.php" name="back_detail" method="POST" id="back_detail"></form>
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->
	</div><!-- ./wrapper -->
	<?php include 'script.php';?>
</body>
</html>

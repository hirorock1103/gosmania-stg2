<?php 
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

//初期化 各種定義
//エラーメッセージ
$err = [];

//入力パラメータ
$data = [];

//テーブルタグの描画配列
$table_ary = [];

//html ・・・ HTML上に描画するカラム key = name
$table_ary['html']['Ad_Id']				 = ['col_name' => 'ログインID',	 'must' => $must_label,	'type' => 'string', 'arr' => [] ,			'placeholder' => '半角英数字'];
$table_ary['html']['Ad_Name']			 = ['col_name' => '氏名',		 'must' => $must_label,	'type' => 'string', 'arr' => [] ,			'placeholder' => ''];
$table_ary['html']['Ad_NameKana']		 = ['col_name' => '氏名カナ',		 'must' => '',			'type' => 'string', 'arr' => [] ,			'placeholder' => ''];
$table_ary['html']['Ad_Password_Text']	 = ['col_name' => 'ログインPW',	 'must' => $must_label,	'type' => 'string', 'arr' => [] ,			'placeholder' => '半角英数字'];
$table_ary['html']['Ad_Invalid']		 = ['col_name' => 'ステータス',	 'must' => $must_label,	'type' => 'radio',  'arr' => $def_status ,	'placeholder' => ''];
$table_ary['html']['Ad_Seq']			 = ['col_name' => 'Seq',		 'must' => '',			'type' => 'hidden', 'arr' => [] ,			'placeholder' => ''];

if(isset($_POST) && !empty($_POST)){
	$data = $_POST;
	$condition['Ad_Seq'] = ['placeholder' => 'Ad_Seq' , 'value' => $data['Ad_Seq'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$data = GetListCommon($dbh, $condition, null, 'Admin', 'Ad_Seq')[$data['Ad_Seq']];
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
				<form action="admin_edit.php" name="frm_admin" method="POST">
					<div class="flex-area">
						<div class="f" >
							<?php include 'template_detail.php'; ?>
							<button type="button" name="frm_close"class="btn" onClick="window.close();" >閉じる</button>
							<input type="submit" name="submit" class="btn btn-success" value="編集" style="margin-left:10px;" >
						</div>
					</div>
				</form>
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->
	</div><!-- ./wrapper -->
	<?php include 'script.php';?>
</body>
</html>

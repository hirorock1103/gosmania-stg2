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

//テーブルタグの描画配列
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
	'Sm_IsUsing' => [
		'col_name' => '使用中',
		'must' => $must_label,
		'type' => 'radio',
		'arr' => $mail_is_using_flags,
		'placeholder' => '半角英数字'
	],
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

if(isset($_POST) && !empty($_POST)){
	$data = params();
	$condition['Sm_Seq'] = ['placeholder' => 'Sm_Seq' , 'value' => $data['Sm_Seq'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$fetch = GetListCommon($dbh, $condition, null, 'SendMail', 'Sm_Seq');

	if(isset($fetch[$data['Sm_Seq']])) {
		$data = $fetch[$data['Sm_Seq']];
	}else {
		// 取得失敗
		$err[] = 'クレカメール情報が取得できませんでした。';
	}
}


function params() {
	$data = [];
	$data['Sm_Seq'] = filter_input(INPUT_POST, 'Sm_Seq') ? intval(filter_input(INPUT_POST, 'Sm_Seq')) : 0;
	return $data;
}

?>
<html>
<?php include 'header.php'; ?>
</head>
<body class="skin-blue" style="">
	<div class="wrapper">
		<!-- Content Wrapper. Contains page content -->
		<div class="content_wrapper">
			<h3 class="detail-title">クレカメール詳細</h3>
			<!-- Content Header (Page header) -->
			<section class="content-header"></section>
			<!-- Main content -->
			<section class="content">
				<form action="/admin/send_mail_edit.php" name="frm_admin" method="POST">
					<div class="flex-area">
						<div class="f" >
							<?php if (!empty($err)) {
								echo '<div class="alert alert-danger">';
								foreach($err as $message) {
									echo '<p>' . $message . '</p>';
								}
								echo '</div>';
								echo '<button type="button" name="frm_close"class="btn" onClick="window.close();" >閉じる</button>';
							} else {
								include 'template_detail.php'; ?>
								<button type="button" name="frm_close"class="btn" onClick="window.close();" >閉じる</button>
								<input type="submit" name="submit" class="btn btn-success" value="編集" style="margin-left:10px;" >
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

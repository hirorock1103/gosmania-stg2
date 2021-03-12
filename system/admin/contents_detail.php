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
$table_ary['html']['contents_name']			= ['col_name' => 'コンテンツ名',		 'must' => $must_label,	'type' => 'string','arr' => [] ,			'placeholder' => ''];
$table_ary['html']['status']		 		= ['col_name' => 'ステータス',	 'must' => $must_label,	'type' => 'radio', 'arr' => $def_status ,	'placeholder' => ''];

// 基本情報(DBからの表示)(最初に遷移)
if(isset($_POST) && !empty($_POST) && !isset($_POST["fileupload"])){
	$data = $_POST;
	$condition['id'] = ['placeholder' => 'id' , 'value' => $data['id'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$data = GetListCommon($dbh, $condition, null, 'contents', 'id')[$data['id']];
}
if (true) {
	$allshow_flag = true;
	$arr_files = _allshow($dbh);
}
function _allshow($dbh)
{
	$condition = array();
	$column = array('id', 'contents_id', 'file_name', 'status');
	return GetListCommon($dbh, $condition, $column, 'contentsfile', 'id');
}
// ファイルアップロード
if(isset($_POST["fileupload"]) && !empty($_POST["fileupload"])){
	// 基本情報の表示
	$data = $_POST;
	var_dump($_POST);
	$condition['id'] = ['placeholder' => 'id' , 'value' => $data['contents_id'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$data = GetListCommon($dbh, $condition, null, 'contents', 'id')[$data['contents_id']];
	// バリデーション処理
	// エラーがなければで分岐
	$fileendmag = "ファイルのアップロードが完了しました";
	$fileendmag = "入力エラーがあります";
	$fileendmag = "ファイルのアップロードが失敗しました";
	save_upload_file("contents_folder",$_POST["file_name"],$_POST["file_name"]);
}
// ファイル一覧
if(!isset($_POST["fileupload"])){
	// ファイル一覧の表示
	$file_data = $_POST;
	$file_condition['id'] = ['placeholder' => 'id' , 'value' => $file_data['id'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$file_data = GetListCommon($dbh, $file_condition, null, 'contentsfile', 'id')[$file_data['id']];
}
?>
<html>
<?php include 'header.php'; ?>
</head>
<body class="skin-blue">
	<div class="wrapper">
		<!-- Content Wrapper. Contains page content -->
		<div class="content_wrapper">
			<h3 class="detail-title">コンテンツ登録</h3>
			<!-- Content Header (Page header) -->
			<section class="content-header"></section>
			<!-- Main content -->
			<section class="content">
				<!-- 基本情報 -->
				<form action="contents_edit.php" name="frm_contents" method="POST">
					<div class="flex-area">
						<div class="f" >
							<?php include 'template_detail.php'; ?>
							<button type="button" name="frm_close"class="btn" onClick="window.close();" >閉じる</button>
							<input type="hidden" name="contents_id" value="<?= $data['id'] ?>">
							<input type="submit" name="submit" class="btn btn-success" value="編集" style="margin-left:10px;" >
						</div>
					</div>
				</form>
				<!-- ファイルアップロード -->
				<form action="contents_detail.php" name="fileupload" method="POST" enctype="multipart/form-data">
					<div class="flex-area">
						<div class="f" >
							<?php $hoge="ファイルアップロード" ?>
							<div class="table" style="margin-bottom: 10px;">
								<div class="tr"><div class="th"><?php echo $hoge ?? '基本情報'; ?></div></div>
								<div class="tr">
									<div class="th">ファイル名</div>
									<div class="td"><input type="text" name="file_name" placeholder="ファイル名を入力">
								</div>
								</div>
								<div class="tr">
									<div class="th">ファイル</div>
									<div class="td"><input type="file" name="file"></div>
								</div>
								<div class="tr">
									<div class="th">ステータス</div>
									<div class="td">
										<div class="btn-group" data-toggle="buttons">
											<label class="btn btn-default active"><input type="radio" name="status" autocomplete="off" value="0" checked=""> 有効</label>
											<label class="btn btn-default "><input type="radio" name="status" autocomplete="off" value="1"> 無効</label>
										</div>
									</div>
								</div>
								<div class="tr">
									<div class="th">操作</div>
									<div class="td">
										<input type="hidden" name="contents_id" value="<?= $data['id'] ?>">
										<input type="submit" name="fileupload" class="btn btn-success" value="追加" style="margin-left:10px;" >
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
				<!-- ファイル一覧 -->
				<form action="contents_edit.php" name="frm_contents" method="POST">
					<div class="flex-area">
						<div class="f" >
							<?php $hoge="ファイル一覧" ?>
							<div class="table" style="margin-bottom: 10px;">
								<div class="tr"><div class="th"><?php echo $hoge ?? '基本情報'; ?></div></div>
								<table class="table table_result_client table_sp">
									<thead>
										<tr>
											<th class="listUser table_result_element" style="background: #F8F4ED;">ファイル名</th>
											<th class="listUser table_result_element" style="background: #F8F4ED;">ステータス</th>
											<th class="listUser table_result_element" style="background: #F8F4ED;">詳細</th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($arr_files as $seq => $value) { ?>
										<tr>
											<!-- <td class="listUser" ><?php //echo h($value['contents_name']); ?></td> -->
											<td class="listUser" ><?php echo h($value['file_name']); ?></td>
											<td class="listUser"><?php echo $def_status[$value['status']]; ?></td>
											<td class="listUser" style="padding:8px 10px" ><button type="submit" name="id" class="btn" value="<?php echo h($value['id']); ?>" style="padding:3px 20px">詳細</button></td>
										</tr>
									<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</form>
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->
	</div><!-- ./wrapper -->
	<?php include 'script.php';?>
</body>
</html>

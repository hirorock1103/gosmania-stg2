<?php 
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

//初期化 各種定義
$file_data = array();
$title = "";
$guard_flag = 0;

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
if(isset($_POST) && !empty($_POST) && !isset($_POST["fileupload"]) && !isset($_POST["back"]) && !isset($_POST["filechange"])){
	$data = $_POST;
	$contents_id = $data['id'];
	$condition['id'] = ['placeholder' => 'id' , 'value' => $data['id'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$data = GetListCommon($dbh, $condition, null, 'contents', 'id')[$data['id']];
	$allshow_flag = true;
	$arr_files = _allshow($dbh);
	// ファイル一覧の表示
	$file_data = $_POST;
/* 	$file_condition['contents_id'] = ['placeholder' => 'contents_id' , 'value' => $file_data["id"], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$file_data = GetListCommon($dbh, $file_condition, null, 'contentsfile', 'contents_id'); */
	// ファイル一覧の表示
	$sql = "select * from contentsfile where contents_id = :id order by id asc";
	$db = $dbh->prepare($sql);
	$db->bindValue(':id', $contents_id, PDO::PARAM_INT);
	$db->execute();
	$file_data = $db->fetchAll();
}
// 編集から戻ってきたとき
if(isset($_POST["back"]) && !isset($_POST["fileupload"])){
	$data["id"] = $_SESSION['back_data'];
	$contents_id = $data['id'];
	$condition['id'] = ['placeholder' => 'id' , 'value' => $data['id'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$data = GetListCommon($dbh, $condition, null, 'contents', 'id')[$data['id']];
	$allshow_flag = true;
	$arr_files = _allshow($dbh);
	// ファイル一覧の表示
/* 	$file_condition['contents_id'] = ['placeholder' => 'contents_id' , 'value' => $_SESSION['back_data'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$file_data = GetListCommon($dbh, $file_condition, null, 'contentsfile', 'contents_id'); */
	// ファイル一覧の表示
	$sql = "select * from contentsfile where contents_id = :id order by id asc";
	$db = $dbh->prepare($sql);
	$db->bindValue(':id', $contents_id, PDO::PARAM_INT);
	$db->execute();
	$file_data = $db->fetchAll();
}

// ファイルアップロード
if(isset($_POST["fileupload"]) && !empty($_POST["fileupload"])){
	// 基本情報の表示
	$data = $_POST;
	$contents_id = $data['contents_id'];
	$title = $data['title'];
	$guard_flag = $data['guard_flag'];


	//情報しゅとく用	
	$condition['id'] = ['placeholder' => 'id' , 'value' => $data['contents_id'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$data = GetListCommon($dbh, $condition, null, 'contents', 'id')[$data['contents_id']];
	
	// バリデーション処理
	$error = [];

	//アップロードファイル情報
	$allow = ['jpeg','jpg','png', 'pdf', 'gif','JPEG','JPG'];

	/** main image */
	$tempfile = "";
	$th_tempfile = "";
	$filename = "";
	$th_filename = "";
	$filepath = "";
	$th_filepath = "";

	$is_pdf = false;

	if(isset($_FILES['file']['tmp_name'])){

		$tempfile = $_FILES['file']['tmp_name']; // 一時ファイル名
		//拡張子
		$list = explode('.',$_FILES['file']['name']);
		$kaku = $list[(count($list)-1)];

		if( in_array($kaku, $allow) ) {
			$filename = $contents_id."_".time().".".$kaku; 
			$filepath = "image/contents_folder/".$filename;

			if($kaku == 'pdf'){
				$is_pdf = true;
			}
		} else{
			//許可されていない拡張子
			$error['main_image'] = "許可されていないファイルです";
		}


	}else{
		$error['main_image'] = "メイン画像は必須です！";
	}

	if(empty($title)){
		$error['title'] = "タイトルは必須です";
	}

	/** thum image */
	if( !empty($tempfile) && isset($_FILES['thum']['tmp_name'])){

		$th_tempfile = $_FILES['thum']['tmp_name']; // 一時ファイル名

		if(!empty($th_tempfile)){

			//thumnail画像あり
			$list = explode('.',$_FILES['thum']['name']);
			$kaku = $list[(count($list)-1)];
			if( in_array($kaku, $allow) ) {
				$th_filename = $contents_id."_".time()."_th.".$kaku; 
				$th_filepath = "image/contents_folder/".$th_filename;
				//ファイルアップロード
				if (is_uploaded_file($th_tempfile)) {
					if ( move_uploaded_file($th_tempfile , $th_filepath )) {
						//echo $filename . "をアップロードしました。";

					} else {
						//$error[] = "ファイルをアップロードできません。";
						$error['thumnail'] = "ファイルをアップロードできませんでした。";
					}
				}else {
					//$error[] = "ファイルが選択されていません。";
					$error['thumnail'] = "ファイルが選択されていません。";
				} 
			} else{
				//許可されていない拡張子
				$error['thumnail'] = "許可されていないファイルです";
			}

		}else{

			//thumnailなし　※pdfのときは必須！
			if($is_pdf == true){
				$error['thumnail'] = "ファイルが選択されていません。";
			}

		}



	}


	//check	
	if(empty($error)){
		//ファイルアップロード
		if (is_uploaded_file($tempfile)) {
			if ( move_uploaded_file($tempfile , $filepath )) {
				//echo $filename . "をアップロードしました。";
				try{
					$params = [];
					$params['contents_id'] = ['placeholder' => 'contents_id' , 'value' => $contents_id, 'type' => PDO::PARAM_INT, 'method' => ' ='];
					$params['guard_flag'] = ['placeholder' => 'guard_flag' , 'value' => $guard_flag, 'type' => PDO::PARAM_INT, 'method' => ' ='];
					$params['title'] = ['placeholder' => 'title' , 'value' => $title, 'type' => PDO::PARAM_STR, 'method' => ' ='];
					$params['file_name'] = ['placeholder' => 'file_name' , 'value' => $filename, 'type' => PDO::PARAM_STR, 'method' => ' ='];
					$params['status'] = ['placeholder' => 'status' , 'value' => 1, 'type' => PDO::PARAM_INT, 'method' => ' ='];
					if($th_filename != ""){
						$params['thumbnail_name'] = ['placeholder' => 'thumbnail_name' , 'value' => $th_filename, 'type' => PDO::PARAM_STR, 'method' => ' ='];
					}
					$ins_ret = InsertCommon($dbh, 'contentsfile', $params, 'id');
				}catch(Exception $e){
					$error['main_image'] = $e->getMessage();
				}catch(PDOException $e){
					$error['main_image'] = $e->getMessage();
				}
			} else {
				//$error[] = "ファイルをアップロードできません。";
				$error['main_image'] = "ファイルをアップロードできませんでした。";
			}
		}else {
			//$error[] = "ファイルが選択されていません。";
			$error['main_image'] = "ファイルが選択されていません。";
		} 
	}
	// エラーがなければで分岐

	// ファイル一覧の表示
	$sql = "select * from contentsfile where contents_id = :id order by id asc";
	$db = $dbh->prepare($sql);
	$db->bindValue(':id', $contents_id, PDO::PARAM_INT);
	$db->execute();
	$file_data = $db->fetchAll();
}

// ファイル一覧　内容変更
if(isset($_POST["filechange"]) && !empty($_POST["filechange"])){
	switch ($_POST["filechange"]) {
		case "有効化":
			$fileid = $_POST["fileid"];
			$stval = 0;
			file_chenge($dbh,$fileid,$stval);
			break;
		case "無効化":
			$fileid = $_POST["fileid"];
			$stval = 1;
			file_chenge($dbh,$fileid,$stval);
			break;
		case "削除":
			$fileid = $_POST["fileid"];
			file_delite($dbh,$fileid);
			break;
	}
	// 情報表示

	/*
	$data["id"] = $_SESSION['back_data'];
	$condition['id'] = ['placeholder' => 'id' , 'value' => $data['id'], 'type' => PDO::PARAM_INT, 'method' => ' ='];
	$contents_id = $data['id'];
	$data = GetListCommon($dbh, $condition, null, 'contents', 'id')[$data['id']];
	$allshow_flag = true;
	$arr_files = _allshow($dbh);
	// ファイル一覧の表示
	$sql = "select * from contentsfile where contents_id = :id order by id asc";
	$db = $dbh->prepare($sql);
	$db->bindValue(':id', $contents_id, PDO::PARAM_INT);
	$db->execute();
	$file_data = $db->fetchAll();
	*/
	$contents_id = $_POST['contents_id'];
	// ファイル一覧の表示
	$sql = "select * from contentsfile where contents_id = :id order by id asc";
	$db = $dbh->prepare($sql);
	$db->bindValue(':id', $contents_id, PDO::PARAM_INT);
	$db->execute();
	$file_data = $db->fetchAll();

}


//
if($contents_id == ""){
	header('Location: ./contents_list.php');
	exit;	
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
				<div style="display:flex;">
					<div>
						<form action="contents_edit.php" name="frm_contents" method="POST">
							<div class="flex-area">
								<div class="" style="width:100%;" >
									<?php include 'template_detail.php'; ?>
									<button type="button" name="frm_close"class="btn" onClick="window.close();" >閉じる</button>
									<input type="hidden" name="id" value="<?= $data['id'] ?>">
									<input type="submit" name="submit" class="btn btn-success" value="編集" style="margin-left:10px;" >
								</div>
							</div>
						</form>
						<!-- ファイルアップロード -->
						<form action="contents_detail.php" name="fileupload" method="POST" enctype="multipart/form-data">
							<div class="flex-area">
								<div class="" style="width:100%;" >
									<?php $hoge="ファイルアップロード" ?>
									<div class="table" style="margin-bottom: 10px;">
										<div class="tr"><div class="th"><?php echo $hoge ?? '基本情報'; ?></div></div>
										<div class="tr" style="">
											<div class="th">表示タイトル</div>
											<div class="td">
											<input type="text" name="title" value="<?php echo $title; ?>" placeholder="ファイル名を入力">
											<?php if(isset($error['title'])){ ?>
											<p class="error"><?=$error['title']?></p>
											<?php } ?>
											</div>
										</div>
										<div class="tr">
											<div class="th">ファイル</div>
											<div class="td">
												<input type="file" name="file">
												<?php if(isset($error['main_image'])){ ?>
												<p class="error"><?=$error['main_image']?></p>
												<?php } ?>
											</div>
										</div>
										<div class="tr">
											<div class="th">サムネイル</div>
											<div class="td">
												<input type="file" name="thum">
												<?php if(isset($error['thumnail'])){ ?>
												<p class="error"><?=$error['thumnail']?></p>
												<?php } ?>
											</div>
										</div>
										<div class="tr">
											<div class="th">ガード</div>
											<div class="td">
											<select name="guard_flag">
											<option value="0" <?php echo $guard_flag == "0" ? 'selected' : ''; ?>> on
											<option value="1" <?php echo $guard_flag == "1" ? 'selected' : ''; ?>> off 
											</select>
											</div>
										</div>
										
<!-- 										<div class="tr">
											<div class="th">ステータス</div>
											<div class="td">
												<div class="btn-group" data-toggle="buttons">
													<label class="btn btn-default active"><input type="radio" name="status" autocomplete="off" value="0" checked=""> 有効</label>
													<label class="btn btn-default "><input type="radio" name="status" autocomplete="off" value="1"> 無効</label>
												</div>
											</div>
										</div> -->
										<div class="tr">
											<div class="th">操作</div>
											<div class="td">
												<input type="hidden" name="contents_id" value="<?= $data['id'] ?>">
												<input type="submit" name="fileupload" class="btn btn-success" value="追加" style="" >
											</div>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
					<!-- ファイル一覧 -->
					<div class="flex-area" style="width: 100%; margin-left: 30px;">
						<div class="" style="width:100%;" >
							<?php $hoge="ファイル一覧" ?>
							<div class="table" style="margin-bottom: 0px;">
								<div class="tr"><div class="th"><?php echo $hoge ?? '基本情報'; ?></div></div>
								<table class="table table_result_client table_sp" style="margin-bottom: 0px;">
									<thead>
										<tr>
												<th class="listUser table_result_element" style="color: #fff; background: #1abc9c;">タイトル</th>
												<th class="listUser table_result_element" style="color: #fff; background: #1abc9c;">ガード</th>
												<th class="listUser table_result_element" style="color: #fff; background: #1abc9c;">ファイルリンク</th>
												<th class="listUser table_result_element" style="color: #fff; background: #1abc9c;">サムネイルリンク</th>
												<th class="listUser table_result_element" style="color: #fff; background: #1abc9c;">ステータス</th>
												<th class="listUser table_result_element" style="color: #fff; background: #1abc9c;">操作</th>
										</tr>
									</thead>
									<tbody>
									<?php foreach ($file_data as $seq => $value) { ?>
										<form action="contents_detail.php" method="post">
											<tr>
												<!-- <td class="listUser" ><?php //echo h($value['contents_name']); ?></td> -->
												<td class="listUser"><?php echo $value["title"]; ?></td>
												<td class="listUser"> <?php echo  $value['guard_flag'] > 0 ? 'off' : 'on' ?> </td>
												<td class="listUser"><a href="image/contents_folder/<?php echo $value["file_name"]; ?>" target="_blank"><?php echo $value["file_name"]; ?></a></td>
												<td class="listUser">
													<?php if($value['thumbnail_name']=="0" || empty($value['thumbnail_name'])){ ?>
														サムネイルはありません
													<?php } else {  ?>
														<a href="image/contents_folder/<?php echo $value['thumbnail_name']; ?>" target="_blank"><?php echo $value['thumbnail_name']; ?></a>
													<?php } ?>
												</td>
												<td class="listUser">
												<?php $style = $value['status'] == 1 ? 'red' : ''; ?>
												<span style="color:<?=$style?>;"><?php echo $def_status[$value['status']]; ?></span>
												</td>
												<td class="listUser" style="padding:8px 10px" >
													<input type="hidden" name="fileid" value="<?= $value["id"] ?>">
													<input type="hidden" name="contents_id" value="<?= $value["contents_id"] ?>">
													<?php if($value['status']==0){ ?>
														<input type="submit" name="filechange" class="btn btn-success" value="無効化" style="display:inline-block; background:#ccc; border:1px solid #ddd;">
													<?php } else { ?>
															<input type="submit" name="filechange" class="btn btn-success" value="有効化" style="display:inline-block;">
													<?php } ?>
													<input type="submit" name="filechange" class="btn btn-success" value="削除" style="display:inline-block;">
												</td>
											</tr>
										</form>
									<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</section><!-- /.content -->
		</div><!-- /.content-wrapper -->
	</div><!-- ./wrapper -->
	<?php include 'script.php';?>
</body>
</html>
<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

$list = [];
$data = [];

$error = $info = NULL;

$mail_types = JSON_DECODE(SEND_MAIL_TYPE, true);
$mail_is_using_flags = JSON_DECODE(SEND_MAIL_IS_USING, true);
$mail_auto_send_flags = JSON_DECODE(SEND_MAIL_AUTO_FLAG, true);

if( isset($_POST['search']) && !empty($_POST['search']) ) {
	
	$data = $_POST;
	
	$condition = search_params();

	$list = SearchListCommon($dbh, $condition, null, 'SendMail', 'Sm_Seq');
}else if(isset($_POST['change_using_mailcontent']) && !empty($_POST['change_using_mailcontent'])) {
	$data = change_mail_params();
	$result = change_isUsing_mail($dbh, $data);
	if(false == $result) {
		$error = "更新に失敗しました。";
	}else {
		$info = "メールを使用中に変更しました。";
	}

	$condition = search_params();
	$list = SearchListCommon($dbh, $condition, null, 'SendMail', 'Sm_Seq');
}



function search_params() {
	$condition = [];

	$condition['Sm_Subject'] = filter_input(INPUT_POST, 'Sm_Subject');
	$condition['Sm_Content'] = filter_input(INPUT_POST, 'Sm_Content');
	$condition['Sm_IsUsing'] = filter_input(INPUT_POST, 'Sm_IsUsing');
	$condition['Sm_Type'] = filter_input(INPUT_POST, 'Sm_Type');

	foreach($condition as $key => $value) {
		if($value === NULL || $value === "") {
			unset($condition[$key]);
			continue;
		}

		if($key != 'Sm_IsUsing') {
			// LIKE検索するカラム
			$condition[$key] = [
				'column' => $key,
				'value' => trim_into_all($value),
				'type' => PDO::PARAM_STR,
				'method' => ' LIKE'
			];
		}else {
			// 一致検索するカラム (セレクトボックスなど)
			$condition[$key] = [
				'column' => $key,
				'value' => intval($value),
				'type' => PDO::PARAM_INT,
				'method' => '='
			];
		}
	}
	return $condition;
}

function change_mail_params() {
	$data = [];
	$data['Sm_Seq'] = filter_input(INPUT_POST, 'Sm_Seq');
	return $data;
}

function change_isUsing_mail($dbh, $data) {
	try{
		$dbh->beginTransaction();
		// 選択されたメールのタイプを取得
		$sql = "SELECT Sm_Seq, Sm_Type, Sm_IsUsing FROM SendMail WHERE Sm_Seq = :Sm_Seq";
		$db = $dbh->prepare($sql);
		$db->bindValue(':Sm_Seq', $data['Sm_Seq'], PDO::PARAM_INT);
		$db->execute();
		$old = $db->fetch(PDO::FETCH_ASSOC);
		if($old == false) {
			throw new Exception('not found');
		}

		// メールタイプが同じメールを全て未使用に変更
		$sql = "UPDATE SendMail SET
			Sm_IsUsing = 0
		WHERE Sm_Type = :Sm_Type";
		$db = $dbh->prepare($sql);
		$db->bindValue(':Sm_Type', $old['Sm_Type'], PDO::PARAM_INT);
		$db->execute();

		// 指定されたメールだけIsUsing = 1に
		$sql = "UPDATE SendMail SET
			Sm_IsUsing = 1
		WHERE Sm_Seq = :Sm_Seq";
		$db = $dbh->prepare($sql);
		$db->bindValue(':Sm_Seq', $data['Sm_Seq'], PDO::PARAM_INT);
		$db->execute();

		$dbh->commit();
		return true;
	}catch(Exception $e) {
		echo $e->getMessage();exit;
		return false;
	}
}

?>
<html>
	<?php include 'header.php'; ?></head>
	<body class="skin-blue" style="">
		<div class="wrapper">
			<?php include 'main_header.php'; ?>
			<?php include 'side.php';?>
			<!-- Content Wrapper. Contains page content -->
			<div class="content-wrapper">
				<!--<h1><span>一覧</span></h1> -->
				<!-- Content Header (Page header) -->
				<section class="content-header"></section>
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-xs-12">
							<form action="" name="frm_admin_list" method="post">
								<div class="box1">
									<div class="box-body">
										<h2>メール文章一覧</h2>
										<div class="content_position_search">
											<div class="row">
												<div class="col-md-12 search-box">
													<table class="nowrap">
														<tr>
															<th>件名</th>
															<td><input type="text" name="Sm_Subject" value="<?php echo isset($condition['Sm_Subject']) ? $condition['Sm_Subject']['value'] : ''; ?>" placeholder="入力してください" class="form_corpcode" style="width: 200px;"></td>
															
															<th>本文</th>
															<td><input type="text" name="Sm_Content" value="<?php echo isset($condition['Sm_Content']) ? $condition['Sm_Content']['value'] : ''; ?>" placeholder="入力してください" class="form_corpcode" style="width: 200px;"></td>
														</tr>
														<tr>
															<th>メール種別</th>
															<td>
																<select name="Sm_Type">
																	<option value="">全て</option>
																	<?php foreach($mail_types as $key => $value) {
																		$selected =  ( isset($condition['Sm_Type']) && $key == $condition['Sm_Type']['value'] ) ? ' selected ' : '';
																		echo '<option value="' . $key . '"'. $selected . ' >' . $value . '</option>';
																	} ?>
																</select>
															</td>
															<th>状態</th>
															<td>
																<select name="Sm_IsUsing">
																	<option value="">全て</option>
																	<?php foreach($mail_is_using_flags as $key => $value) {
																		$selected =  ( isset($condition['Sm_IsUsing']) && $key == $condition['Sm_IsUsing']['value'] ) ? ' selected ' : '';
																		echo '<option value="' . $key . '"'. $selected . ' >' . $value . '</option>';
																	} ?>
																</select>
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-12" style="margin-top:10px;">
													<input type="submit" name="search" class="btn import_btn large" value="検索">
												</div>
											</div>
										</div>
									</div>
								</div><!-- box1 -->
							</form>
							<div class="">
								<?php if(!empty($error) ){ ?>
									<div class="alert alert-danger mb15"><?php echo htmlspecialchars($error);?></div>
								<?php } else if(!empty($info)) { ?>
									<div class="alert alert-info mb15"><?php echo htmlspecialchars($info);?></div>
								<?php } ?>
								<div class="search_results">
									<div id="" class="wrap_scroll">
										<table class="table table_result_client table_sp">
											<thead>
												<tr>
													<th class="listUser table_result_element">使用中</th>
													<th class="listUser table_result_element">メール種別</th>
													<th class="listUser table_result_element">メール送信処理</th>
													<th class="listUser table_result_element">件名</th>
													<th class="listUser table_result_element">本文</th>
													<th class="listUser table_result_element">詳細</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($list as $Sm_Seq => $mail) { ?>
													<tr>
														<td class="listUser">
															<?php if($mail['Sm_IsUsing'] == 1){
																echo '<span style="background:yellow; padding:2px 5px;">使用中</span>';
															}else{  ?>
																<form action="" method="POST">
																	<input type="submit" name="change_using_mailcontent" value="使用中に変更する">
																	<input type="hidden" name="Sm_Seq" value="<?php echo htmlspecialchars($mail['Sm_Seq']);?>">
																</form>
															<?php } ?>
														</td>
														<td class="listUser"><?php echo h($mail_types[$mail['Sm_Type']] ); ?></td>
														<td class="listUser"><?php echo h($mail_auto_send_flags[$mail['Sm_IsAutoSend']] ); ?></td>
														<td class="listUser"><?php echo h( mb_substr($mail['Sm_Subject'], 0, 20)); ?></td>
														<td class="listUser"><?php echo h( mb_substr($mail['Sm_Content'], 0, 29) . '...'); ?></td>
														<td class="listUser">
															<form action="./send_mail_detail.php" method="POST" target="_blank" style="margin-bottom: 0;">
																<button type="submit" name="Sm_Seq" class="btn" value="<?php echo h($mail['Sm_Seq']); ?>" style="padding:3px 20px">詳細</button>
															</form>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div><!-- col-xs-12 -->
					</div><!-- row -->
				</section><!-- /.content -->
			</div><!-- /.content-wrapper -->

		</div><!-- ./wrapper -->
		<?php include 'script.php';?>
	</body>
</html>

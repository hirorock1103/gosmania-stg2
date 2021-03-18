<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";
// 初期化
$search_flag = false;
$arr_content = array();
$total_rows = 0;
$def_status = [0=>"有効", 1=>"無効"];
// 検索条件
$param = $_POST;
$arr_input = array(
	"contents_name"											=> isset($param['contents_name']) ? $param['contents_name'] : "",
	"status"												=> isset($param['status']) ? $param['status'] : "",
);

//一覧
$sql = "select * from contents order by id asc";
$db = $dbh->prepare($sql);
/* $db->bindValue(':id', $contents_id, PDO::PARAM_INT); */
$db->execute();
$arr_content = $db->fetchAll();

// 検索する？
if (isset($_POST['frm_submit'])) {
	$search_flag = true;
	$arr_content = _search($dbh, $arr_input);
	$total_rows = count($arr_content);
}

/**********************************************/
// 検索情報取得
/**********************************************/
function _search($dbh, $arr_input)
{
	$condition = array();
	if (trim_into_all($arr_input['contents_name']) != "") {
		$condition['contents_name'] = array('placeholder' => 'contents_name', 'value' => trim_into_all($arr_input['contents_name']), 'type' => PDO::PARAM_STR ,'method' => ' LIKE');
		
	}
	if (trim_into_all($arr_input['status']) != "") {
		$condition['status'] = array('placeholder' => 'status', 'value' => trim_into_all($arr_input['status']), 'type' => PDO::PARAM_INT, 'method' => ' =');
	}
	
	$column = array('id', 'contents_name', 'status');
	// $column = array('Ad_Seq', 'contents_name', 'contents_nameKana', 'Ad_Id', 'Ad_Invalid');
	
	return GetListCommon($dbh, $condition, $column, 'contents', 'id');
}
?>

<html>
	<?php include 'header.php'; ?></head>
	<body class="skin-blue">
		<div class="wrapper">
			<?php include 'main_header.php'; ?>
			<?php include 'side.php';?>
			<div class="content-wrapper">
				<section class="content-header"></section>
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-xs-12">
							<form action="contents_list.php" name="frm_contents_list" method="post">
								<!-- <input type="hidden" name="total_data_cnt" value="<?php //echo h($total_data_cnt); ?>" />
								<input type="hidden" name="total_pager_cnt" value="<?php //echo h($total_pager_cnt); ?>" /> -->
								<div class="box1">
									<div class="box-body">
										<h2>コンテンツ一覧 / 検索</h2>
										<div class="content_position_search">
											<div class="row">
												<div class="col-md-12 search-box">
													<table class="nowrap">
														<tr>
															<th>コンテンツ名</th>
															<td><input type="text" name="contents_name" value="<?php echo h($arr_input['contents_name']); ?>" placeholder="入力してください" class="form_corpcode" style="width: 200px;"></td>
															<th>ステータス</th>
															<td>
																<select name="status" class="chosen-select">
																	<option value="">全て</option>
																	<?php foreach ($def_status as $key => $val) { ?>
																		<option value="<?php echo $key; ?>" <?php echo (($arr_input['status'] !== "" && $arr_input['status'] == $key) ? 'selected' : ''); ?>><?php echo $val; ?></option>
																	<?php } ?>
																</select>
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-12" style="margin-top:10px;">
													<input type="submit" name="frm_submit" class="btn import_btn large" value="検索">
												</div>
											</div>
										</div>
									</div>
								</div><!-- box1 -->
							</form>
							<form action="contents_detail.php" name="frm_contents_list" method="post" target="_blank">
								<div class="">
									<h3>検索結果数：<?php echo !empty($total_rows) ? number_format($total_rows) : 0  ;  ?>件</h3>
									<div class="search_results">
										<div id="" class="wrap_scroll">
											<table class="table table_result_client table_sp">
												<thead>
													<tr>
														<th class="listUser table_result_element">コンテンツ名</th>
														<th class="listUser table_result_element">ステータス</th>
														<th class="listUser table_result_element">詳細</th>
													</tr>
												</thead>
												<tbody>
<?php foreach ($arr_content as $seq => $value) { ?>
													<tr>
														<td class="listUser" ><?php echo h($value['contents_name']); ?></td>
														<td class="listUser"><?php echo $def_status[$value['status']]; ?></td>
														<td class="listUser" style="padding:8px 10px" ><button type="submit" name="id" class="btn" value="<?php echo h($value['id']); ?>" style="padding:3px 20px">詳細</button></td>
													</tr>
<?php } ?>										</tbody>
											</table>
										</div>
									</div>
								</div>
							</form>
						</div><!-- col-xs-12 -->
					</div><!-- row -->
				</section><!-- /.content -->
			</div><!-- /.content-wrapper -->

		</div><!-- ./wrapper -->
		<?php include 'script.php';?>
	</body>
</html>

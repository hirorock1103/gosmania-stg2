<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

$search_flag = false;
$arr_admin = array();
$total_rows = 0;

// 検索条件
$param = $_POST;
$arr_input = array(
	"Ad_Name"											=> isset($param['Ad_Name']) ? $param['Ad_Name'] : "",
	"Ad_Id"												=> isset($param['Ad_Id']) ? $param['Ad_Id'] : "",
	"Ad_Invalid"									=> isset($param['Ad_Invalid']) ? $param['Ad_Invalid'] : "",
);
//var_dump($arr_input);


// ■検索？
if (isset($_POST['frm_submit'])) {
	$search_flag = true;
	// 顧客情報取得
	$arr_admin = _search($dbh, $arr_input);
	$total_rows = count($arr_admin);
}
//var_dump($arr_admin);
//echo $total_data_cnt . "<br />";
//echo $total_pager_cnt . "<br />";
//echo $cur_page . "<br />";
//echo $min_page . "<br />";
//echo $max_page . "<br />";

//削除
$delete_message = "";
if(isset($_GET['delete'])){

	$ad_seq = $_GET['delete'];
	if($ad_seq > 0){

		//message
		$delete_message = "管理者を削除しました";
		$sql = "delete from Admin where Ad_Seq = " . $ad_seq;
		$db = $dbh->prepare($sql);
		$db->execute();

	}

}




/**********************************************/
// 顧客情報取得
/**********************************************/
function _search($dbh, $arr_input)
{
	$condition = array();
	
	if (trim_into_all($arr_input['Ad_Name']) != "") {
		$condition['Ad_Name'] = array('placeholder' => 'Ad_Name', 'value' => trim_into_all($arr_input['Ad_Name']), 'type' => PDO::PARAM_STR ,'method' => ' LIKE');
		
	}
	
	if (trim_into_all($arr_input['Ad_Id']) != "") {
		$condition['Ad_Id'] = array('placeholder' => 'Ad_Id', 'value' => trim_into_all($arr_input['Ad_Id']), 'type' => PDO::PARAM_STR ,'method' => ' LIKE');
	}
	
	if (trim_into_all($arr_input['Ad_Invalid']) != "") {
		$condition['Ad_Invalid'] = array('placeholder' => 'Ad_Invalid', 'value' => trim_into_all($arr_input['Ad_Invalid']), 'type' => PDO::PARAM_INT, 'method' => ' =');
	}
	
	$column = array('Ad_Seq', 'Ad_Name', 'Ad_NameKana', 'Ad_Id', 'Ad_Invalid');
	
	return GetListCommon($dbh, $condition, $column, 'Admin', 'Ad_Seq');
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
				<!--<h1><span>一覧</span></h1>-->
				<!-- Content Header (Page header) -->
				<section class="content-header"></section>
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-xs-12">

							<form action="admin_list.php" name="frm_admin_list" method="post">
								<input type="hidden" name="total_data_cnt" value="<?php echo h($total_data_cnt); ?>" />
								<input type="hidden" name="total_pager_cnt" value="<?php echo h($total_pager_cnt); ?>" />

								<div class="box1">
									<div class="box-body">
										<h2>管理者一覧 / 検索</h2>
										<div class="content_position_search">
											<div class="row">
												<div class="col-md-12 search-box">
													<table class="nowrap">
														<tr>
															<th>名前</th>
															<td><input type="text" name="Ad_Name" value="<?php echo h($arr_input['Ad_Name']); ?>" placeholder="入力してください" class="form_corpcode" style="width: 200px;"></td>
															
															<th>ログインID</th>
															<td><input type="text" name="Ad_Id" value="<?php echo h($arr_input['Ad_Id']); ?>" placeholder="入力してください" class="form_corpcode" style="width: 200px;"></td>
															
															<th>ステータス</th>
															<td>
																<select name="Ad_Invalid" class="chosen-select">
																	<option value="">全て</option>
																	<?php foreach ($def_status as $key => $val) { ?>
																	<option value="<?php echo $key; ?>" <?php echo (($arr_input['Ad_Invalid'] !== "" && $arr_input['Ad_Invalid'] == $key) ? 'selected' : ''); ?>><?php echo $val; ?></option>
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
							<?php if($delete_message != ""){ ?>
							<span style="color:red;">削除しました</span>
							<?php } ?>
							<form action="admin_detail.php" name="frm_admin_list" method="post" target="_blank">
								<div class="">
									<h3>検索結果数：<?php echo !empty($total_rows) ? number_format($total_rows) : 0  ;  ?>件</h3>
									<div class="search_results">
										<div id="" class="wrap_scroll">
											<table class="table table_result_client table_sp">
												<thead>
													<tr>
														<th class="listUser table_result_element">名前</th>
														<th class="listUser table_result_element">ログインID</th>
														<th class="listUser table_result_element">ステータス</th>
														<th class="listUser table_result_element">詳細</th>
													</tr>
												</thead>
												<tbody>
<?php foreach ($arr_admin as $ad_seq => $ad) { ?>
													<tr>
														<td class="listUser" ><?php echo h($ad['Ad_Name']); ?></td>
														<td class="listUser"><?php echo h($ad['Ad_Id']); ?></td>
														<td class="listUser"><?php echo $def_status[$ad['Ad_Invalid']]; ?></td>
														<td class="listUser" style="padding:8px 10px" >
														<button type="submit" name="Ad_Seq" class="btn" value="<?php echo h($ad['Ad_Seq']); ?>" style="padding:3px 20px">詳細</button>
														<?php if($_SESSION['gosmania']['login_info']['Ad_Seq'] != $ad['Ad_Seq']){ ?>
														<a class="btn" style="padding:3px 20px; background-color:#eee;" href="admin_list.php?delete=<?php echo h($ad['Ad_Seq']);  ?>" onclick="return confirm('削除した管理者は戻せませんがよろしいでしょうか？')">削除</a>
														<?php } ?>
														</td>
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

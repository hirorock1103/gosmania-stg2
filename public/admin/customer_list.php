<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

$list = [];
$data = [];
$total_rows = 0;

if( isset($_POST) && !empty($_POST) ) {
	
	$data = $_POST;
	
	$condition = [];
	
	if(isset($data['Cs_Id']) && !empty($data['Cs_Id'])){
		$condition['Cs_Id'] = array('column' => 'Cs_Id', 'value' => trim_into_all($data['Cs_Id']), 'type' => PDO::PARAM_STR ,'method' => ' LIKE');
	}
	
	if(isset($data['Cs_Name']) && !empty($data['Cs_Name'])){
		$condition['Cs_Name'] = array('column' => 'Cs_Name', 'value' => trim_into_all($data['Cs_Name']), 'type' => PDO::PARAM_STR ,'method' => ' LIKE');
	}
	
	if(isset($data['Cs_Zip']) && !empty($data['Cs_Zip'])){
		$condition['Cs_Zip'] = array('column' => 'Cs_Zip', 'value' => trim_into_all($data['Cs_Zip']), 'type' => PDO::PARAM_STR ,'method' => ' LIKE');
	}
	//$list = SearchListCommon($dbh, $condition, null, 'Customer', 'Cs_Seq');
	$list = SearchListCommon2($dbh, $condition, null, 'Customer', 'Cs_Seq',null, 100, $total_rows);
	if( isset($data['export']) ) {
		$csv = '会員ID,名前,郵便番号,メール送付フラグ' . PHP_EOL;
		//2行目以降
		foreach($list as $key => $row) {
			foreach($row as $col => $value) {
				if($col == 'Cs_Id' || $col == 'Cs_Name' || $col == 'Cs_Zip' ){
					$csv .= $value.',';
				} else if($col == 'Cs_SendMail') {
					$csv .= ($value == 0 ? '送付不要' : '要送付' ) . ',';
				}
			}
		$csv = rtrim($csv, ',');
		$csv .= PHP_EOL;
		}
		$filename = "Customer";
		header('Content-Type: application/octet-stream; charset=sjis-win');
		header('Content-Disposition: attachment; filename='.$filename.date('YmdHis') . '.csv');
		echo mb_convert_encoding($csv, 'sjis-win', 'UTF-8');
		exit;
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
				<!--<h1><span>一覧</span></h1>-->
				<!-- Content Header (Page header) -->
				<section class="content-header"></section>
				<!-- Main content -->
				<section class="content">
					<div class="row">
						<div class="col-xs-12">
							<form action="" name="frm_admin_list" method="post">
								<div class="box1">
									<div class="box-body">
										<h2>ログイン会員情報データ一覧</h2>
										<div class="content_position_search">
											<div class="row">
												<div class="col-md-12 search-box">
													<table class="nowrap">
														<tr>
															<th>会員ID</th>
															<td><input type="text" name="Cs_Id" value="<?php echo isset($data['Cs_Id']) ? $data['Cs_Id'] : ''; ?>" placeholder="入力してください" class="form_corpcode" style="width: 200px;"></td>
															
															<th>名前</th>
															<td><input type="text" name="Cs_Name" value="<?php echo isset($data['Cs_Name']) ? $data['Cs_Name'] : ''; ?>" placeholder="入力してください" class="form_corpcode" style="width: 200px;"></td>
															
															<th>郵便番号</th>
															<td><input type="text" name="Cs_Zip" value="<?php echo isset($data['Cs_Zip']) ? $data['Cs_Zip'] : ''; ?>" placeholder="入力してください" class="form_corpcode" style="width: 200px;"></td>
														</tr>
													</table>
												</div>
												<div class="col-md-12" style="margin-top:10px;">
													<input type="submit" name="search" class="btn import_btn large" value="検索">
													<input type="submit" name="export" class="btn import_btn large" value="CSV出力" style="margin-left:10px;">
												</div>
											</div>
										</div>
									</div>
								</div><!-- box1 -->
							</form>
							<div class="">
							<h3>検索結果数：<?php echo !empty($total_rows) ? number_format($total_rows) : 0  ;  ?>件</h3>
							<p>※100件のみ表示</p>
								<div class="search_results">
									<div id="" class="wrap_scroll">
										<table class="table table_result_client table_sp">
											<thead>
												<tr>
													<th class="listUser table_result_element">会員ID</th>
													<th class="listUser table_result_element">名前</th>
													<th class="listUser table_result_element">郵便番号</th>
													<th class="listUser table_result_element">有効期限</th>
													<th class="listUser table_result_element">メール送付フラグ</th>
													<th class="listUser table_result_element">作成者</th>
												</tr>
											</thead>
											<tbody>
<?php foreach ($list as $cs_seq => $customer) { ?>
												<tr>
													<td class="listUser" ><?php echo h($customer['Cs_Id']); ?></td>
													<td class="listUser"><?php echo h($customer['Cs_Name']); ?></td>
													<td class="listUser"><?php echo h($customer['Cs_Zip']); ?></td>
													<td class="listUser"><?php echo date( "Y年m月",strtotime($customer['Cs_Timelimit'])).'末日'; ?></td>
													<td class="listUser"><?php echo ($customer['Cs_SendMail'] == 0) ? '送付不要' : '要送付'; ?></td>
													<td class="listUser"><?php echo h($customer['Cs_Creator']); ?></td>
												</tr>
<?php } ?>										</tbody>
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

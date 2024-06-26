<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

//非表示対象年月これより以前の情報は不要
$target_year_month = date("Ym",strtotime("-1 year"));


$list = [];
$data = [];
$includeOutputted = false;
$total_rows = 0;
if( isset($_POST) && !empty($_POST) ) {
	
	$data = $_POST;
	
	$condition = [];
	
	// if(isset($data['At_Date_From']) && !empty($data['At_Date_From'])){
	// 	$condition['At_Date_From'] = ['column' => 'createdate' , 'value' => $data['At_Date_From'], 'type' => PDO::PARAM_STR, 'method' => '>='];
	// }
	
	// if(isset($data['At_Date_To']) && !empty($data['At_Date_To'])){
	// 	$condition['At_Date_To'] = ['column' => 'createdate' , 'value' => $data['At_Date_To'], 'type' => PDO::PARAM_STR, 'method' => '<='];
	// }
	
	// $list = SearchListCommon($dbh, $condition, null, 'PaymentInfo', 'seq');

	$includeOutputted = boolval(filter_input(INPUT_POST, 'includeOutputted'));
	$since = filter_input(INPUT_POST, 'At_Date_From');
	$until = filter_input(INPUT_POST, 'At_Date_To');
	$list = getPaymentInfoRecords($dbh, $includeOutputted, $since, $until);

	$total_rows = count($list);

}

if(isset($data['export'])){
	// トランザクション開始
	//
	try{
		$dbh->beginTransaction();
		// ループして更新
		$sql = "UPDATE PaymentInfo SET
			csv_output_date = NOW(),
			updatedate = NOW()
		WHERE seq = :seq";
		$db = $dbh->prepare($sql);
		foreach($list as $seq => $row) {
			if(empty($row['Cs_Seq'])){continue;}
			$db->bindValue(':seq', $seq, PDO::PARAM_INT);
			$db->execute();
		}
		$dbh->commit();
		// CSV出力
		// 2行目以降
		$csv = "";
		foreach($list as $key => $row) {
			if(empty($row['Cs_Seq'])){continue;}
			$csv .= $row['gmo_id'].",".$row['card_limitdate'];
			$csv .= "\r\n";
		}

		$str = $includeOutputted ? "（出力済）" : "";
		$filename = "payment".$str."_";
		header('Content-Type: application/octet-stream; charset=sjis-win');
		header('Content-Disposition: attachment; filename='.$filename.date('Ymd').'.csv');
		//header('Content-Transfer-Encoding: binary');
		echo mb_convert_encoding($csv, 'sjis-win', 'UTF-8');
		exit();
	}catch(Exception $e){
		var_dump($e->getMessage());
		$dbh->rollback();	
	}

}






?>
<html>
	<?php include 'header.php'; ?>
	<style>
	.rest{
		color:#eac5bb;
	}
	
	.free{
		color:red;
	}
	</style>
	</head>
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
							<form action="" name="frm_assign_list" method="post">
								<div class="box1">
									<div class="box-body">
										<h2>会員支払方法データ一覧</h2>
										<div class="content_position_search">
											<div class="row">
												<div class="col-md-12 search-box">
													<table class="nowrap">
														<tr>
															<th>期間</th>
															<td>
																<span class="nopadding" style="margin:0;"><input type="text" name="At_Date_From" value="<?php echo isset($since) ? $since : ''; ?>" class="datepicker" autocomplete="off" style="width:100px;"></span> ～
																<span class="nopadding" style="margin:0;"><input type="text" name="At_Date_To" value="<?php echo isset($until) ? $until : ''; ?>" class="datepicker" autocomplete="off" style="width:100px;"></span>
															</td>
															<th>出力済データ</th>
															<td>
																<?php $checked = $includeOutputted == true ? "checked" : "";  ?>
																<label><input type="checkbox" name="includeOutputted" value="1" <?php echo $checked;  ?>>含める</label>
															</td>
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
														<th class="listUser table_result_element">初回csv出力日</th>
														<th class="listUser table_result_element">作成日(GMO連携日)</th>
														<!-- <th class="listUser table_result_element"></th> -->
													</tr>
												</thead>
												<tbody>
												<?php foreach ($list as $at_seq => $record) { 
													//if(empty($record['Cs_Name'])){var_dump($record);}
													if( date("Ym",strtotime($record['createdate'])) <= $target_year_month ){continue;}

													  ?>
													<tr>
														<td class="listUser" ><?php echo !empty($record['Cs_Name']) ? htmlspecialchars($record['Cs_Name']) : "会員情報(".$record['gmo_id'].")が存在しないため出力対象外"; ?></td>
														<td class="listUser" ><?php echo htmlspecialchars($record['Cs_Id']); ?></td>
														<td class="listUser" ><?php echo htmlspecialchars($record['csv_output_date']); ?></td>
														<td class="listUser" ><?php echo htmlspecialchars($record['createdate']); ?></td>
														<!-- <td class="listUser" style="padding:8px 10px" ><button type="submit" name="Ad_Seq" class="btn" value="" style="padding:3px 20px">詳細</button></td> -->
													</tr>
												<?php } ?>
												</tbody>
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
		<pre><?php  //var_dump(getPaymentInfoRecords($dbh, true, '2020-06-05')); ?></pre>
		<?php include 'script.php';?>
	<script>
	$(function () {
		$('.datepicker').datepicker({
			altFormat:'yyyy-mm-dd'
		});
	});
	</script>
	</body>
</html>

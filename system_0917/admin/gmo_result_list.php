<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

$list = [];
$data = [];
$total_rows = 0;

//group byで年月を取得する
$query = "select ym from GmoResult group by ym Order by ym DESC";
$db = $dbh->prepare($query);
$db->execute();
$ym_list = $db->fetchAll(PDO::FETCH_ASSOC);


if( isset($_POST) && !empty($_POST) ) {
	
	$data = $_POST;
	
	if(isset($data['ym']) && !empty($data['ym'])){
		$query = "select R.*, C.Cs_Name from GmoResult as R left join Customer as C on R.Cs_Id = C.Cs_Id  where ym = :ym order by Cs_Id ASC ";
		$db = $dbh->prepare($query);
		$db->bindValue(":ym", $data['ym'], PDO::PARAM_INT);
		$db->execute();
		$list = $db->fetchAll(PDO::FETCH_ASSOC);
		$total_rows = count($list);
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
										<h2>取込済結果データ一覧</h2>
										<div class="content_position_search">
											<div class="row">
												<div class="col-md-12 search-box">
													<table class="nowrap">
														<tr>
															<th>年月</th>
															<td>
																<select name="ym">
																	<option value=""> -選択してください- 
																	<?php
																		foreach($ym_list as $k => $row){
																			$selected = "";
																			if(isset($data['ym']) && $data['ym'] == $row['ym']){ $selected = "selected";  }
																			echo "<option value=\"".$row['ym']."\" ".$selected." >".$row['ym'];
																		}
																	?>
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
								<h3>検索結果数：<?php echo !empty($total_rows) ? number_format($total_rows) : 0  ;  ?>件</h3>
								<div class="search_results">
									<div id="" class="wrap_scroll">
										<table class="table table_result_client table_sp">
											<thead>
												<tr>
													<th class="listUser table_result_element">GOSMANIA会員番号</th>
													<th class="listUser table_result_element">名前</th>
													<th class="listUser table_result_element">対象年月</th>
													<th class="listUser table_result_element">結果</th>
												</tr>
											</thead>
											<tbody>
<?php foreach ($list as $cs_seq => $customer) { ?>
												<tr>
													<td class="listUser" ><?php echo h($customer['Cs_Id']); ?></td>
													<td class="listUser" ><?php echo !empty($customer['Cs_Name']) ? h($customer['Cs_Name']): "退会の可能性"; ?></td>
													<td class="listUser"><?php echo h($customer['ym']); ?></td>
													<td class="listUser"><?php echo h($customer['result']); ?></td>
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

<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

$arr_mode = array( 2=>"決済履歴", 1=>"連絡先履歴");
$arr_mailsend_flg = array(0 => "希望しない", 1 => "希望する");
$Cs_Id = "";
$list = [];
$payment_list = [];
$data = [];
$mode = [];
$includeOutputted = false;
$error = array();
if( isset($_POST) && !empty($_POST) ) {

	$Cs_Id = isset($_POST['Cs_Id']) ? $_POST['Cs_Id'] : "";
	$mode = isset($_POST['mode']) ? $_POST['mode'] : array();
	
	if($Cs_Id == ""){
		$error[] = "会員番号は必須です。";
	}
	if(empty($mode) ){
		$error[] = "履歴タイプを選択してください。";
	}
	
	if(empty($error)){
		
		if(in_array( "1" , $mode)){

			//customer
			$query = "select CI.*, C.Cs_Seq from CustomerInfo as CI LEFT JOIN Customer as C on CI.Cs_Id = C.Cs_Id  ";
			if(!empty($Cs_Id)){
				$query .= " where CI.Cs_Id like :Cs_Id  ";
			}
			$query .= "ORDER BY Ci_Seq DESC";
			$db = $dbh->prepare($query);
			if(!empty($Cs_Id)){
				$db->bindValue(':Cs_Id', "%".$Cs_Id."%", PDO::PARAM_STR);
			}
			$db->execute();
			$list = $db->fetchAll(PDO::FETCH_ASSOC);

		}


		if(in_array( "2" , $mode)){

			//payment
			$query = "select PI.*, C.Cs_Seq from PaymentInfo as PI LEFT JOIN Customer as C on PI.gmo_id = C.Cs_Id  ";
			if(!empty($Cs_Id)){
				$query .= " where PI.gmo_id like :Cs_Id  ";
			}
			$query .= "ORDER BY PI.seq DESC";
			$db = $dbh->prepare($query);
			if(!empty($Cs_Id)){
				$db->bindValue(':Cs_Id', "%".$Cs_Id."%", PDO::PARAM_STR);
			}
			$db->execute();
			$payment_list = $db->fetchAll(PDO::FETCH_ASSOC);

		}

	}
	
}



?>
<html>
	<?php include 'header.php'; ?>
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
										<h2>各種変更履歴</h2>
										<div class="content_position_search">
											<div class="row">
												<div class="col-md-12 search-box">
													<table class="nowrap">
														<tr>
															<th>GOSMANIA会員番号</th>
															<td>
																<input type="text" name="Cs_Id" value="<?php echo htmlspecialchars($Cs_Id);  ?>" class=""  style=""> 
															</td>
															<td style="display:flex;">
																<?php foreach($arr_mode as $k => $val){  ?>
																	<?php $checked = in_array($k , $mode) ? "checked" : "";  ?>
																	<input <?php echo $checked;  ?>  type="checkbox" name="mode[]" id="ch_<?php echo $k;  ?>"  value="<?php echo $k;  ?>"> <label style="padding:4px;"   for="ch_<?php echo $k;  ?>"><?php echo $val;  ?></label>
																<?php }  ?>
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
							<?php if(!empty($error)){  ?>
								<ul class="error">
									<?php foreach($error as $err_msg){  ?>
										<li><?php echo $err_msg;  ?>
									<?php }  ?>
								</ul>
							<?php }  ?>
							
							<?php if( in_array("2", $mode)){ ?>
							<form action="admin_detail.php" name="frm_admin_list" method="post" target="_blank">
							<h3>決済履歴</h3>
								<div class="">
									<div class="search_results">
										<div id="" class="wrap_scroll">
											<table class="table table_result_client table_sp">
												<thead>
													<tr>
														<th class="listUser table_result_element">GOSMANIA会員番号</th>
														<th class="listUser table_result_element">有効期限</th>
														<th class="listUser table_result_element">更新日</th>
														<!-- <th class="listUser table_result_element"></th> -->
													</tr>
												</thead>
												<tbody>
												<?php foreach ($payment_list as $at_seq => $record) { ?>
													<?php //$red = ($record['Cs_Seq'] == null) ? "bg-red" : "";  ?>
													<?php $red = ($record['Cs_Seq'] == null) ? "" : "";  ?>
													<?php $comment = ($record['Cs_Seq'] == null) ? "<span class=\"red\">現在の連携データには存在しない会員です</span>" : "";  ?>
													<tr class="<?php echo $red;  ?>">
														<td class="listUser" ><?php echo htmlspecialchars($record['gmo_id']); ?><br><?=$comment?></td>
														<td class="listUser" ><?php echo htmlspecialchars($record['card_limitdate']); ?></td>
														<td class="listUser" ><?php echo htmlspecialchars($record['createdate']); ?></td>
													</tr>
												<?php } ?>
												</tbody>
											</table>
										</div>							
									</div>
								</div>
							</form>
							<?php } ?>
							<?php if( in_array("1", $mode)){ ?>
							<form action="admin_detail.php" name="frm_admin_list" method="post" target="_blank">
							<h3>連絡先履歴</h3>
								<div class="">
									<div class="search_results">
										<div id="" class="wrap_scroll">
											<table class="table table_result_client table_sp">
												<thead>
													<tr>
														<th class="listUser table_result_element">GOSMANIA会員番号</th>
														<th class="listUser table_result_element">メール</th>
														<th class="listUser table_result_element">電話番号</th>
														<th class="listUser table_result_element">連絡希望</th>
														<th class="listUser table_result_element">更新日</th>
														<!-- <th class="listUser table_result_element"></th> -->
													</tr>
												</thead>
												<tbody>
												<?php foreach ($list as $at_seq => $record) { ?>
													<?php //$red = ($record['Cs_Seq'] == null) ? "bg-red" : "";  ?>
													<?php $red = ($record['Cs_Seq'] == null) ? "" : "";  ?>
													<?php $comment = ($record['Cs_Seq'] == null) ? "<span class=\"red\">現在の連携データには存在しない会員です</span>" : "";  ?>
													<tr class="<?php echo $red;  ?>">
														<td class="listUser" ><?php echo htmlspecialchars($record['Cs_Id']); ?><br><?=$comment?></td>
														<td class="listUser" ><?php echo htmlspecialchars($record['Ci_MailAddress']); ?></td>
														<td class="listUser" ><?php echo htmlspecialchars($record['Ci_Phone']); ?></td>
														<td class="listUser" ><?php echo $arr_mailsend_flg[$record['Ci_InformationSend']]; ?></td>
														<td class="listUser" ><?php echo htmlspecialchars($record['Ci_Creatdate']); ?></td>
													</tr>
												<?php } ?>
												</tbody>
											</table>
										</div>							
									</div>
								</div>
							</form>
							<?php } ?>
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

<?php
include_once dirname(__FILE__) . "/../../settings.php";
include_once dirname(__FILE__) . "/../../functions.php";

include_once dirname(__FILE__) . "/../../cron_functions/send_mail_functions.php";


$mail_types = JSON_DECODE(SEND_MAIL_TYPE, true);

$list = [];
$data = [];
$error = [];
$info = '';

if( isset($_POST) && !empty($_POST) ) {
	
	$error = validate();
		$query = params();

	if(empty($error) && !empty($_POST['search']) ){
		// 対象者確認処理へ
		$list = getSendMailTargetUsers($dbh, $query['Sm_Type'] );
	}else if (empty($error) && !empty($_POST['send_mail']) ){
		// メール送信処理へ
		$list = getSendMailTargetUsers($dbh, $query['Sm_Type'] );

		$result = executeSendMailtoTarget($dbh, $query['Sm_Type'], $list);
		if($result == 'SUCCESS') {
			$info = 'メールを送信しました。';
		}else {
			$error[] = '問題が発生しました。';
		}
	}


	if( isset($data['send_mail']) ) {
		
	}
}


function params() {
	$data = [];
	$data['Sm_Type'] = filter_input(INPUT_POST, 'Sm_Type');
	return $data;
}

function validate() {
	// ここの関数はPOST送信された値が正しいかのチェックを先にする
	$error = [];
	// いきなりメール送信するな
	if(filter_input(INPUT_POST, 'send_mail') != NULL && filter_input(INPUT_POST, 'check') == NULL) {
		$error[] = '対象の会員を検索してからメール送信に進んでください。';
	}

	// メール選択しろ
	if(filter_input(INPUT_POST, 'Sm_Type') == '') {
		$error[] = 'メール種別を選択してください。';
	}

	return $error;
}

?>
<html>
	<?php include '../../header.php'; ?>
	<style>
		.alert {
			margin: 0 auto 16px;
		}
	</style>
	</head>
	<body class="skin-blue" style="">
		<div class="wrapper">
			<?php include '../../main_header.php'; ?>
			<?php include '../../side.php';?>
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
										<h2>メール送信対象一覧</h2>
										<div class="content_position_search">
											<div class="row">
												<div class="col-md-12 search-box">
													<table class="nowrap">
														<tr>
															<th>メール種別</th>
															<td>
																<select name="Sm_Type" id="Sm_Type_selector">
																	<option value="">選択してください</option>
																	<?php foreach($mail_types as $type_key => $output) {
																		$selected = ( isset($query['Sm_Type']) && $type_key == $query['Sm_Type'] ) ? ' selected ' : '';
																		echo '<option value="' . $type_key . '"'. $selected . ' >' . $output . '</option>';
																	} ?>
																</select>
															</td>	
													</table>
												</div>
												<div class="col-md-12" style="margin-top:10px;">
													<input type="submit" name="search" class="btn import_btn large" value="検索">
													<?php if(isset($_POST['search']) ) { ?>
														<input type="hidden" name="check" value="true">
														<input type="submit" name="send_mail" id="mail_send_button" class="btn import_btn large" value="メール送信" style="margin-left:10px;">
													<?php } ?>
												</div>
											</div>
										</div>
									</div>
								</div><!-- box1 -->
							</form>
							<div class="">
								<div class="search_results">
									<?php if(!empty($error)) { ?>
										<div class="alert alert-danger">
											<strong>エラー</strong>
											<?php foreach($error as $message) {
												echo '<p>' . $message . '</p>';
											} ?>
										</div>
									<?php } ?>
									<?php if(!empty($info)) { ?>
										<div class="alert alert-info">
												<p><?php echo h($info);?></p>
										</div>
									<?php } ?>
									<div id="" class="wrap_scroll">
										<table class="table table_result_client table_sp">
											<thead>
												<tr>
													<th class="listUser table_result_element" style="width: 10%;">会員ID</th>
													<th class="listUser table_result_element">名前</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($list as $cs_seq => $customer) { ?>
													<tr>
														<td class="listUser" ><?php echo h($customer['Cs_Id']); ?></td>
														<td class="listUser"><?php echo h($customer['Cs_Name']); ?></td>
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
		<?php include '../../script.php'; var_dump($_POST, $_SESSION);?>
	</body>
	<script>
	$(function(){
		$('#Sm_Type_selector').change(function(){
			$('#mail_send_button').hide();
		});
	});
	</script>
</html>

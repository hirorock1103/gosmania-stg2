<?php

/**
 * クレカ有効期限の確認
 * 条件：
 * 1.現在Customerテーブルに存在する
 * 2.メール送信希望フラグが1のもの
 * 3.カードの有効期限残が2ヶ月
 *
 */

include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";
include_once dirname(__FILE__) . "/cron_functions/send_mail_functions.php";

$mail_types = JSON_DECODE(SEND_MAIL_TYPE, true);

$list = [];
$data = [];
$error = [];
$info = '';

$selected_mail_type = 0;

if( isset($_POST) && !empty($_POST) ) {
	
	$error = validate();
		$query = params();
		$selected_mail_type = $query['Sm_Type'];
	if(empty($error) && !empty($_POST['search']) ){
		// 対象者確認処理へ
		$list = getSendMailTargetUsers($dbh, $query['Sm_Type'] );
		//echo "<pre>";
		//var_dump($list);
		//echo "</pre>";
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
	<?php include 'header.php'; ?>
	<style>
		.alert {
			margin: 0 auto 16px;
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
														<?php if($selected_mail_type == 1 || $selected_mail_type == 2 || $selected_mail_type == 3 ){  ?>
														<input type="hidden" name="check" value="true">
														<input type="submit" name="send_mail" onclick="return confirm('メールを送信します。よろしいですか？');"  id="mail_send_button" class="btn import_btn large" value="メール送信" style="margin-left:10px;">
														<?php } ?>
													<?php } ?>
												</div>
												<div class="col-md-12" style="margin-top:10px;">
													<p>---</p>
													<?php if($selected_mail_type == 1){  ?>
													<ul>
														<li> クレカ有効期限の抽出確認
														<li> 条件：
														<li> 1.現在Customerテーブルに存在する会員(アクセスの支払方法がクレカになっている会員のみ)が対象
														<li> 2.メール送信希望フラグが1(送信希望)に設定されている会員
														<li> 3.カードの有効期限が当月のもの
													</ul>	
													<?php }else if($selected_mail_type == 2){  ?>
													<ul>
														<li> ファンクラブ有効期限の抽出確認
														<li> 条件：
														<li> 1.現在Customerテーブルに存在する会員(会員有効期限が2ヶ月を切っている)が対象※翌月期限が切れるもの
														<li> 2.メール送信希望フラグが1(送信希望)に設定されている会員
													</ul>	
													<?php }else if($selected_mail_type == 3){  ?>
													<ul>
														<li> 決済登録完了メール
														<li> 条件：
														<li> 1.GMO決済登録が完了した顧客
														<li> 2.メールアドレスが登録されていてメール送信希望フラグが1(送信希望)に設定されている会員
														<li> 3.当月に連携したデータのみが対象(<?php echo date("Y年m月"); ?>に連携されたデータが対象)
														<li>&nbsp; 
														<li> <span style="color:red;">※必ず有効期限が更新されているか確認してから送信してください</span> 
													</ul>	
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
									<div class="row">
										<div id="" class="col-sm-6 wrap_scroll">
											<table class="table table_result_client table_sp">
												<thead>
													<tr>
													<?php if($selected_mail_type == 1){  ?>
														<th class="listUser table_result_element" style="width: 10%;">会員ID</th>
														<th class="listUser table_result_element">名前</th>
														<th class="listUser table_result_element">メールアドレス</th>
														<th class="listUser table_result_element">カード有効期限</th>
														<th class="listUser table_result_element">残月数</th>
													<?php }else if($selected_mail_type == 2){  ?>
														<th class="listUser table_result_element" style="width: 10%;">会員ID</th>
														<th class="listUser table_result_element">名前</th>
														<th class="listUser table_result_element">メールアドレス</th>
														<th class="listUser table_result_element">会員有効期限</th>
														<th class="listUser table_result_element">残月数</th>
													<?php }else if($selected_mail_type == 3){  ?>
														<th class="listUser table_result_element" style="width: 10%;">会員ID</th>
														<th class="listUser table_result_element">名前</th>
														<th class="listUser table_result_element">メールアドレス</th>
														<th class="listUser table_result_element">GMO年月</th>
														<th class="listUser table_result_element">結果</th>
													<?php } ?>
													</tr>
												</thead>
												<tbody>
													<?php foreach ($list as $cs_seq => $customer) { //var_dump($customer);  ?>
														<tr>
														<?php if($selected_mail_type == 1){  ?>
															<td class="listUser" ><?php echo h($customer['Cs_Id']); ?></td>
															<td class="listUser"><?php echo h($customer['Cs_Name']); ?></td>
															<td class="listUser">
																<?php if(isset($customer['Ci_MailAddress']) && !empty($customer['Ci_MailAddress'])) {
																	echo h($customer['Ci_MailAddress']);
																} else { ?>
																	<span class="text-danger">メールアドレス情報が1件もなかったため取得できませんでした。</span>
																<?php } ?>
															</td>
															<td class="listUser"><?php echo h($customer['card_limitdate']); ?></td>
															<td class="listUser"><?php echo h($customer['card_limitmonth']); ?></td>
														<?php }else if($selected_mail_type == 2){  ?>
															<td class="listUser" ><?php echo h($customer['Cs_Id']); ?></td>
															<td class="listUser"><?php echo h($customer['Cs_Name']); ?></td>
															<td class="listUser">
																<?php if(isset($customer['Ci_MailAddress']) && !empty($customer['Ci_MailAddress'])) {
																	echo h($customer['Ci_MailAddress']);
																} else { ?>
																	<span class="text-danger">メールアドレス情報が1件もなかったため取得できませんでした。</span>
																<?php } ?>
															</td>
															<td class="listUser"><?php echo h(date("Y年m月末日",strtotime($customer['Cs_Timelimit']))); ?></td>
															<td class="listUser"><?php echo h($customer['member_limitmonth']); ?></td>
														<?php }else if($selected_mail_type == 3){  ?>
															<td class="listUser" ><?php echo h($customer['Cs_Id']); ?></td>
															<td class="listUser">
																<?php if(isset($customer['Cs_Name']) && !empty($customer['Cs_Name'])) {
																	echo h($customer['Cs_Name']);
																} else { ?>
																	<span class="text-danger">お名前の登録がありません(退会員の可能性があります)</span>
																<?php } ?>
															</td>
															<td class="listUser">
																<?php if(isset($customer['Ci_MailAddress']) && !empty($customer['Ci_MailAddress'])) {
																	echo h($customer['Ci_MailAddress']);
																} else { ?>
																	<span class="text-danger">メールアドレス情報が1件もなかったため取得できませんでした。</span>
																<?php } ?>
															</td>
															<td class="listUser" ><?php echo h($customer['ym']); ?></td>
															<td class="listUser" ><?php echo h($customer['result']);  ?></td>
														<?php } ?>
														</tr>
													<?php } ?>
												</tbody>
											</table>
										</div>
										<div class="col-sm-6">
											<table class="table table_result_client table_sp">
												<thead>
													<tr><th class="text-danger">開発用本文プレビュー(1件目の中身)</th></tr>
												</thead>
<tr>
<td>
<span style="white-space: pre-wrap; word-break: break-all;">
<?php 
var_dump($array_key_first($list));
if(isset($list[array_key_first($list)])) {
	echo generateMailContent(getSendMailData($dbh, $selected_mail_type), $list[array_key_first($list)]);
} ?>
</span>
</td>
</tr>
										</div>
									</div><?php // .row ?>
								</div>
							</div>
						</div><!-- col-xs-12 -->
					</div><!-- row -->
				</section><!-- /.content -->
			</div><!-- /.content-wrapper -->

		</div><!-- ./wrapper -->
		<?php include 'script.php';?>
	</body>
	<script>
	$(function(){
		$('#sSm_Type_selector').change(function(){
			$('#mail_send_button').hide();
		});
	});
	</script>
</html>

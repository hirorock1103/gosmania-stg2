<?php
include_once dirname(__FILE__) . "/settings.php";
include_once dirname(__FILE__) . "/functions.php";

$one_week_ago = new DateTimeImmutable('-7 day');
$thirty_days_ago = new DateTimeImmutable('-30 day');
$paymentInfo_sammary = [
	'weekly' =>		 count(getPaymentInfoRecords($dbh, true, $one_week_ago->format('Y-m-d'))),
//	'monthly' =>	 count(getPaymentInfoRecords($dbh, true, $thirty_days_ago->format('Y-m-d')))
];

echo "ss";
exit();
$customer_upload_summary = getLatestInsertDateFromCustomer($dbh);

//$payment_info_output_summary = get_latest_csv_outputted_date($dbh);

/**
 * 認証データアップロード履歴を調べる
 * @return [
 * 	'alert'  = true-> 警告,
 * 	'latest_upload_date' = 最終アップロード日 (直近のアップロード)
 * ]
 */
function getLatestInsertDateFromCustomer($dbh) {
	$sql = "SELECT Cs_Createdate FROM Customer ORDER BY Cs_Createdate DESC LIMIT 1";
	$db = $dbh->prepare($sql);
	$db->execute();
	$str_date = $db->fetch(PDO::FETCH_COLUMN);
	$date = new DateTimeImmutable($str_date);
	$now = new DateTimeImmutable();
	if ($date->format('Ymd') != $now->format('Ymd')) {
		$alert = true;
		$latest_upload_date = NULL;
	}else{
		$alert = false;
		$latest_upload_date = $date->format('Y年m月d日');
	}
	return [
		'alert' => $alert,
		'latest_upload_date' => $latest_upload_date,
	];
}
/**
 * CSV連携データ出力履歴を調べる
 * @return [
 * 	'alert' = true-> 警告,
 * 	'csv_output_date' = 最終出力日,
 * 	'message' = アラートに表示する文字列 csv_output_dateによって変動
 * ]
 */
function get_latest_csv_outputted_date($dbh) {
	$sql = "SELECT csv_output_date FROM PaymentInfo ORDER BY csv_output_date DESC LIMIT 1";
	$db = $dbh->prepare($sql);
	$db->execute();
	$db_date = $db->fetch(PDO::FETCH_COLUMN);
	if(NULL == $db_date) {
		$alert = true;
		$csv_output_date_string = '';
		$message = '最終出力日が取得できませんでした。';
	}else {
		$csv_output_date = new DateTimeImmutable($db_date);
		$target = $csv_output_date->add(new DateInterval('P7D'))->setTime(23,59,59);
		if ($target < new DateTime()) {
			$alert = true;
			$message = '最終履歴から８日以上経過しています！';
		}else{
			$alert = false;
			$message = $target->format('Y年m月d日') . 'までにデータを連携してください。';
		}
		$csv_output_date_string = $csv_output_date->format('Y年m月d日');
	}
	return [
		'alert' => $alert,
		'csv_output_date' => $csv_output_date_string,
		'message' => $message
	];
}

?>
<html>
	<?php include 'header.php'; ?>
	<link rel="stylesheet" href="./skeduler/jquery.skeduler.css" type="text/css">
</head>
	<body class="skin-blue" style="">
		<div class="wrapper">
			<?php include 'main_header.php'; ?>
			<?php include 'side.php';?>
			<!-- Content Wrapper. Contains page content -->
			<div class="content-wrapper">
				<!-- Content Header (Page header) -->
				<section class="content-header">
				</section>
				<!-- Main content -->
				<section class="content">
					<div class="flex-area">
						<div class="bg">
							<div class="index_table_title">
								<p>クレカ新規登録・更新件数</p>
							</div>
							<table class="table" style="background-color:#fff;">
								<thead>
									<tr>
										<th>期間</th>
										<th>件数</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="">１週間（直近７日）</td>
										<td class=""><?php echo htmlspecialchars( $paymentInfo_sammary['weekly'] . '件'); ?></td>
									</tr>
									<tr class="">
										<td class="">１ヶ月（直近３０日）</td>
										<td class=""><?php echo htmlspecialchars( $paymentInfo_sammary['monthly'] . '件'); ?></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div><!-- flexarea -->

					<div class="flex-area">
						<div class="bg">
							<div class="index_table_title">
								<p>認証データアップロード履歴</p>
							</div>
							<div class="alert <?php echo ($customer_upload_summary['alert']) ? 'alert-danger' : 'alert-info';?>">
								<strong><?php echo htmlspecialchars(date('Y年m月d日') . '(本日)');?></strong>
								<p><?php if(NULL == $customer_upload_summary['latest_upload_date']){
									echo 'まだアップロードされていません。';
								}else{
									echo '最終アップロード日時 : ' . $customer_upload_summary['latest_upload_date'];
								} ?></p>
							</div>
						</div>
					</div><!-- flexarea -->

				</section><!-- /.content -->
			</div><!-- /.content-wrapper -->
			
		</div><!-- ./wrapper -->

		<pre>
		<?php
		$sql = "SELECT csv_output_date FROM PaymentInfo ORDER BY csv_output_date DESC LIMIT 1";
		$db = $dbh->prepare($sql);
		$db_date = $db->fetch(PDO::FETCH_COLUMN);
		var_dump($db_date);
		?></pre>
		<?php include 'script.php';?>
	</body>
</html>

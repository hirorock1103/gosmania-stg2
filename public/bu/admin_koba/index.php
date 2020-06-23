<?php include_once dirname(__FILE__) . "/settings.php"; ?>
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
								<p>なにかしら</p>
							</div>
							<table class="table" style="background-color:#fff;">
								<thead>
									<tr>
										<th class="">なにか</th>
										<th class="">出したい</th>
										<th class="" style="width:16%">情報があれば</th>
									</tr>
								</thead>
								<tbody>
									<tr class="">
										<td class="">指示を</td>
										<td class="">くだ</td>
										<td class="">さい</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div><!-- flexarea -->
				</section><!-- /.content -->
			</div><!-- /.content-wrapper -->
			
		</div><!-- ./wrapper -->
		<?php include 'script.php';?>
	</body>
</html>

<?php
include_once dirname(__FILE__) . "/settings.php";
// true -> GMO連携データなし
$entry_mode = (find_record_by($dbh, 'PaymentInfo', 'seq', 'gmo_id', $ses['cs_id'], 'desc') === false);

// true -> 顧客情報あり 
$cus_edit_mode = (find_record_by($dbh, 'CustomerInfo', 'Ci_Seq', 'Cs_Id', $ses['cs_id'], 'desc') == true);

?>
<html lang="ja">
<head><?php include_once dirname(__FILE__) . "/head.php"; ?></head>
<body>

<div class="wrap">
	<?php include_once dirname(__FILE__) . "/header.php"; ?>
	<section class="section-list page-news GOSMANIA">

		<div class="block-gosmania2">
			<div id="aplly_kind00" class="app btn sp_none flex-buttons">
					<?php $class = ($entry_mode == false) ? "disable" : "";  ?>
					<a class="btn-sub btn-select <?php echo $class;  ?>" href="./entry.php">
						<i class="fas fa-edit" style="position: absolute; top: 12px; left: 40px;"></i>クレジットカード新規登録はこちら
					</a>

					<?php $class = ($entry_mode == true) ? "disable" : "";  ?>
					<a class="btn-sub btn-select <?php echo $class;  ?>" href="./credit_edit.php" class="btn-sub btn-select">
						<i class="fas fa-sync-alt" style="position: absolute; top: 12px; left: 40px;"></i>クレジットカード更新はこちら
					</a>
					<?php $file_name = ($cus_edit_mode == true) ? "customer_info_edit.php" : "customer_info_form.php";  ?>
					<a class="btn-sub btn-select" href="./<?php echo $file_name;  ?>"  class="btn-sub btn-select">
						<i class="far fa-envelope" style="position: absolute; top: 12px; left: 40px;"></i>お客様情報の登録・更新はこちら
					</a>
			</div>

			<div id="aplly_kind00" class="app btn pc_none flex-buttons">
					<?php $class = ($entry_mode == false) ? "disable" : "";  ?>
					<a name="action" value="send" href="./entry.php" class="btn-sub btn-select <?php echo $class;  ?>"  style="width:90%;">
						<i class="fas fa-edit" style="position: absolute; top: 12px; left: 40px;"></i>クレジットカード<br>新規登録はこちら
					</a>

					<?php $class = ($entry_mode == true) ? "disable" : "";  ?>
					<a href="./credit_edit.php" class="btn-sub btn-select <?php echo $class;  ?>" style="width:90%;">
						<i class="fas fa-sync-alt" style="position: absolute; top: 12px; left: 40px;"></i>クレジットカード<br>更新はこちら
					</a>
					<a href="./customer_info_edit.php" class="btn-sub btn-select" style="width:90%;">
						<i class="far fa-envelope" style="position: absolute; top: 12px; left: 40px;"></i>お客様情報の<br>登録・更新はこちら
					</a>
			</div>

		</div>
		<!--<div class="block-gosmania2--comment"><a class="link-type-1" href="tokutei.php">特定商取引法に関する表記</a></div>-->

	</section>

<footer></footer>


</div><!-- .wrap -->
</body>
</html>

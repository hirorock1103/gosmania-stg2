<?php
include_once dirname(__FILE__) . "/settings.php";
// true -> GMO連携データなし
$entry_mode = (find_record_by($dbh, 'PaymentInfo', 'seq', 'gmo_id', $ses['cs_id'], 'desc') === false);

// true -> 顧客情報あり 
$cus_edit_mode = (find_record_by($dbh, 'CustomerInfo', 'Ci_Seq', 'Cs_Id', $ses['cs_id'], 'desc') == true);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<?php include_once dirname(__FILE__) . "/head.php"; ?>
	<!-- 追加 -->
	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js'></script>
	<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/lity/1.6.6/lity.css' />
	<script src='https://cdnjs.cloudflare.com/ajax/libs/lity/1.6.6/lity.js'></script>
	<script type="text/javascript" src="js/aspct.js" charset="utf-8"></script>
</head>
<body>

<div class="wrap">
	<?php include_once dirname(__FILE__) . "/header.php"; ?>
	<section class="section-list page-news GOSMANIA">
		<div class="block-gosmania2--comment2">
			<span>
				<button
					type="button"
					class="btn-sub select_button"
					<?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? '' : 'disabled');?>
					data-target="user_data"
i                   style="padding-left: 3px;"
				>継続手続き</button>
				<button
					type="button"
					class="btn-sub select_button"
					<?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');?>
					data-target="shopping_link"
					style="display:none;"
				>通信販売</button>
				<button
					type="button"
					class="btn-sub select_button"
					<?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');?>
					data-target="contents"
				>コンテンツ</button>
			</span>
			<a class="link-type-1" style="margin-top: 3px; margin-bottom:5px;" href="riyou.php">継続手続きご利用に関する注意事項</a>
		</div>

		<div
			class="block-gosmania2"
			id="user_data"
			style="display: <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'none' : ''); ?>;"
		>
			<div id="aplly_kind00" class="app btn sp_none flex-buttons">
				<?php $class = ($entry_mode == false) ? "disable" : "";  ?>
				<a class="btn-sub btn-select <?php echo $class;  ?>" href="./entry.php">
					<i class="fas fa-edit" style="position: absolute; left: 40px;"></i>クレジットカード新規登録はこちら
				</a>

				<?php $class = ($entry_mode == true) ? "disable" : "";  ?>
				<a class="btn-sub btn-select <?php echo $class;  ?>" href="./credit_edit.php" class="btn-sub btn-select">
					<i class="fas fa-sync-alt" style="position: absolute; left: 40px;"></i>クレジットカード更新はこちら
				</a>
				<?php $file_name = ($cus_edit_mode == true) ? "customer_info_edit.php" : "customer_info_form.php";  ?>
				<a class="btn-sub btn-select" href="./<?php echo $file_name;  ?>"  class="btn-sub btn-select">
					<i class="far fa-envelope" style="position: absolute; left: 40px;"></i>お客様情報の登録・更新はこちら
				</a>
			</div>

			<div id="aplly_kind00" class="app btn pc_none flex-buttons">
					<?php $class = ($entry_mode == false) ? "disable" : "";  ?>
					<a name="action" value="send" href="./entry.php" class="btn-sub btn-select <?php echo $class;  ?>"  style="width:90%;">
						<i class="fas fa-edit" style="position: absolute; left: 15px;"></i>クレジットカード<br>新規登録はこちら
					</a>

					<?php $class = ($entry_mode == true) ? "disable" : "";  ?>
					<a href="./credit_edit.php" class="btn-sub btn-select <?php echo $class;  ?>" style="width:90%;">
						<i class="fas fa-sync-alt" style="position: absolute; left: 15px;"></i>クレジットカード<br>更新はこちら
					</a>
					<a href="./customer_info_edit.php" class="btn-sub btn-select" style="width:90%;">
						<i class="far fa-envelope" style="position: absolute; left: 15px;"></i>お客様情報の<br>登録・更新はこちら
					</a>
			</div>

		</div>

		<div
			class="block-gosmania2"
			id="shopping_link"
			style="display: <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? '' : 'none'); ?>;"
		>
			<div id="aplly_kind00" class="app btn sp_none flex-buttons">
				<a href="https://store.plusmember.jp/gospellers/gateway/?c=3ea6e56e1297b97c3294b174288f614a" class="btn-sub btn-select">
					<i class="fas fa-shopping-cart" style="position: absolute; left: 35px;"></i>会員限定グッズの購入はこちら
				</a>
			</div>
			<div id="aplly_kind00" class="app btn pc_none flex-buttons">
				<a href="https://store.plusmember.jp/gospellers/gateway/?c=3ea6e56e1297b97c3294b174288f614a" class="btn-sub btn-select" style="width:90%;">
					<i class="fas fa-shopping-cart" style="position: absolute; left: 15px;"></i>会員限定グッズの<br>購入はこちら
				</a>
			</div>
		</div>
<!--<div class="block-gosmania2--comment"><a class="link-type-1" href="tokutei.php">特定商取引法に関する表記</a></div>-->
		<!-- コンテンツ画面 -->
		<div
			class="block-gosmania2"
			id="contents"
			style="display: <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? '' : 'none'); ?>;"
		>
			<?php 
			// DB（コンテンツ情報）
			$con_array = [
				["id"=>1, "contents_name"=>"イベント", "status"=>0 ],
				["id"=>2, "contents_name"=>"全国ツアー2021", "status"=>0 ],
				["id"=>3, "contents_name"=>"ファンの集い", "status"=>0 ],
				["id"=>3, "contents_name"=>"Test1", "status"=>0 ],
				["id"=>3, "contents_name"=>"Test2", "status"=>0 ],
				["id"=>3, "contents_name"=>"Test3", "status"=>0 ],
			];
			// 表示
			echo '<!-- PC -->';
			echo '<div id="aplly_kind00" class="app btn sp_none" style="box-sizing: border-box;">';
			foreach($con_array as $value){
				echo '<button type="button" class="btn-sub btn-select <?php echo $class;?> select_button1" style="margin: 5px 5px;"';
				echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');
				echo 'data-target="file" >';
				echo $value["contents_name"];
				echo '</button>';
			}
			echo '</div>';
			echo '<!-- スマホ -->';
			echo '<div id="aplly_kind00" class="app btn pc_none flex-buttons">';
			foreach($con_array as $value){
				echo '<button type="button" class="btn-sub btn-select <?php echo $class;?> select_button1"';
				echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');
				echo 'data-target="file" >';
				echo $value["contents_name"];
				echo '</button>';
			}
			echo '</div>';
			?>
		</div>


		<!-- ファイル画面 -->
		<div
			class="block-gosmania2"
			id="file"
			style="display: <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? '' : 'none'); ?>;"
		>
		<?php 
			// DB（ファイル情報）→コンテンツIDで紐付けて取得してくる
			$fil_array = [
				["id"=>1, "contents_id"=>"1", "file_name"=>"sample.jpg", "status"=>0 ],
				["id"=>2, "contents_id"=>"1", "file_name"=>"sample.jpg", "status"=>0 ],
				["id"=>3, "contents_id"=>"1", "file_name"=>"sample.jpg", "status"=>0 ],
				["id"=>3, "contents_id"=>"1", "file_name"=>"sample.jpg", "status"=>0 ],
				["id"=>3, "contents_id"=>"1", "file_name"=>"sample.jpg", "status"=>0 ],
			];
		?>
		<?= $con_array[1]["contents_name"] ?>
		<!-- PC表示 -->
			<div id="aplly_kind00" class="app btn sp_none flex-buttons">
				<button type="button" class="btn-sub select_button" <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');?>
					data-target="contents" style="margin:0 0 20px 0;">一覧に戻る</button>
				<div class="imagearea">
					<?php 
						foreach($fil_array as $value){
							echo '<figure>';
							echo '<a href="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" data-lity="data-lity">';
							echo '<div calss="photo">';
							echo '<img src="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" alt="写真" width="220px" height="130px"></div></a>';
							echo '<figcaption>';
							echo $value["file_name"];
							echo '</figcaption></figure>';
						}
					?>
<!-- 					<figure><a href="admin/image/contents_folder/sample.mp4" data-lity="data-lity"><div calss="photo"><video src="admin/image/contents_folder/sample.mp4" width="220px" height="130px" loop autoplay muted></video></div></a><figcaption>sample.mp4</figcaption></figure> -->
					<!-- <figure><a href="admin/image/contents_folder/no1.pdf" data-lity="data-lity"><div calss="photo"><iframe src="admin/image/contents_folder/no1.pdf#page=1&scrollbar=0" width="220px" height="130px" ></iframe ></div></a><figcaption>sample.pdf</figcaption></figure> -->
					<figure><a href="admin/image/contents_folder/no1.pdf" data-lity="data-lity"><div calss="photo"><img src="admin/image/contents_folder/samn.jpg" alt="写真" width="220px" height="130px"></div></a><figcaption>sample.pdf</figcaption></figure>
				</div>
			</div>
			<!-- スマホ表示 -->
			<div id="aplly_kind00" class="app btn pc_none flex-buttons">
				<button type="button" class="btn-sub select_button" <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');?>
					data-target="contents" style="margin:0 0 20px 0;">一覧に戻る</button>
				<div class="imagearea">
					<?php 
						foreach($fil_array as $value){
							echo '<div>';
							echo '<a href="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" data-lity="data-lity">';
							echo '<img src="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" alt="写真" width="220px" height="130px"></a>';
							echo '<figcaption>';
							echo $value["file_name"];
							echo '</figcaption></div>';
						}
					?>
					<div><a href="admin/image/contents_folder/sample.mp4" data-lity="data-lity"><video src="admin/image/contents_folder/sample.mp4" width="220px" height="130px" loop autoplay muted></video></a><figcaption>sample.mp4</figcaption></div>
					<div><a href="admin/image/contents_folder/no1.pdf" data-lity="data-lity"><img src="admin/image/contents_folder/samn.jpg" alt="写真" width="220px" height="130px"></a><figcaption>sample.pdf</figcaption></div>
				</div>
			</div>
		</div>
	</section>

<footer></footer>

</div><!-- .wrap -->
<script src="./js/jquery-3.3.1.min.js" type="text/javascript"></script>
<script>
$(function(){
	$('.select_button').click(function(){
		console.log($(this).text(), $(this).data('target'));
		$('.block-gosmania2').hide();
		$('#' + $(this).data('target')).show();

		$('.select_button').prop('disabled', false);
		$(this).prop('disabled', true);
	});
	$('.select_button1').click(function(){
		console.log($(this).text(), $(this).data('target'));
		$('.block-gosmania2').hide();
		$('#' + $(this).data('target')).show();

		$('.select_button1').prop('disabled', false);
		$(this).prop('disabled', true);
	});
});
</script>
</body>
</html>

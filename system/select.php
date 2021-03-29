<?php
include_once dirname(__FILE__) . "/settings.php";
// true -> GMO連携データなし
$entry_mode = (find_record_by($dbh, 'PaymentInfo', 'seq', 'gmo_id', $ses['cs_id'], 'desc') === false);

// true -> 顧客情報あり 
$cus_edit_mode = (find_record_by($dbh, 'CustomerInfo', 'Ci_Seq', 'Cs_Id', $ses['cs_id'], 'desc') == true);

//image
$sql = "select contentsfile.* from contentsfile
where status = 0 order by id asc";
$db = $dbh->prepare($sql);
$db->execute();
$fil_array = [];
while($row = $db->fetch(PDO::FETCH_ASSOC)){
	$fil_array[] = array(
		'id' => $row['id'],
		'contents_id' => $row['contents_id'],
		'file_name' => $row['file_name'],
		'title' => $row['title'],
		'guard_flag' => $row['guard_flag'],
		'thumbnail_name' => $row['thumbnail_name'],
		'status' => $row['status'],
	);
}


//contents titles
$sql = "select * from contents where status = 0 order by id asc";
$db = $dbh->prepare($sql);
$db->execute();
$con_array = [];
$con_titles = [];
while($row = $db->fetch(PDO::FETCH_ASSOC)){
	$con_array[] = array(
		'id' => $row['id'],
		'contents_name' => $row['contents_name'],
		'status' => $row['status'],
	);
	$con_titles[$row['id']] = $row['contents_name'];
}
$con_titles_json = json_encode($con_titles);


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
			<span class="">
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
					class="btn-sub select_button contentcheack"
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
			// 表示
			echo '<!-- PC -->';
			echo '<div id="aplly_kind00" class="app app2 btn sp_none" style="box-sizing: border-box;">';
			foreach($con_array as $value){
				echo '<button type="button" class="btn-sub btn-select select_button select_button1" style="margin:15px;"';
				echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');
				echo 'data-target="file" ';
				echo 'data-id="'.$value['id'].'" >';
				$contents_name = mb_strimwidth( $value['contents_name'], 0, 44, '…', 'UTF-8' );
				echo $contents_name;
				echo '</button>';
			}
			echo '</div>';
			echo '<!-- スマホ -->';
			echo '<div id="aplly_kind00" class="app app3 btn pc_none flex-buttons">';
			foreach($con_array as $value){
				echo '<button type="button" class="btn-sub btn-select <?php echo $class;?> select_button select_button1 ml-0"';
				echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');
				echo 'data-target="file" ';
				echo 'data-id="'.$value['id'].'" >';
				$contents_name = mb_strimwidth( $value['contents_name'], 0, 30, '…', 'UTF-8' );
				echo $contents_name;
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
		<span id="contents-name"><?= $con_array[1]["contents_name"] ?></span>
		<!-- PC表示 -->
			<div id="aplly_kind00" class="app btn sp_none" style="    padding: 30px 0; padding-bottom: 0;">
				<div class="imagearea">
				<ul class="imgsumul">
					<?php 
						foreach($fil_array as $value){
							echo '<figure class="contents-image target-'.$value['contents_id'].'">';
							echo '<li class="imgsumli">';
							echo '<a class="imgsuma" href="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" data-lity="data-lity">';
							echo '<img class="imgsum" src="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" alt="写真"></a>';
							echo '</li>';
							echo '<figcaption>';
							echo $value["title"];
							echo '</figcaption></figure>';
						}
					?>
<!-- 					<figure><a href="admin/image/contents_folder/sample.mp4" data-lity="data-lity"><div calss="photo"><video src="admin/image/contents_folder/sample.mp4" width="220px" height="130px" loop autoplay muted></video></div></a><figcaption>sample.mp4</figcaption></figure> -->
					<!-- <figure><a href="admin/image/contents_folder/no1.pdf" data-lity="data-lity"><div calss="photo"><iframe src="admin/image/contents_folder/no1.pdf#page=1&scrollbar=0" width="220px" height="130px" ></iframe ></div></a><figcaption>sample.pdf</figcaption></figure> -->
<!-- 					<figure><a href="admin/image/contents_folder/no1.pdf" data-lity="data-lity"><div calss="photo"><img src="admin/image/contents_folder/samn.jpg" alt="写真" width="220px" height="130px"></div></a><figcaption>sample.pdf</figcaption></figure> -->
				</ul>
				</div>
				<button type="button" class="btn-sub select_button" <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');?>
					data-target="contents" style="margin:10px 0px 0px 0;">一覧に戻る</button>
			</div>
			<!-- スマホ表示 -->
			<div id="aplly_kind00" class="app btn pc_none flex-buttons" style="padding: 30px 0;    padding-bottom: 0;">
				<div class="imagearea">
				<ul class="imgsumul">
					<?php 
						foreach($fil_array as $value){

/* 							echo '<div>';
							echo '<a href="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" data-lity="data-lity">';
							echo '<img src="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" alt="写真" width="218px" height="130px"></a>';
							echo '<figcaption>';
							echo $value["title"];
							echo '</figcaption></div>'; */

							echo '<figure class="contents-image target-'.$value['contents_id'].'">';
							echo '<li class="imgsumli">';
							echo '<a class="imgsuma" href="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" data-lity="data-lity">';
							echo '<img class="imgsum" src="admin/image/contents_folder/';
							echo $value["file_name"];
							echo '" alt="写真"></a>';
							echo '</li>';
							echo '<figcaption>';
							echo $value["title"];
							echo '</figcaption></figure>';
						}
					?>
<!-- 					<div><a href="admin/image/contents_folder/sample.mp4" data-lity="data-lity"><video src="admin/image/contents_folder/sample.mp4" width="220px" height="130px" loop autoplay muted></video></a><figcaption>sample.mp4</figcaption></div> -->
<!-- 					<div><a href="admin/image/contents_folder/no1.pdf" data-lity="data-lity"><img src="admin/image/contents_folder/samn.jpg" alt="写真" width="220px" height="130px"></a><figcaption>sample.pdf</figcaption></div> -->
				</ul>
				<button type="button" class="btn-sub select_button" <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');?>
					data-target="contents" style="margin:20px 0 10px 0;     width: 150px;">一覧に戻る</button>
				</div>
			</div>
		</div>
	</section>

<footer></footer>

</div><!-- .wrap -->
<script src="./js/jquery-3.3.1.min.js" type="text/javascript"></script>
<script>

var titles = JSON.parse('<?php echo $con_titles_json; ?>');

$(function(){
	$('.select_button').click(function(){
		console.log($(this).text(), $(this).data('target'));
		$('.block-gosmania2').hide();
		$('#' + $(this).data('target')).show();

		$('.select_button').prop('disabled', false);
		$(this).prop('disabled', true);

		//ファイル開いたとき//一覧で戻ったとき
		// if( $(this).data('target')=="file" || $(this).text()=="一覧に戻る" && $(this).data('target')=="contents"){
		if( $(this).text()=="一覧に戻る" && $(this).data('target')=="contents"){
			let inputElement = document.getElementsByClassName("contentcheack");
			$(inputElement).prop('disabled', true);
		}

	});
	$('.select_button1').click(function(){
		console.log($(this).text(), $(this).data('target'));
		$('.block-gosmania2').hide();
		$('#' + $(this).data('target')).show();

		$('.select_button1').prop('disabled', false);
		$(this).prop('disabled', true);

		var contents_id = $(this).data('id');

		$("#contents-name").text(titles[contents_id]);

		//対象の画像を表示 --その他は非表示
		$(".contents-image").hide();
		$(".target-"+contents_id).show();

	});
});
</script>
</body>
</html>

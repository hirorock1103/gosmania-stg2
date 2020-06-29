<?php
include_once dirname(__FILE__) . "/settings.php";

$status = base64_decode( rawurldecode( filter_input(INPUT_GET, 'status')) );
?>
<html lang="ja">
<head><?php include_once dirname(__FILE__) . "/head.php"; ?></head>
<body>
	<div class="wrap">
	<?php include_once dirname(__FILE__) . "/header.php"; ?>
		<section class="section-list page-news GOSMANIA">
			<!--<img src="./image/gos_logo2.png" style="width: 120px; margin-left:130px;"> -->
				<div class="block-gosmania2">
					<?php if($status === 'credit_update') { // クレジット情報更新 credit_edit.php ?>
						<p class="block-tit-done">クレジットカードの更新が完了いたしました。</p>
						<p class="txt-basic">次回の継続より、更新されたクレジットカードでの自動更新となります。</p>
						<p class="txt-credit--mt40" >※会員有効期限の1ヶ月半前までに登録・更新されていない場合は、<br>翌年度以降のクレジットカード決済となります。</p>
						<p class="txt-credit--mt20" >ご不明な点などございましたら、GOSMANIAまでお問い合わせくださいますようお願いいたします。</p>
						<p class="block-tit-done" style="margin-bottom:0px;">GOSMANIA</p>
						<p class="block-tit-done" style="font-weight: normal;margin-top:0px;">TEL：<a href="tel:03-3479-2958">03-3479-2958</a>(平日16:00～19:00)</p>

					<?php } else if($status === 'cs_info_update') { // 顧客情報更新 customer_info_edit.php ?>
						<p class="block-tit-done">お客様情報の登録・更新が完了いたしました。</p>
						<p class="txt-credit--mt40" >※メールの配信・停止反映までに最大で1ヶ月お時間を頂戴する場合がございます。	</p>
						<p class="txt-credit--mt20" >
					ご不明な点などございましたら、GOSMANIAまでお問い合わせくださいますようお願いいたします。</p>
					<p class="block-tit-done" style="margin-bottom:0px;">GOSMANIA</p>
					<p class="block-tit-done" style="font-weight: normal;margin-top:0px;">TEL：<a href="tel:03-3479-2958">03-3479-2958</a>(平日16:00～19:00)</p>

					<?php } else { // entry.php 登録 ?>
						<p class="block-tit-done">クレジットカードの登録が完了いたしました。</p>
						<p class="txt-basic">次回の継続より、クレジットカード決済での自動更新となります。</p>
						<p class="txt-credit--mt40" >※会員有効期限の1ヶ月半前までに登録・更新されていない場合は、<br>翌年度以降のクレジットカード決済となります。</p>
						<p class="txt-credit--mt20" >
					ご不明な点などございましたら、GOSMANIAまでお問い合わせくださいますようお願いいたします。</p>
						<p class="block-tit-done" style="margin-bottom:0px;">GOSMANIA</p>
						<p class="block-tit-done" style="font-weight: normal;margin-top:0px;">TEL：<a href="tel:03-3479-2958">03-3479-2958</a>(平日16:00～19:00)</p>
					<?php } ?>
				</div>
				<div class="block-gosmania2--comment elm-center app"><a class="link-btn-sub" href="select.php">メニュー画面に戻る</a></div>


		</section>
	<footer>
	</footer>
	</div><!-- .wrap -->
</body>
</html>

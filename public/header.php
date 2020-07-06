<div class="pc">
	<a href="./select.php"><img src="./image/gos_logo2.png" class="img1"></a>
	<img src="./image/gos_logo2.png" class="img4">
	<div class="logout">
		<p class="section-tit credit-name mt-for-credit-name"><?php echo $ses['cs_name']; ?></p>
		<p class="btn" style="margin-top: 10px;"><a href="login.php?logout" class="btn btn-sub btn-logout">ログアウト</a></p>
	</div>
	<p class="section-tit memberlimit"><span class="memberlimit--little-big">会員有効期限&nbsp;:&nbsp;<?php echo $ses['cs_timelimit']; ?></span><br><span>※反映までにお時間を頂戴する場合がございます。</span></p>


</div>
<div class="mobile">
	<div class="mobile-row-1">
		<div class="mobile-logo">
			<a href="./select.php"><img src="./image/gos_logo2.png" class="img4"></a>
		</div>
		<div class="mobile-logout">
			<p class="member-name"><?php echo $ses['cs_name']; ?></p>
			<p class="btn"><a href="login.php?logout" class="btn-sub btn-logout">ログアウト</a></p>
		</div>
	</div>
	<div class="mobile-row-2">
	<span class="limit-txt1">会員有効期限&nbsp;:&nbsp;<?php echo $ses['cs_timelimit']; ?></span><br><span class="limit-txt">※反映までにお時間を頂戴する場合がございます。</span>
	</div>



</div>

<img src="./image/gos_logo2.png" class="img1">
<img src="./image/gos_logo2.png" class="img4">
<div class="logout">
	<p class="section-tit credit-name" style="margin-top:12px;"><?php echo $ses['cs_name']; ?></p>
	<p class="btn " style="margin-top: 10px;"><a href="login.php?logout" class="btn btn-sub btn-logout">ログアウト</a></p>
</div>
<p class="section-tit memberlimit"><span class="memberlimit--little-big">会員有効期限&nbsp;<?php echo $ses['cs_timelimit']; ?></span><br><span>※反映までにお時間を頂戴する場合がございます。</span></p>

<?php
include_once dirname(__FILE__) . "/settings.php";

// GMO定義 --------------------------------------------------
define("GMO_API_TOKEN_AUTH_JS", "https://static.mul-pay.jp/ext/js/token.js");// token取得JS
define("GMO_API_SITE_ID", "mst2000019418");
define("GMO_API_SITE_PASS", "fcbmanzy");
define("GMO_API_TOKEN_AUTH_SHOP_ID", "9200000464142");//暫定でinstimeのshopIDを設定。下に切り替える。


define("GMO_API_MEMBER_SEARCH_URL", "https://p01.mul-pay.jp/payment/SearchMember.idPass");// 会員参照
define("GMO_API_MEMBER_REGIST_URL", "https://p01.mul-pay.jp/payment/SaveMember.idPass");
define("GMO_API_MEMBER_UPDATE_URL", "https://p01.mul-pay.jp/payment/UpdateMember.idPass");
define("GMO_API_CARD_REGIST_URL", "https://p01.mul-pay.jp/payment/SaveCard.idPass");

//------------------------------------------------------------


// 定義
define("PAYMENT_REGIST_LIMIT", 1440);// 24時間


$data = $_POST;

var_dump($data);
exit;



?>
<html lang="ja">
<head><?php include_once dirname(__FILE__) . "/head.php"; ?></head>
<body>
<div class="wrap">
<?php include_once dirname(__FILE__) . "/header.php"; ?>
	<section class="section-list page-news GOSMANIA">
		<p class="credit-tit" style="margin-bottom:40px;">以下必要事項をご入力の上、<span><br></span>登録ボタンを押してください。</p>
		<p class="credit-tit">お客様情報を入力してください</p>
		<form action="confirm.php" method="post" >
		<table class="entry_form" style="margin-bottom:40px;">
			<tbody>
				<tr>
					<th>メールアドレス<span>必須</span></th>
					<td><input type="text" style="border-radius: 3px; padding: 10px;" name="sample" placeholder="例）sample@mail.com"></td>
				</tr>
				<tr>
					<th>メールアドレス(確認)<span>必須</span></th>
					<td><input type="text" style="border-radius: 3px; padding: 10px;" name="sample" placeholder="例）sample@mail.com">　</td>
				</tr>
				<tr>
					<th>メール配信<span>必須</span></th>
					<td><label class="entry_radio"><input type="radio" name="sample" checked> 希望する</label><label class="entry_radio"><input type="radio" name="sample"> 希望しない</label> 
						<span class="float_box">GOSMANIA会員有効期限・クレジットカード有効期限が近くなりましたら、ご案内メールをお送りいたします。<br>
							※配信を希望されない場合でも、重要なお知らせについて配信する場合がございます。</span>
					</td>
				</tr>				<tr>
					<th>連絡がつく電話番号<span>必須</span></th>
					<td><input type="text" style="border-radius: 3px; padding: 10px;" name="sample" placeholder="例）12345678910">　</td>
				</tr>
			</tbody>
		</table>
		<p class="credit-tit">クレジットカード情報を<span><br></span>入力してください。</p>
		<p class="txt-credit">・設定したクレジットカードはゴスマニア年会費にご利用いただけます。<br>
			・以下入力フォームに情報をご入力の上、登録ボタンを押してください。<br>
			※クレジットカード情報は、カード決済代行会社（GMOペイメントゲートウェイ株式会社）で安全に保存されます。</p>
			<table class="entry_form">
				<tbody>
					<tr>
						<th>カード会社<!--<span>必須</span>--></th>
						<td>
							<ul>
								<li>
									<select name="sample" style="width:200px; padding: 10px; border-radius: 3px;">
										<option value="">VISA</option>
										<option value="">Mastercard</option>
										<option value="">JCB</option>
										<option value="">American Express</option>
										<option value="">Diners Club</option>
									</select>
								</li>
							</ul>  
							<span class="float_box">※VISA,Master,JCB,American Express,Dinersがご利用いただけます。</span>
						</td>
					</tr>
					<tr>
						<th>カード番号<!--<span>必須</span>--></th>
						<td><input type="text" style="border-radius: 3px; padding: 10px;" name="sample" placeholder="例）1111222233334444"></td>
					</tr>
					<tr>
						<th>セキュリティコード<!--<span>必須</span>--></th>
						<td>
							<input type="text" style="border-radius: 3px; padding: 10px;" class="width_short float_left" name="sample" placeholder="例）000">
							<span class="float_box">※クレジットカード裏面の署名欄にあるコードの下3桁です。<br>
							American Expressについては表面のクレジットカード番号右上に記載されている4桁です。</span>                                </td>
					</tr>
					<tr>
						<th>有効期限<!--<span>必須</span>--></th>
						<td>
							<ul>
								<li>
									<select name="sample" style="border-radius: 3px; padding: 10px;">
										<option value="">---</option>
										<option value="2020">2020</option>
										<option value="2021">2021</option>
										<option value="2022">2022</option>
										<option value="2023">2023</option>
										<option value="2024">2024</option>
										<option value="2025">2025</option>
										<option value="2026">2026</option>
										<option value="2027">2027</option>
										<option value="2028">2028</option>
										<option value="2029">2029</option>
									</select>年
								</li>
								<li>
									<select name="sample" style="border-radius: 3px; padding: 10px;">
										<option value="">---</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
										<option value="7">7</option>
										<option value="8">8</option>
										<option value="9">9</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
									</select>月
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<th>名義人（ｶﾅ）<!--<span>必須</span>--></th>
						<td>
							ｾｲ　<input type="text" class="width_short" style="border-radius: 3px; padding: 10px;" name="sample" placeholder="例）ﾔﾏﾀﾞ">　
							ﾒｲ　<input type="text" class="width_short" style="border-radius: 3px; padding: 10px;" name="sample" placeholder="例）ﾀﾛｳ">                                </td>
					</tr>
					
				</tbody>
			</table>
		<div id="aplly_kind00" class="app btn">
			<button type="submit" name="action" value="send" class="btn-sub" onclick="location.href='credit_done.html' ">登録</button>
		</div>
	</section>
</form>
<footer>
</footer>
</div><!-- .wrap -->
</body>
</html>


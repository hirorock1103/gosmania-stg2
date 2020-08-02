<?php
// 定数定義の値がJSON
// functions.php の get_defined_array() get_defined_name() を利用
define(
		"VALID_UPLOAD_IMAGES_FILE_EXTENSION",
		'["jpg", "JPG", "jpeg", "JPEG", "gif", "GIF", "png", "PNG"]'
);

// ハイフン
define(
		"HYPHENS",
		'["-", "﹣", "－", "−", "⁻", "₋", "‐", "‑", "‒", "–", "—", "―", "ｰ", "﹘"]'
);

// 性別
define(
		"GENDER",
		'{"1" : "男", "2" : "女"}'
);

// ログイン成否
define(
		"LOGINED",
		'{"1" : "成功", "2" : "失敗"}'
);

// 曜日
define(
		"WEEK_JP",
		'{"0" : "日", "1" : "月", "2" : "火", "3" : "水", "4" : "木", "5" : "金", "6" : "土"}'
);

// 都道府県
define("PREFECTURE", '{"1":"北海道","2":"青森県","3":"岩手県","4":"宮城県","5":"秋田県","6":"山形県","7":"福島県","8":"茨城県","9":"栃木県","10":"群馬県","11":"埼玉県","12":"千葉県","13":"東京都","14":"神奈川県","15":"新潟県","16":"富山県","17":"石川県","18":"福井県","19":"山梨県","20":"長野県","21":"岐阜県","22":"静岡県","23":"愛知県","24":"三重県","25":"滋賀県","26":"京都府","27":"大阪府","28":"兵庫県","29":"奈良県","30":"和歌山県","31":"鳥取県","32":"島根県","33":"岡山県","34":"広島県","35":"山口県","36":"徳島県","37":"香川県","38":"愛媛県","39":"高知県","40":"福岡県","41":"佐賀県","42":"長崎県","43":"熊本県","44":"大分県","45":"宮崎県","46":"鹿児島県","47":"沖縄県"}');

// カード種別
define("CREDITCARD_TYPE", '{"1":"VISA","2":"Mastercard","3":"JCB","4":"American","999":"その他"}');

// 口座種別
define("PAYMENT_ACCOUNT_TYPE", '{"0":"普通","1":"当座"}');

// ページャ
define("PAGER_DISP_NUM",									5);// ページャー表示個数
define("PAGER_DATA_NUM",									100);// １ページあたりのデータ数

// 口座種別
define("DEF_STATUS", '{"0":"有効","1":"無効"}');

// メール種別
define("SEND_MAIL_TYPE", '{"1":"クレジットカード有効期限間近", "2":"会員有効期限間近", "3":"年会費決済完了", "4":"クレカ情報登録完了", "5":"クレカ情報更新完了"}');
// メール使用フラグ
define("SEND_MAIL_IS_USING", '{"0":"未使用", "1":"使用中"}');
// メール自動送信フラグ
define("SEND_MAIL_AUTO_FLAG", '{"0":"手動", "1":"自動"}');
//カードエラー
define(
		"GMO_ERROR_CODE",
		'{"E61010002" : "ご利用できないカードをご利用になった、もしくはカード番号が誤っています。",
		"42G830000" : "有効期限に誤りがあります。再度カードをご確認ください。"
		}'
);

//GMO設定ファイル
// GMO定義 --------------------------------------------------
define("GMO_API_TOKEN_AUTH_JS", "https://static.mul-pay.jp/ext/js/token.js");// token取得JS(本番)
//define("GMO_API_TOKEN_AUTH_JS", "https://pt01.mul-pay.jp/ext/js/token.js");// token取得JS

define("GMO_API_SITE_ID", "mst2000023293");
//define("GMO_API_SITE_ID", "tsite00039116"); //テスト環境
//define("GMO_API_SITE_ID", "tsite00039815"); //テスト環境
//define("GMO_API_SITE_ID", "tsite00039926"); //テスト環境

define("GMO_API_SITE_PASS", "rne26yef");
//define("GMO_API_SITE_PASS", "d8k7xrmz"); //　テスト環境
//define("GMO_API_SITE_PASS", "qkmwt3rv"); //　テスト環境
//define("GMO_API_SITE_PASS", "wgchbxbh"); //　テスト環境

define("GMO_API_TOKEN_AUTH_SHOP_ID", "9200002583315");//本番
//define("GMO_API_TOKEN_AUTH_SHOP_ID", "tshop00044681"); //テスト環境
//define("GMO_API_TOKEN_AUTH_SHOP_ID", "tshop00045544"); //テスト環境
//define("GMO_API_TOKEN_AUTH_SHOP_ID", "tshop00045677"); //テスト環境
define("GMO_API_MEMBER_SEARCH_URL", "https://p01.mul-pay.jp/payment/SearchMember.idPass");// 会員参照
define("GMO_API_MEMBER_REGIST_URL", "https://p01.mul-pay.jp/payment/SaveMember.idPass");
define("GMO_API_MEMBER_UPDATE_URL", "https://p01.mul-pay.jp/payment/UpdateMember.idPass");
define("GMO_API_CARD_REGIST_URL", "https://p01.mul-pay.jp/payment/SaveCard.idPass");
//テスト環境
/*
define("GMO_API_MEMBER_SEARCH_URL", "https://pt01.mul-pay.jp/payment/SearchMember.idPass");// 会員参照
define("GMO_API_MEMBER_REGIST_URL", "https://pt01.mul-pay.jp/payment/SaveMember.idPass");
define("GMO_API_MEMBER_UPDATE_URL", "https://pt01.mul-pay.jp/payment/UpdateMember.idPass");
define("GMO_API_CARD_REGIST_URL", "https://pt01.mul-pay.jp/payment/SaveCard.idPass");
 */
//------------------------------------------------------------
  

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
while ($row = $db->fetch(PDO::FETCH_ASSOC)) {
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
while ($row = $db->fetch(PDO::FETCH_ASSOC)) {
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
    <script type="text/javascript" src="js/lity.js" charset="utf-8"></script>
    <script type="text/javascript" src="js/aspct.js" charset="utf-8"></script>
</head>

<body>

    <div class="wrap">
        <?php include_once dirname(__FILE__) . "/header.php"; ?>
        <section class="section-list page-news GOSMANIA">
            <div class="block-gosmania2--comment2">
                <span class="">
                    <button type="button" class="btn-sub select_button" <?php
                    if ($_SESSION[SESSION_BASE_NAME]['login_info']['from_contents'] === false && $_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true) {
                        echo htmlspecialchars('');
                    } else {
                        echo htmlspecialchars('disabled');
                    }
                    ?> data-target="user_data" style="padding-left: 3px;">継続手続き</button>
                    <button type="button" class="btn-sub select_button" <?php
                    if ($_SESSION[SESSION_BASE_NAME]['login_info']['from_contents'] === true) {
                        echo htmlspecialchars('disabled');
                    } elseif ($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true) {
                        echo htmlspecialchars('disabled');
                    } else {
                        echo htmlspecialchars('');
                    }
                    ?> data-target="shopping_link" style="display:;">通信販売</button>
                    <button type="button" class="btn-sub sp-mb10 select_button contentcheack" <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_contents'] === true ? 'disabled' : ''); ?> data-target="contents">コンテンツ</button>
                </span>
                <a class="link-type-1" style="margin-top: 3px; margin-bottom:5px;" href="riyou.php">継続手続きご利用に関する注意事項</a>
            </div>
            <div class="block-gosmania2" id="user_data" style="display: <?php
            if ($_SESSION[SESSION_BASE_NAME]['login_info']['from_contents'] === false && $_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true) {
                echo htmlspecialchars('none');
            } elseif ($_SESSION[SESSION_BASE_NAME]['login_info']['from_contents'] === true) {
                echo htmlspecialchars('none');
            } else {
                echo htmlspecialchars('');
            }
            ?>;">
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
                    <a class="btn-sub btn-select" href="./<?php echo $file_name;  ?>" class="btn-sub btn-select">
                        <i class="far fa-envelope" style="position: absolute; left: 40px;"></i>お客様情報の登録・更新はこちら
                    </a>
                </div>
                <div id="aplly_kind00" class="app btn pc_none flex-buttons">
                    <?php $class = ($entry_mode == false) ? "disable" : "";  ?>
                    <a name="action" value="send" href="./entry.php" class="btn-sub btn-select <?php echo $class;  ?>" style="width:90%;">
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

            <div class="block-gosmania2" id="shopping_link" style="display: <?php
            // echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_contents'] === true ? 'none' : 'none');
            if ($_SESSION[SESSION_BASE_NAME]['login_info']['from_contents'] === false && $_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true) {
                echo htmlspecialchars('');
            } else {
                echo htmlspecialchars('none');
            }
            ?>;">
                <div id="aplly_kind00" class="app btn sp_none flex-buttons">
                    <a href="https://stg-store.plusmember.jp/gospellers/gateway/?c=4186522a8093ecb320093354404d5a49" class="btn-sub btn-select">
                        <i class="fas fa-shopping-cart" style="position: absolute; left: 35px;"></i>会員限定グッズの購入はこちら
                    </a>
                </div>
                <div id="aplly_kind00" class="app btn pc_none flex-buttons">
                    <a href="https://stg-store.plusmember.jp/gospellers/gateway/?c=4186522a8093ecb320093354404d5a49" class="btn-sub btn-select" style="width:90%;">
                        <i class="fas fa-shopping-cart" style="position: absolute; left: 15px;"></i>会員限定グッズの<br>購入はこちら
                    </a>
                </div>
            </div>
            <!--<div class="block-gosmania2--comment"><a class="link-type-1" href="tokutei.php">特定商取引法に関する表記</a></div>-->
            <!-- コンテンツ画面 -->
            <div class="block-gosmania2" id="contents" style="display: <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_contents'] === true ? '' : 'none'); ?>;">
                <?php
                // 表示
                echo '<!-- PC -->';
                echo '<div id="aplly_kind00" class="app app2 btn sp_none" style="box-sizing: border-box;">';
                foreach ($con_array as $value) {
                    echo '<button type="button" class="btn-sub btn-select select_button select_button1" style="margin:15px;"';
                    echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');
                    echo 'data-target="file" ';
                    echo 'data-id="' . $value['id'] . '" >';
                    $contents_name = mb_strimwidth($value['contents_name'], 0, 44, '…', 'UTF-8');
                    echo $contents_name;
                    echo '</button>';
                }
                echo '</div>';
                echo '<!-- スマホ -->';
                echo '<div id="aplly_kind00" class="app app3 btn pc_none flex-buttons">';
                foreach ($con_array as $value) {
                    echo '<button type="button" class="btn-sub btn-select <?php echo $class;?> select_button select_button1 ml-0"';
                    echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : '');
                    echo 'data-target="file" ';
                    echo 'data-id="' . $value['id'] . '" >';
                    $contents_name = mb_strimwidth($value['contents_name'], 0, 30, '…', 'UTF-8');
                    echo $contents_name;
                    echo '</button>';
                }
                echo '</div>';
                ?>
            </div>

            <!-- ファイル画面 -->
            <div class="block-gosmania2" id="file" style="display: <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'none' : 'none'); ?>;">
                <span id="contents-name"><?= $con_array[1]["contents_name"] ?></span>
                <!-- PC表示 -->
                <div id="aplly_kind00" class="app btn sp_none" style="    padding: 30px 0; padding-bottom: 0;">
                    <div class="imagearea">
                        <ul class="imgsumul">
                            <?php
                            foreach ($fil_array as $value) {
                                echo '<figure class=" contents-image target-' . $value['contents_id'] . '">';
                                echo '<li class="imgsumli">';
                                echo '<a class="imgsuma" href="admin/image/contents_folder/';
                                echo $value["file_name"];
                                echo '" data-lity="data-lity">';
                                $guard = $value['guard_flag'] == 0 ? 'oncontextmenu="return false;"' : '';
                                $event = $value['guard_flag'] == 0 ? 'none' : '';
                                echo '<img class="imgsum" ' . $guard . ' src="admin/image/contents_folder/';
                                echo $value["file_name"];
                                echo '" alt="写真" oncontextmenu="return false;"></a>';
                                echo '</li>';
                                echo '<figcaption>';
                                echo $value["title"];
                                echo '</figcaption></figure>';
                            }
                            ?>
                        </ul>
                    </div>
                    <button type="button" class="btn-sub select_button" <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : ''); ?> data-target="contents" style="margin:10px 0px 0px 0;">一覧に戻る</button>
                </div>
                <!-- スマホ表示 -->
                <div id="aplly_kind00" class="app btn pc_none flex-buttons" style="padding: 30px 0;    padding-bottom: 0;">
                    <div class="imagearea">
                        <ul class="imgsumul">
                            <?php
                            foreach ($fil_array as $value) {
                                echo '<figure class="contents-image target-' . $value['contents_id'] . '">';
                                echo '<li class="imgsumli">';
                                echo '<a class="imgsuma" href="admin/image/contents_folder/';
                                echo $value["file_name"];
                                echo '" data-lity="data-lity">';
                                $guard = $value['guard_flag'] == 0 ? 'oncontextmenu="return false;"' : '';
                                $event = $value['guard_flag'] == 0 ? 'none' : '';
                                echo '<img class="imgsum" ' . $guard . ' style="-webkit-user-select:' . $event . '; -webkit-touch-callout:' . $event . '; pointer-events:' . $event . ';" src="admin/image/contents_folder/';
                                //echo '<img class="imgsum" src="admin/image/contents_folder/';
                                echo $value["file_name"];
                                echo '" alt="写真"></a>';
                                echo '</li>';
                                echo '<figcaption>';
                                echo $value["title"];
                                //ガードがONの場合はダウンロードリンク表示
                                if ($value['guard_flag'] == 1) {
                                    echo '<p><a href="../system/download.php';
                                    echo '?fpath=';
                                    echo $value["file_name"];
                                    echo '">ダウンロード</a></p>';
                                }
                                echo '</figcaption></figure>';
                            }
                            ?>
                        </ul>
                        <button type="button" class="btn-sub select_button" <?php echo htmlspecialchars($_SESSION[SESSION_BASE_NAME]['login_info']['from_shop'] === true ? 'disabled' : ''); ?> data-target="contents" style="margin:20px 0 10px 0; width: 150px;">一覧に戻る</button>
                    </div>
                </div>
            </div>
        </section>

        <footer></footer>

    </div><!-- .wrap -->
    <script src="./js/jquery-3.3.1.min.js" type="text/javascript"></script>
    <script>
        var titles = JSON.parse('<?php echo $con_titles_json; ?>');

        document.getElementsByTagName('html')[0].oncontextmenu = function() {
            return true;
        }

        $(function() {


            $('.select_button').click(function() {
                console.log($(this).text(), $(this).data('target'));
                $('.block-gosmania2').hide();
                $('#' + $(this).data('target')).show();

                $('.select_button').prop('disabled', false);
                $(this).prop('disabled', true);

                //ファイル開いたとき//一覧で戻ったとき
                // if( $(this).data('target')=="file" || $(this).text()=="一覧に戻る" && $(this).data('target')=="contents"){
                if ($(this).text() == "一覧に戻る" && $(this).data('target') == "contents") {
                    let inputElement = document.getElementsByClassName("contentcheack");
                    $(inputElement).prop('disabled', true);
                }

            });
            $('.select_button1').click(function() {
                console.log($(this).text(), $(this).data('target'));
                $('.block-gosmania2').hide();
                $('#' + $(this).data('target')).show();

                $('.select_button1').prop('disabled', false);
                $(this).prop('disabled', true);

                var contents_id = $(this).data('id');

                $("#contents-name").text(titles[contents_id]);

                //対象の画像を表示 --その他は非表示
                $(".contents-image").hide();
                $(".target-" + contents_id).show();

            });
        });
    </script>
</body>

</html>
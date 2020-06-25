<!-- jquery 3.3.1 -->
<script src="./js/jquery-3.3.1.min.js"></script>
<script src="./js/jquery-ui-1.12.1.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script src="./js/jquery-ui-1.12.1_draggable.min.js"></script>

<!-- Cookie -->
<script src="./js/jquery.cookie.js" type="text/javascript"></script>

<!-- Bootstrap 3.3.2 JS -->
<script src="./js/bootstrap.min.js" type="text/javascript"></script>
<script src="./js/bootstrap-datetimepicker.min.js"></script>

<!-- Slimscroll -->
<script src="./js/jquery.slimscroll.min.js" type="text/javascript"></script>

<!-- FastClick -->
<script src="./js/fastclick.min.js"></script>

<!-- AdminLTE App -->
<script src="./js/app.min.js" type="text/javascript"></script>
<script src="./js/jquery-ui-1.10.3.custom.js"></script>

<!-- chartapi -->
<script src="https://www.google.com/jsapi" type="text/javascript"></script>

<!-- datepicker -->
<script src="./js/jquery.ui.datepicker-ja.js"></script>
<!-- timepicker -->
<script src="./js/jquery.timepicker.min.js"></script>

<!-- chosen -->
<script src="./js/chosen.jquery.js"></script>
<script src="./js/init.js" type="text/javascript" charset="utf-8"></script>

<!-- chosen -->
<script src="./js/context-menu.js"></script>

<script src="./js/icheck.min.js"></script>
<script src="./js/common.js"></script>

<script type="text/javascript">
$(function() {
	//デイトピッカー
	$( "#datepicker1").datepicker({ inline: true }).datepicker( $.datepicker.regional[ "ja" ] );
	$( "#datepicker2").datepicker({ inline: true }).datepicker( $.datepicker.regional[ "ja" ] );
	$( ".datepicker").datepicker({ inline: true }).datepicker( $.datepicker.regional[ "ja" ] );
});
</script>
<script>
	//サイドメニューのActive化
	$(document).ready(function() {
		$(".sidebar-menu .treeview-menu a").each(function() {
			var urlAry = {
			}
			var activeUrl = location.pathname.split("/")[location.pathname.split("/").length - 1];
			var href = $(this).attr('href').split('?');
			href = href[0];
			if(activeUrl == href || $.inArray(activeUrl, urlAry[href]) != -1) {
				$(this).parent().addClass("active");
				$(this).closest(".treeview").find("a").eq(0).click();
			}
		});
	});
</script>
<script>
	//ラジオボタン
$(function() {
	$('#pass_edit_check').on('change', function() {
		pass_area_update();
	});
	pass_area_update();
});

// チェックボックスの状態をみてパスワード入力エリアを更新する
function pass_area_update() {
	if ($('#pass_edit_check').prop('checked')) {
		$('#login_pw_input').removeClass('hide_item');
		$('#login_pw_check_tr').removeClass('hide_item');
		$('input[type=password]').val('');
	} else {
		$('#login_pw_input').addClass('hide_item');
		$('#login_pw_check_tr').addClass('hide_item');
		//$('input[type=password]').val('*');
	}
}
</script>

<script type="text/javascript">
	$(".chosen-select").chosen({width: "300px"});
</script>

<script type="text/javascript">
function win_open(wURL,wName,wOption) {
	window.open(wURL,wName,wOption);
}
</script>

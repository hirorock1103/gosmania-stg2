// グローバル変数
var _def_service = new Object();
var _limit_date = new Object();

$(function(){


/***************************************/
// 起動時処理
/***************************************/

	//alert("schedule.js");

	// phpデータ取得
	_limit_date = $("#php_limit_date").html();
	_def_service = $.parseJSON($("#php_def_service").html());






/***************************************/
// Event: 契約検索　クリック
/***************************************/
$("#contract_search").on('click', function() {
	// Loading
	//$("#modal_edit_loading").hide();
	
	// 既に選択済みの場合、確認アラート
	var cn_seq = $("input[name='Cn_Seq']").val();
	if (cn_seq != "" && cn_seq != "0") {
		if (!confirm("契約プランを変更すると入力内容がリセットされますが、\nよろしいですか？")) {
			return false;
		}
	}
	
	// 検索モーダル表示
	$("#iframe-modal").prop("src", "contract_search.php");
	$("#modal").addClass("modal-active");
	$("#bg").addClass("bg-active");
	
	return false;
});


/***************************************/
// Event: 作業時間　変更
/***************************************/
$("select[name='Sc_WorkHours']").on('change', function() {
	before	= $("input[name='Sc_WorkHours_Before']").val();
	after		= $("select[name='Sc_WorkHours']").val();
	return _staff_list_clear_alert("Sc_WorkHours", "作業時間", before, after);
});


/***************************************/
// Event: 訪問日　変更
/***************************************/
$("input[name='Sc_Date']").on('change', function() {
	before	= $("input[name='Sc_Date_Before']").val();
	after		= $("input[name='Sc_Date']").val();
	return _staff_list_clear_alert("Sc_Date", "訪問日", before, after);
});


/***************************************/
// Event: 訪問時間　変更
/***************************************/
$("select[name='Sc_Startdate_H']").on('change', function() {
	before	= $("input[name='Sc_Startdate_H_Before']").val();
	after		= $("select[name='Sc_Startdate_H']").val();
	return _staff_list_clear_alert("Sc_Startdate_H", "訪問時間", before, after);
});
$("select[name='Sc_Startdate_M']").on('change', function() {
	before	= $("input[name='Sc_Startdate_M_Before']").val();
	after		= $("select[name='Sc_Startdate_M']").val();
	return _staff_list_clear_alert("Sc_Startdate_M", "訪問時間", before, after);
});
$("select[name='Sc_Enddate_H']").on('change', function() {
	before	= $("input[name='Sc_Enddate_H_Before']").val();
	after		= $("select[name='Sc_Enddate_H']").val();
	return _staff_list_clear_alert("Sc_Enddate_H", "訪問時間", before, after);
});
$("select[name='Sc_Enddate_M']").on('change', function() {
	before	= $("input[name='Sc_Enddate_M_Before']").val();
	after		= $("select[name='Sc_Enddate_M']").val();
	return _staff_list_clear_alert("Sc_Enddate_M", "訪問時間", before, after);
});


/***************************************/
// Event: サービス　変更
/***************************************/
$(".service_list").on('ifChanged', function(e) {
	var col = $(this).prop('name');
	var before = false;
	if ($("input[name='" + col + "_Before']").val() == "1") {
		before = true;
	}
	var after = $(this).prop('checked');
	return _staff_list_clear_alert("service_list-" + col, "サービス内容", before, after);
});


/***************************************/
// Event: スタッフ検索（マッチング）　クリック
/***************************************/
$("#staff_search").on('click', function() {
	// Loading
	//$("#modal_edit_loading").hide();
	
	var bl_status = $("input[name='Bl_Status']").val();
	var py_status = $("input[name='Py_Status']").val();
	var type = "select";
	if (bl_status != "0" || py_status != "0") {// 請求ステータスが確定以上、または支払ステータスが確定以上の場合、select要素は存在せずhidden定義されている項目用。
		type = 'input';
	}
	
	// マッチング画面を表示するための前提条件を取得
	var sc_seq				= $("input[name='Sc_Seq']").val();
	var cn_seq				= $("input[name='Cn_Seq']").val();
	var sc_date				= $("input[name='Sc_Date']").val();
	var sc_workhours	= $(type + "[name='Sc_WorkHours']").val();
	var sc_startdate	= $(type + "[name='Sc_Startdate_H']").val() + ":" + $(type + "[name='Sc_Startdate_M']").val();
	var sc_enddate		= $(type + "[name='Sc_Enddate_H']").val() + ":" + $(type + "[name='Sc_Enddate_M']").val();
	var staff_list		= $("input[name='staff_list']").val();
	var service_list	= [];
	for (col in _def_service) {
		// 支払いレコードが未確定の場合のみ、サービス選択可能なためcheckboxで定義されている
		var checked = false;
		if (py_status == "0") {
			checked = $("input[name='Sc_" + col + "']").prop('checked');
		} else if ($("input[name='Sc_" + col + "']").length > 0 && $("input[name='Sc_" + col + "']").val() != "0") {
			checked = true;
		}
		if (checked == true) {
			service_list.push(col);
		}
	}
	
	// 前提条件チェック
	var msg = "";
	if (isNaN(cn_seq) || !cn_seq || cn_seq == 0) {
		msg += "契約プランを選択してください。\n";
	}
	if (isNaN(sc_workhours) || !sc_workhours) {
		msg += "作業時間を選択してください。\n";
	}
	if (!sc_date || !sc_startdate || !sc_enddate) {
		msg += "訪問予定日を入力してください。\n";
	} else {
		var check_sc_date = new Date(sc_date);
		var limit_date		= new Date(_limit_date);
		if (check_sc_date >= limit_date) {
			msg += "訪問予定日は翌月末日まで選択可能です。\n";
		}
	}
	if (service_list.length == 0) {
//		msg += "サービス内容を選択してください。\n";
	}
	if (msg) {
		alert(msg);
		return false;
	}
	
	// 初期パラメータ
	var param = "&sc_seq=" + sc_seq + "&cn_seq=" + cn_seq + "&sc_workhours=" + sc_workhours + "&sc_date=" + sc_date + "&sc_startdate=" + sc_startdate + "&sc_enddate=" + sc_enddate + "&service_list=" + JSON.stringify(service_list) + "&staff_list=" + staff_list;
	//alert(param);return ;
	
	// 検索モーダル表示
	$("#iframe-modal").prop("src", "staff_search.php?type=new" + param);
	$("#modal").addClass("modal-active");
	$("#bg").addClass("bg-active");
	
	return false;
});


/***************************************/
// Event: モーダルフォーム　CLOSE
/***************************************/
$(".close-modal").on('click', function() {
	// CLOSE
	$("#modal").removeClass("modal-active");
	$("#bg").removeClass("bg-active");
	
	return true;
});


/***************************************/
// Event: 編集モーダルフォーム　SUBMIT
/***************************************/
$("#modal_edit_submit").on('click', function() {
	// Loading
	$(this).hide();
	$("#modal_edit_loading").show();
	
	// CheckboxのDisabled対策として、現在の値をhiddenにセット
	$(".staff_schedule_timezone").each(function(i) {
		var name = $(this).attr("name").replace('_Input', '');
		var val  = 0;
		if ($(this).prop('checked')) {
			val = 1;
		}
		
		$("input[name='" + name + "']").val(val);
	});
	
	// Closeせずこのままformをサブミット
	//$("#modal_bg").removeClass("bg-show");
	//$("#modal_edit").removeClass("show");
	
	$("form[name='frm_modal_edit']").submit();
	
	return true;
});


/***************************************/
// Event: 完了モーダルフォーム　CLOSE
/***************************************/
$(".modal_complete_close").on('click', function() {
	// CLOSE
	$("#modal_bg").removeClass("bg-show");
	$("#modal_complete").removeClass("show");
	
	return true;
});


/***************************************/
// Event: 削除フラグON時の確認ダイアログ
/***************************************/
$("#invalid_check").on('click', function() {
	// 登録ステータス取得
	var invalid = $("input[name='Sc_Invalid']").val();
	if (invalid == "1") {
		if (!confirm("このスケジュールを無効にしますがよろしいですか？\n再表示するにはシステム管理者による変更が必要になります。")) {
			return false;
		}
	}
	
	return true;
});







});


/*-------------------------------------*/
/*-------------------------------------*/
// FUNCTION
/*-------------------------------------*/
/*-------------------------------------*/
function _staff_list_clear_alert(col, item, before, after)
{
	if (before == after) {
		return true;
	}
	
	// スタッフ選択済みの場合、スタッフリセット確認アラート
	var staff_list = JSON.parse($("input[name='staff_list']").val());
	var valid_staff_cnt = 0;
	
	for(st_seq in staff_list) {
		if ($("input[name='staff_list_del[" + st_seq + "]']").prop('checked') == false) {
			valid_staff_cnt++;
		}
	}
	//alert(valid_staff_cnt);
	
	// 有効スタッフが存在したらアラート
	if (valid_staff_cnt > 0) {
		if (confirm(item + "を変更する場合、\n現在選択中のスタッフリストがリセットされますが、\nよろしいですか？")) {
			// はいを選択した場合、スタッフリストをクリア
			$("input[name='staff_list']").val("[]");// JSON形式
			$(".tbl_staff_list").remove();
			$(".staff_list_err").empty();
			
		} else {
			// 元の値に戻す
			if (col == 'Sc_Date') {
				$("input[name='" + col + "']").val(before);
			} else if (col.indexOf('service_list-') === 0) {
				col = col.replace("service_list-", "");
				$("input[name='" + col + "']").prop('checked', before);
				setTimeout(function(col) { $("input[name='" + col + "']").iCheck('update'); }, 0, col);
			} else {
				$("select[name='" + col + "']").val(before);
			}
			return false;
		}
		//alert(item + "を変更する場合、既に選択済みのスタッフ勤務スケジュールに矛盾が発生しないかをご確認ください。");
	}
	
	return true;
}




/*-------------------------------------*/
/*-------------------------------------*/
// オブジェクト定義
/*-------------------------------------*/
/*-------------------------------------*/


var _def_task_type_schedule = new Object();
var _arr_schedule = new Object();

$(function(){
/***************************************/
// 起動時処理
/***************************************/

	// phpデータ取得
	_def_task_type_schedule = $.parseJSON($("#php_def_task_type_schedule").html());
	_arr_schedule = $.parseJSON($("#php_arr_schedule").html());




/***************************************/
// Event: 契約検索　クリック
/***************************************/
$("#customer_search").on('click', function() {

	// 検索モーダル表示
	$("#iframe-modal").prop("src", "customer_search_task.php");
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
// Event: タスク種別変更
/***************************************/
$("select[name='Tk_Type']").on('change', function() {
	var type = $(this).val();
	
	// 訪問前詳細連絡の場合、Trialチェックボックスを表示する
	if (type == _def_task_type_schedule) {
		$(".task_col_Tk_Trial_Check_Money").show();
		$(".task_col_Tk_Trial_Check_Item").show();
		$(".task_col_Tk_Trial_Check_Cancel").show();
		$(".task_col_Sc_Seq").show();
		$(".task_col_Sc_Memo").show();
	} else {
		$(".task_col_Tk_Trial_Check_Money").hide();
		$(".task_col_Tk_Trial_Check_Item").hide();
		$(".task_col_Tk_Trial_Check_Cancel").hide();
		$(".task_col_Sc_Seq").hide();
		$(".task_col_Sc_Memo").hide();
	}
	
	return true;
});


/***************************************/
// Event: タスクSEQ変更
/***************************************/
$("select[name='Sc_Seq']").on('change', function() {
	var sc_seq = $(this).val();
	
	// スケジュール備考の内容を変更する
	$(".task_col_Sc_Memo textarea").val(_arr_schedule[sc_seq].Sc_Memo);
	
	return true;
});






});



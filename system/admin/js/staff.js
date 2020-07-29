// グローバル変数
//var _arr_estimate = new Object();
$(function(){


/***************************************/
// 起動時処理
/***************************************/

	//alert("staff.js");
	
	// phpデータ取得
	//_arr_estimate = $.parseJSON($("#php_arr_estimate").html());
	
	
	
/***************************************/
// Event:対象チェック
/***************************************/
$(document).on('change','.station_line',function(){
	var line_cd = $(this).val();
	var num = $(this).prop('name').replace("St_LineCd", "");

	// 選択ありなら駅リスト更新
	if (line_cd != "") {
		msg = new Object();
		msg.mode			= "SELECT_STATION_LIST";
		msg.line_cd		= line_cd;
		msg.num				= num;
		fncStaffAjax(msg);
	} else {
		$(this).closest("div").find(".station").empty();
	}
	
	return ;
});


/***************************************/
// Event: 追加ボタン
/***************************************/
$(document).on('click','.add',function(){
	// 行数取得
	var len = $("#staff_movingtime").length;
	
	// コピー元DOM取得
	var html = $("#staff_movingtime_record").html().replace("chosen-select_num", "chosen-select_" + len);
	$("#staff_movingtime").append(html);
	
	$(".chosen-select_" + len).chosen({});
	
	return false;
});


/***************************************/
// Event: 削除ボタン
/***************************************/
$(document).on('click','.remove',function(){
	$(this).closest("div.tr").remove();
});





});


/*-------------------------------------*/
/*-------------------------------------*/
// FUNCTION
/*-------------------------------------*/
/*-------------------------------------*/
/***************************************/
// FUNCTION: 
/***************************************/
function fncStaffAjax(msg)
{
	dispLoading("処理中...");
	
	$.ajax({
		url: "ajax.php",
		type: "POST",
		data: msg,
	}).done(function(data, status, xhr) {
		try {
			var res = $.parseJSON( data );
		} catch( e ){
			var msg = "[js no catch]\n通信エラーが発生しました。";
			alert(msg);
			//console.log(data);
			//console.log(e);
			//location.href = "./estimate_list.php";
			return;
		}
		
		// 値を更新
		if (res['mode'] == "SELECT_STATION_LIST") {
			// Option
			var html = "";
			var cnt = 0;
			for (key in res["station_list"]) {
				html += '<option value="' + key + '">' + res["station_list"][key]['Sta_Name'] + '</option>';
				cnt++;
			}
			html = '<option value="">選択してください (' + cnt + '駅)</option>' + html;
			$('select[name="St_StaCd' + res['num'] + '"]').empty().append(html);
			//$('select[name="St_Station' + res['num'] + '"]').chosen();
		}
		
		// ローダー消去
		//$('.modalform_loading_area').hide();
		
		
	}).fail(function(xhr, status, error) {
		//alert('error!!!');
			var msg = "[js fail]\n通信エラーが発生しました。";
			alert(msg);
			//location.href = "./estimate_list.php";
			return;
			
			
	}).always( function(data) {
			removeLoading();// Lading 画像を消す
	});
	
}


/***************************************/
// FUNCTION: ローディング
/***************************************/
function dispLoading(msg){
	var dispMsg = "<div class='loadingMsg'>" + msg + "</div>";
	// ローディング画像が表示されていない場合のみ出力
	if($("#loading").length == 0){
		$("body").append("<div id='loading'>" + dispMsg + "</div>");
	}
	return ;
}
function removeLoading(){
	$("#loading").remove();
}




/*
function display_template_name(msg){
	$("input[name='" + _template_name + "']").val(msg).prop('title', msg);
}
*/






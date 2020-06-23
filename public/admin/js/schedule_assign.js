var _def_task_type_schedule = new Object();
var _arr_sch		= new Object();
var _arr_shop		= new Object();
var _arr_staff	= new Object();
var _CAL_START_D;
var _CAL_END_D;
var _CAL_START_D;
var _SELECT_WEEK_NUM;
var STAFF_ROLE_LEADER;
var WORK_STATUS_OFF;
var _drag_st_seq = 0;
var _drop_sc_seq = 0;

$(function(){


/***************************************/
// 起動時処理
/***************************************/

	//alert("schedule_mapping.js");

	
	// データ取得
	_arr_sch					= $.parseJSON($("#php_arr_sch").html());
	_arr_shop					= $.parseJSON($("#php_arr_shop").html());
	_arr_staff				= $.parseJSON($("#php_arr_staff").html());
	_CAL_START_D			= $.parseJSON($("#php_CAL_START_D").html());
	_CAL_END_D				= $.parseJSON($("#php_CAL_END_D").html());
	_SELECT_WEEK_NUM	= $.parseJSON($("#php_SELECT_WEEK_NUM").html());
	STAFF_ROLE_NONE		= $.parseJSON($("#php_STAFF_ROLE_NONE").html());
	STAFF_ROLE_LEADER	= $.parseJSON($("#php_STAFF_ROLE_LEADER").html());
	WORK_STATUS_OFF		= $.parseJSON($("#php_WORK_STATUS_OFF").html());
	
	// _arr_sch.staff_listの空配列をObject型に変換する
	for (key in _arr_sch) {
		if (Object.keys(_arr_sch[key].staff_list).length == 0) {
			_arr_sch[key].staff_list = new Object();
		}
	}
	
	// 右クリックメニュ設定
	$("#tbl_schedule a.catch-item").each(function() {
		//console.log(i + " " + v + " " + $(this).html());
		var name = $(this).prop('name');// assign[sc_seq][st_seq]
		var param = $(this).prop('name').replace('assign', '').split('][');
		var sc_seq = param[0].replace('[', '');
		var st_seq = param[1].replace(']', '');
		fncContextMenu($(this), sc_seq, st_seq);
	});
	
	// スタッフリストをドラッグ可能に
	$(".staff_draggable").draggable({
		helper: 'clone',
		opacity: 0.7,
		start: function(event, ui) {
			f_dragstart(event);
		},
		stop: function(event, ui) {
			f_dragend(event);
		}
	});
	
	// カレンダー日付枠（スケジュール枠）をドロップ可能に
	$(".sch_calender").droppable({
		accept :".staff_draggable" ,
		tolerance: "pointer", 
		over: function(event, ui) {
			f_dragenter(event);
		},
		out: function(event, ui) {
			f_dragleave(event);
		},
		drop: function(event, ui){
			f_drop(event);
		}
	});


/***************************************/
// Event: スタッフ索引　クリック
/***************************************/
$(document).on('click', ".button-kana", function() {
	// 選択行番号を取得（１～１０）
	var kana_category = $(this).val();
	
	// ON/OFF判定
	var status = "ON";
	if ($(this).hasClass("button-this-kana")) {
		status = "OFF";
	}
	
	if (status == "ON") {
		// 選択行のみ表示
		$.each(_arr_staff, function(st_seq, v) {
			var list = $("#staff_list_" + st_seq);
			if (v.kana_category == kana_category) {
				list.show();
			} else {
				list.hide();
			}
		});
	} else {
		$(".staff_name_list").show();
	}
	
	// 索引ボタンのON/OFF設定
	$("#tbl_staff_name_index button").removeClass("button-this-kana");
	if (status == "ON") {
		$(this).addClass("button-this-kana");
	}
	
	// FORMパラメータ変更
	$('input[name="sel_kana"]').val(kana_category);
	
	
	return false;
});


/***************************************/
// Event: スケジュール期間　クリック
/***************************************/
$(document).on('change', "select[name='sel_date']", function() {
	var span = $(this).val().split('_');
	
	// カレンダー日付更新
	var dt = new Date(span[0]);
	$('.schedule_d').each(function(i) {
		$(this).html(dt.getDate());
		dt.setDate(dt.getDate() + 1);
	});
	
	// カレンダースケジュール表示
	$(".sch_calender").hide();// 全非表示
	var dt = new Date(span[0]);
	for ($i = 0; $i < 7; $i++) {
		var d = _format_date(dt);
		$(".sch_date_" + d).show();
		dt.setDate(dt.getDate() + 1);
		//console.log(d);
	}
	
	
	return true;
});









});


/*-------------------------------------*/
/*-------------------------------------*/
// FUNCTION
/*-------------------------------------*/
/*-------------------------------------*/
/***************************************/
// Function: コンテキストメニュ
/***************************************/
function fncContextMenu(element, sc_seq, st_seq)
{
	var option = [];
	
	// リーダー設定
	var txt = "リーダーに設定";
	var role = STAFF_ROLE_LEADER;
	$.each(_arr_sch[sc_seq].staff_list, function(i, val) { 
		if (i == st_seq && val.Sa_Role == STAFF_ROLE_LEADER) {
			txt = "リーダーを解除";
			role = STAFF_ROLE_NONE;
			return;
		}
	});
	option.push({
		text		: txt,
		action	: function() {
			fncMenuLeader(sc_seq, st_seq, txt, role);
		}
	});

	// キャンセル設定
	var dt = new Date(_arr_sch[sc_seq].Sc_Date);
	var cancel_msg = _arr_shop[_arr_sch[sc_seq].Sp_Seq].Sp_Name + 'の' + (dt.getMonth() + 1) + '月' + dt.getDate() + '日の予定から外しますか？';
	option.push({
		text		: 'キャンセル',
		action	: function() {
			if(confirm(cancel_msg)){
				fncMenuCancel(sc_seq, st_seq);
			}
		}
	});

	// 予定を確認
	var confirm_msg = _arr_staff[st_seq].St_Name + "さんの予定を開きますか？";
	option.push({
		text		: '予定を確認',
		action	: function() {
			if(confirm(confirm_msg)){
				open('assign_list.php?st=' + st_seq, '_blank' ) ;
			}
		}
	});


	// 右クリックメニュ設定
	var contextMenuObj = new ContextMenu({
		element  : element,
		menuList : option
/*
		menuList : [
			{
				text		: 'リーダー設定',
				action	: function() {
					fncMenuLeader(st_seq);
				}
			},
			{
				text		: 'キャンセル',
				action	: function() {
					alert('SBショップ渋谷店の4月7日の予定から外します。');
				}
			},
			{
				text		: '予定を確認',
				action	: function() {
					if(confirm('山岸 徹さんの予定を開きますか？')){
						open('assign_list.php?st=123', '_blank' ) ;
					}
				}
			}
		]
*/
	});
	
}
function fncMenuLeader(sc_seq, st_seq, txt, role)
{
	//alert(txt + 'します。');
	
	var target = 'assign[' + sc_seq + '][' + st_seq + ']';
	
	// リーダー解除？
	if (role == STAFF_ROLE_NONE) {
		// フラグ解除
		_arr_sch[sc_seq].staff_list[st_seq].Sa_Role = STAFF_ROLE_NONE;// 配列のリーダーフラグをリセット
		$('a[name="' + target + '"] .leader-active').remove();// リーダーアイコンを消去
		$('input[name="' + target + '"]').val(STAFF_ROLE_NONE);// リーダーフラグをリセット
		
		// 右クリックメニュ更新
		fncContextMenu($('a[name="' + target + '"]'), sc_seq, st_seq);
		
	// リーダー設定？
	} else {
		$.each(_arr_sch[sc_seq].staff_list, function(i, val) {
			// 対象スタッフならリーダー設定
			if (i == st_seq) {
				_arr_sch[sc_seq].staff_list[st_seq].Sa_Role = STAFF_ROLE_LEADER;
				$('a[name="' + target + '"]').append('<div class="leader-active"><i class="fa fa-group" aria-hidden="true" style="margin-top:3px"></i></div>');
				$('input[name="' + target + '"]').val(STAFF_ROLE_LEADER);
				
				// 右クリックメニュ更新
				fncContextMenu($('a[name="' + target + '"]'), sc_seq, st_seq);
				
			// その他のスタッフがリーダー設定されていたら解除
			} else if (val.Sa_Role == STAFF_ROLE_LEADER) {
				var reset_target = 'assign[' + sc_seq + '][' + i + ']';
				_arr_sch[sc_seq].staff_list[i].Sa_Role = STAFF_ROLE_NONE;// 配列のリーダーフラグをリセット
				$('a[name="' + reset_target + '"] .leader-active').remove();// リーダーアイコンを消去
				$('input[name="' + reset_target + '"]').val(STAFF_ROLE_NONE);// リーダーフラグをリセット
				
				// 右クリックメニュ更新
				fncContextMenu($('a[name="' + reset_target + '"]'), sc_seq, i);
			}
		});
	}
	
	return ;
}
function fncMenuCancel(sc_seq, st_seq)
{
	// 当日のスケジュールから当スタッフを削除
	var target = 'assign[' + sc_seq + '][' + st_seq + ']';
	$('a[name="' + target + '"]').remove();
	$('input[name="' + target + '"]').remove();
	
	// スケジュール配列からも削除
	delete _arr_sch[sc_seq].staff_list[st_seq];
	
	return ;
}


/***************************************/
// Function: スケジュール登録からのコールバック
/***************************************/
function fncCallBackSchedule(sc_seq) {
	//alert(sc_seq);
	// 登録したスケジュールを取得し、配列（_arr_sch）に追加する
	msg = new Object();
	msg.mode			= "GET_SCHEDULE_DETAIL";
	msg.sc_seq		= sc_seq;
	fncScheduleAjax(msg);
	
	return ;
}


/***************************************/
// FUNCTION: 
/***************************************/
function fncScheduleAjax(msg)
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
		if (res['mode'] == "GET_SCHEDULE_DETAIL") {
			// 配列に追加
			_arr_sch[res['sc_seq']] = res['schedule'];
			_arr_sch[res['sc_seq']].staff_list = new Object();// _arr_sch.staff_listの空配列をObject型に変換する
			// 画面に表示
			$('#' + res['schedule'].Sp_Seq + '_' + res['schedule'].Sc_Date).attr('data-sc_seq', res['sc_seq']).empty();
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


/***************************************/
// ドラッグ開始時の処理
/***************************************/
function f_dragstart(event){
	//ドラッグしたデータのインデックスをDataTransferオブジェクトにセット
	_drag_st_seq = parseInt($(event.target).prop('name'), 10);

	//console.log("drag:" + _drag_st_seq);
	//event.dataTransfer.setData("text", _drag_st_seq);
	
	// 当スタッフを強調
	$(".staff_" + _drag_st_seq).addClass("now-catching");
	
	// 当スタッフのアサイン不可（欠勤、または同一日にアサイン済み）をグレー強調
	var dt = new Date(_CAL_START_D);
	for ($i = 0; $i < (_SELECT_WEEK_NUM * 7); $i++) {
		var d = _format_date(dt);
		// 勤務予定なし？
		if (!_arr_staff[_drag_st_seq]['workdate'][d]) {
			$(".sch_date_" + d).addClass("date-deactive");
		}
		// 同一日にアサイン済み？
		for (sc_seq in _arr_sch) {
			if (_arr_sch[sc_seq].d == d && _arr_sch[sc_seq].staff_list[_drag_st_seq]) {
				$(".sch_date_" + d).addClass("date-deactive");// この日の全スケジュールを不可とする
				break;
			}
		}
		
		dt.setDate(dt.getDate() + 1);
	}
	
	return ;
}


/***************************************/
// ドラッグ要素がドロップ要素入った処理
/***************************************/
function f_dragenter(event){
	// 当曜日がスタッフの休日になっていないこと（Classで判定）
	if (!$(event.target).hasClass("date-deactive")) {
		var sc_seq = parseInt($(event.target).attr('data-sc_seq'), 10);
		//console.log("f_dragenter: sc_seq:" + sc_seq + " st_seq:" + _drag_st_seq);
		// この日に当スタッフがアサインされていないければ、日付を強調表示
		if (sc_seq) {
			var find = false;
			$.each(_arr_sch[sc_seq].staff_list, function(i) {
				if (i == _drag_st_seq) {
					find = true;
					return ;
				}
			});
			if (find == false) {
				$("#" + _arr_sch[sc_seq].Sp_Seq + "_" + _arr_sch[sc_seq].Sc_Date).addClass("date-active");// 背景をドロップエリア色に変更
				_drop_sc_seq = sc_seq;// ドロップ先sc_seq保存
			}
		}
	}
	
	event.preventDefault();//dragoverイベントをキャンセルして、ドロップ先の要素がドロップを受け付けるようにする
}


/***************************************/
// ドラッグ要素がドロップ要素から外れた処理
/***************************************/
function f_dragleave(event){
	// ドロップ強調されていれば解除（Classで判定）
	if ($(event.target).hasClass("date-active")) {
		//console.log("f_dragleave");
		$(event.target).removeClass("date-active");
	}
	
	return ;
}


/***************************************/
// ドラッグ要素がドロップ要素に重なっている間の処理
/***************************************/
function f_dragover(event){
	//console.log("f_dragover");
	event.preventDefault();//dragoverイベントをキャンセルして、ドロップ先の要素がドロップを受け付けるようにする
}


/***************************************/
// ドロップ時の処理
/***************************************/
function f_drop(event){
	var sc_seq = parseInt($(event.target).attr("data-sc_seq"), 10);
	//console.log("f_drop:" + sc_seq);
	
	// ドロップエリア内ならこの日にスタッフ追加
	if (_drop_sc_seq != 0 && _drag_st_seq != 0) {
		// ドロップ先が強調表示されているかもチェック
		var target = "#" + _arr_sch[_drop_sc_seq].Sp_Seq + "_" + _arr_sch[_drop_sc_seq].Sc_Date;
		if ($(target).hasClass("date-active")) {
			$(target).removeClass("date-active");
			// ドロップ処理
			fncScheduleStaffAssign(_drop_sc_seq, _drag_st_seq);
		}
	}
	
	event.preventDefault();//エラー回避のため、ドロップ処理の最後にdropイベントをキャンセルしておく
}


/***************************************/
// ドラッグ終了時の処理
/***************************************/
function f_dragend(event){
	var st_seq = $(event.target).prop('name');
	//console.log("f_dragend:" + st_seq);
	
	// 当スタッフの強調解除
	$(".staff_" + st_seq).removeClass("now-catching");
	
	// 欠勤曜日の強調解除
	$(".sch_calender").removeClass("date-deactive");
	
	return ;
}


/***************************************/
// スケジュールにスタッフをアサインする
/***************************************/
function fncScheduleStaffAssign(sc_seq, st_seq)
{
	// 配列追加
	_arr_sch[sc_seq].staff_list[st_seq] = {
			Sa_Seq: 0,
			Sa_Role: STAFF_ROLE_NONE,
			St_Name: _arr_staff[st_seq].St_Name
	};
	
	// スタッフリスト追加
	var target = "#" + _arr_sch[sc_seq].Sp_Seq + "_" + _arr_sch[sc_seq].Sc_Date;
	var assign = "assign[" + sc_seq + "][" + st_seq + "]";
	var html = "";
	html += '<a class="catch-item staff_' + st_seq + '" name="' + assign + '">';
	html += '<p class="name">' + _arr_staff[st_seq].St_Name + '</p>';
	html += '</a>';
	html += '<input type="hidden" name="' + assign + '" value="' + STAFF_ROLE_NONE + '" />';
	$(target).append(html);
	
	// 右クリックメニュ設定
	fncContextMenu($('a[name="' + assign + '"]'), sc_seq, st_seq);
	
	return ;
}


/***************************************/
// 日付（YYYY-MM-DD）形式に変換
/***************************************/
function _format_date(dt)
{
	return dt.getFullYear() + "-" + ('0'+(dt.getMonth() + 1)).slice(-2) + "-" + ('0'+(dt.getDate())).slice(-2);
}




/*-------------------------------------*/
/*-------------------------------------*/
// オブジェクト定義
/*-------------------------------------*/
/*-------------------------------------*/


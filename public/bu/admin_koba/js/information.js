// グローバル変数
var _def_staff_upload_path;
var _def_info_category_training;
$(function(){


/***************************************/
// 起動時処理
/***************************************/

	//alert("information.js");
	
	_def_staff_upload_path = $.parseJSON($("#php_def_STAFF_UPLOAD_PATH").html());
	_def_info_category_training = $.parseJSON($("#php_def_INFO_CATEGORY_TRAINING").html());
	
	
	// カテゴリが動画以外の場合、動画カテゴリ欄を非表示
	if ($("[name='If_Category']").val() != _def_info_category_training) {
		$("#If_MovieCategory_area").hide();
	}
	
	
	
	
/*************************************************/
// Event: カテゴリを”動画”にした場合、動画カテゴリを可視化する
/*************************************************/
$("select[name='If_Category']").on('change', function() {
	if ($(this).val() == _def_info_category_training) {
		$("#If_MovieCategory_area").slideDown();
	} else {
		$("#If_MovieCategory_area").slideUp();
	}
});


/***************************************/
// Event:ファイルアップロード
/***************************************/
$(document).on('click','#btn_upload_file', function(){

	var _file = $('#upload_file');
	
	// ファイルあり？
	if (!_file.val()) {
		alert("アップロードするファイルを選択してください。");
		return false;
	}
	
	// 保存
	var fd = new FormData();
	// loading
	//dispLoading("処理中...");
	
	fd.append("file", $(_file).prop("files")[0]);
	fd.append("mode", "INFORMATION_FILE_UPLOAD");
	
	// POST作成
	var postData = {
		type : "POST",
		dataType : "text",
		data : fd,
		processData : false,
		contentType : false
	};
	
	// 送信
	$.ajax(
		"ajax.php", 
		postData
	).done(function(data, status, xhr) {
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
		
		// 選択中ファイルを消去
		$('#upload_file').val("");
		
		// 同一名称のファイルがなければリスト更新
		var _find = false;
		$('input[name="new_file[]"]').each(function() {
			if (res['filename'] == $(this).val()) {
				_find = true;
				return ;
			}
		});
		
		// リスト更新
		if (_find == false) {
			var _html = "";
			_html += '<div class="tr">';
			_html += '	<div class="td"><span class="label label-danger">NEW</span> ' + res['filename'] + '</div>';
			_html += '	<div class="td-edit" style="width:100px;">';
			_html += '		<input type="button" class="btn btn_new_file_delete" value="取消" />';
			_html += '		<input type="hidden" name="If_FilenameNew[]" value="' + res['filename'] + '" />';
			_html += '	</div>';
			_html += '</div>';
			$("#tbl_upload_file").append(_html);
		}
		
		
	}).fail(function(xhr, status, error) {
		//alert('error!!!');
			var msg = "[js fail]\n通信エラーが発生しました。";
			alert(msg);
			//location.href = "./estimate_list.php";
			return;
			
			
	}).always( function(data) {
		//removeLoading();// Lading 画像を消す
	});
	
	return ;
});


/***************************************/
// Event:ファイル削除
/***************************************/
$(document).on('click','.btn_new_file_delete', function(){
	// 枠ごと削除（ファイルはtmpにあるためCRONで削除）
	$(this).closest('div.tr').remove();
	return false;
});






});


/*-------------------------------------*/
/*-------------------------------------*/
// FUNCTION
/*-------------------------------------*/
/*-------------------------------------*/
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






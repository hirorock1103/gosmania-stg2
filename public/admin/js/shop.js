// グローバル変数
var _input_type;
$(function(){


/***************************************/
// 起動時処理
/***************************************/

	//alert("shop.js");
	
	// phpデータ取得
	_input_type		= $.parseJSON($("#php_input_type").html());
	
	
	// sortable
	if (_input_type == "INPUT") {
		$(".sortable").sortable({
			cursor: "pointer", 
			opacity: 0.5
		});
	}
	
	
	
	
/***************************************/
// Event:郵便番号
/***************************************/
$(document).on("click", ".zip_search", function() {
	var zip = $('input[name="Sp_ZipCode"]').val().replace(/[－\-　 ]/g, '');
	var han = zip.replace(/[０-９－]/g, function(s) {
		return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
	});
	$('input[name="Sp_ZipCode"]').val(han);
	AjaxZip3.zip2addr("Sp_ZipCode", "", "Sp_Address", "Sp_Address");
	return false;
});


/***************************************/
// Event:座標算出
/***************************************/
$(document).on("click", ".point_search", function() {
	var addr = $('input[name="Sp_Address"]').val().replace(/[\　 ]/g, '');
	
	msg = new Object();
	msg.mode			= "SEARCH_SHOP_POINT";
	msg.addr			= addr;
	fncShopAjax(msg);
	
	return false;
});







});


/*-------------------------------------*/
/*-------------------------------------*/
// FUNCTION
/*-------------------------------------*/
/*-------------------------------------*/
function fncShopAjax(msg)
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
		if (res['mode'] == "SEARCH_SHOP_POINT") {
			if (res['err'] == "") {
				$('input[name="Sp_Lat"]').val(res['lat']);
				$('input[name="Sp_Lng"]').val(res['lng']);
			} else {
				alert(res['err']);
			}
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
// ドラッグ開始時の処理
/***************************************/
/* jquery ui sortableで対応
function f_dragstart(event){
	//ドラッグしたデータのインデックスをDataTransferオブジェクトにセット
	var obj = $(event.target).closest("div.tr");
	var i = $('.report_config').index(obj);
	//console.log("drag:" + i);
	event.dataTransfer.setData("text", i);
}
*/


/***************************************/
// ドラッグ要素がドロップ要素に重なっている間の処理
/***************************************/
/* jquery ui sortableで対応
function f_dragover(event){
	// 挿入位置の下線を強調
	$(".droparea").css("border-bottom","1px solid #CCC");
	var obj = $(event.target).closest("div.tr");
	$(obj).css("border-bottom","1px solid #f60");
	//console.log($(obj).prop("id"));
	
	event.preventDefault();//dragoverイベントをキャンセルして、ドロップ先の要素がドロップを受け付けるようにする
}
*/


/***************************************/
// ドロップ時の処理
/***************************************/
/* jquery ui sortableで対応
function f_drop(event){
	console.log("f_drop");
	$(".droparea").css("border-bottom","1px solid #CCC");// 下線を元に戻す
	
	var obj = $(event.target).closest("div.tr");
	var drop = $('.droparea').index(obj);
	var drag = event.dataTransfer.getData("text");
	//console.log("drag:" + drag + " drop:" + drop);
	
	// 異なる位置にドロップされた場合のみ処理
	if (drag != drop && drag != (drop - 1)) {
		//console.log("drag:" + drag + " drop:" + drop);
		var item = $('.report_config:eq(' + drag + ')');
		//var html = $(item).clone(true);
		$(item).remove();
		//console.log($(item).html());
		if (drag > drop) {
			// ↑に行くときは、自分をRemoveしてDrop先にInsert
			$(item).insertBefore($('.report_config:eq(' + drop + ')'));
		} else {
			// ↓に行くときは、自分をRemoveしてDrop-1にInsert
			if (drop > $('.report_config').length) {
				$(item).insertAfter($('.report_config:last'));
			} else {
				$(item).insertBefore($('.report_config:eq(' + (drop - 1) + ')'));
			}
		}
	}

	event.preventDefault();//エラー回避のため、ドロップ処理の最後にdropイベントをキャンセルしておく
}
*/


/***************************************/
// ドロップ終了時の処理
/***************************************/
/* jquery ui sortableで対応
function f_dropend(event){
	console.log("f_dropend");
	$(".droparea").css("border-bottom","1px solid #CCC");
}
*/


/***************************************/
// FUNCTION: ローディング
/***************************************/
function dispLoading(msg){
	var dispMsg = "<div class='loadingMsg'></div>";
	// ローディング画像が表示されていない場合のみ出力
	if($("#loading").length == 0){
		$("body").append("<div id='loading'>" + dispMsg + "</div>");
	}
	return ;
}
function removeLoading(){
	$("#loading").remove();
}





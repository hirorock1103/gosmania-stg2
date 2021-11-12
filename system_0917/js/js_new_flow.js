$(function(){

/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
/* new_flow01.html /////////////////////////////////////////////////////////////////////////////////////////////*/

// ページトップ
// $("#page-top").click(function(){
$(document).on('click', '#page-top',function(){
	$('html,body').animate({ scrollTop: 0 }, 500);
});

// スクロールメニュー
function CartSide() {
	var CSScroll	= $(window).scrollTop();
	// if(CSScroll > 252) {	$("#price_box").css("margin-top", (CSScroll - 252)); }
	if(CSScroll > 252) {	$("#price_box_ajax").css("top", (CSScroll - 20) + "px"); }
	if(CSScroll < 252) {	$("#price_box_ajax").css("top", "202px"); }
	// if(CSScroll > 142) {	$("#price_box").css("top", (CSScroll + 40) + "px"); }
	// if(CSScroll < 142) {	$("#price_box").css("top", "202px"); }
}

$(window).load(CartSide);
$(window).resize(CartSide);
$(window).scroll(CartSide);

// 表示制御
// 初期状態 Ver.02
$("#JS-fw01-02").css("display", "none");
$("#JS-fw01-07").css("display", "none");
$("#price_box .content:nth-child(2)").css("display", "");
$("#price_box .content:nth-child(3)").css("display", "none");
$("#price_box .price").css("display", "none");
$("#price_box .option_box").css("display", "none");
/* $("#price_box .option").css("display", "none"); */


$(document).on('click', '#simset01',function(){
/*	$(".JS-fw01-01").css("display", "");
	$("#JS-fw01-02").css("display", "none");
	$("#JS-fw01-04").css("display", "none");
	$("#JS-fw01-05").css("display", "");
	$("#JS-fw01-06").css("display", "");
	$("#JS-fw01-08").css("display", "");
	$("#JS-fw01-09").css("display", "");

	$("#price_box .content:nth-child(2)").css("display", "");
	$("#price_box .content:nth-child(3)").css("display", "none");
	$("#price_box .price").css("display", "none");
	$("#price_box .option_box").css("display", "none");*/
	$("#JS-fw01-05").css("display", "");/*Instagramのアカウント情報*/
	$("#JS-fw01-03").css("display", "");/*Twitterのアカウント情報*/

});

$(document).on('click', '#simset02',function(){
/*	$(".JS-fw01-01").css("display", "none");
	$("#JS-fw01-02").css("display", "");
	$("#JS-fw01-04").css("display", "");
	$("#JS-fw01-05").css("display", "none");
	$("#JS-fw01-06").css("display", "");
	$("#JS-fw01-08").css("display", "none");
	$("#JS-fw01-09").css("display", "");

	$("#price_box .content:nth-child(2)").css("display", "");
	$("#price_box .content:nth-child(3)").css("display", "none");
	$("#price_box .price").css("display", "none");
	$("#price_box .option_box").css("display", "none");*/
	$("#JS-fw01-05").css("display", "none");/*Instagramのアカウント情報*/
	$("#JS-fw01-03").css("display", "");/*Twitterのアカウント情報*/

});


$(document).on('click', '#simset03',function(){
	$("#JS-fw01-05").css("display", "");/*Instagramのアカウント情報*/
	$("#JS-fw01-03").css("display", "none");/*Twitterのアカウント情報*/
});



$(document).on('click', '#simtype01',function(){
	$("#JS-fw01-06").css("display", "");
	$("#JS-fw01-07").css("display", "none");
//	$("#JS-fw01-09").css("display", "");
});

$(document).on('click', '#simtype02',function(){
	$("#JS-fw01-06").css("display", "none");
	$("#JS-fw01-07").css("display", "none");
//	$("#JS-fw01-09").css("display", "none");
});

$(document).on('click', 'input[name="tanmatu"]',function(){
	$("#price_box .tanmatu_box .content:nth-child(2)").css("display", "none");
	$("#price_box .tanmatu_box .content:nth-child(3)").css("display", "");
	$("#price_box .tanmatu_box .price").css("display", "");
});

$(document).on('click', 'input[name="plan"]',function(){
	$("#price_box .plan_box .content:nth-child(2)").css("display", "none");
	$("#price_box .plan_box .content:nth-child(3)").css("display", "");
	$("#price_box .plan_box .price").css("display", "");
});

$(document).on('click', '#keiyaku01',function(){
	$("#JS-fw01-07").css("display", "none");
});

$(document).on('click', '#keiyaku02',function(){
	$("#JS-fw01-07").css("display", "");
});

$(document).on('click', 'input[name="option"]',function(){
	$("#price_box .option_box").css("display", "");
});


$(document).on('click', '#type01',function(){
    $(".switch_must").removeClass('must');
});
$(document).on('click', '#type02',function(){
    $(".switch_must").addClass('must');
});

/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
/* new_flow02.html /////////////////////////////////////////////////////////////////////////////////////////////*/

// お申込み種別の選択
function JS_fw02_ak() {
	$("#aplly_kind00").css("display", "none");
	$("#aplly_kind01").css("display", "none");
	$("#aplly_kind02").css("display", "none");
	var aplly_kind	= $('input[name="aplly_kind"]:checked').val();
	if(aplly_kind == 1){
		$('#aplly_kind00').css("display", "");
		$('#aplly_kind01').css("display", "");
		$("#aplly_kind02").css("display", "none");
	}
	if(aplly_kind == 2){
		$('#aplly_kind00').css("display", "");
		$('#aplly_kind01').css("display", "none");
		$("#aplly_kind02").css("display", "");
	}
}

$(window).load(JS_fw02_ak);
$('input[name="aplly_kind"]').click(JS_fw02_ak);

// 住所自動入力
function JS_fw02_aa() {
	AjaxZip3.zip2addr( 'zip_code', '', 'prefecture', 'city' );
	AjaxZip3.onSuccess = function() {
		AjaxZip3.zip2addr( 'zip_code', '', 'AutoAddr', 'AutoAddr' );
	}
}

// $(window).load(JS_fw02_aa);
$("#AutoAddr").click(JS_fw02_aa);

// クレジットカード
function JS_fw02_ar() {
	$("#agree_relationships").css("display", "none");
	var relationships	= $('input[name="relationships"]:checked').val();
	if(relationships == 1){
		$('#agree_relationships').css("display","none");
	}
	if(relationships == 2){
		$('#agree_relationships').css("display", "");
	}
}

$(window).load(JS_fw02_ar);
$('input[name="relationships"]').click(JS_fw02_ar);

// 審査表示
$(document).on('click', '#Examination',function(){
	$("#second").css("display", "none");
	$("#ExaminationLoading").css("display", "");
	$('form').submit();
});

/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
/* new_flow03.html /////////////////////////////////////////////////////////////////////////////////////////////*/

// なし

/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
/* new_flow04.html /////////////////////////////////////////////////////////////////////////////////////////////*/

// 本人確認書類の選択
function JS_fw04_pl() {
	$(".JS-fw04-pl").css("display", "none");
	$(".JS-fw04-pl input").prop('disabled', true);

	var JS_fw04_pl	= $("#JS-fw04-pl option:selected").val();

	$(".JS-fw04-pl" + JS_fw04_pl).css("display", "");
	$(".JS-fw04-pl" + JS_fw04_pl + " input").prop('disabled', false);
}

$(window).load(JS_fw04_pl);
$('#JS-fw04-pl').change(JS_fw04_pl);

// フィルタリング
$('#filtering02').css("visibility","hidden");
$('.filtering03').css("visibility","hidden");
$('#filtering04').css("visibility","hidden");

function JS_fw04_fu() {
	var filtering01	= $('input[name="filtering_user"]:checked').val();
	if(filtering01 == 3){
		$('#filtering02').css("visibility","visible");
	} else {
		$('#filtering02').css("visibility","hidden");
		$('.filtering03').css("visibility","hidden");
		$('#filtering04').css("visibility","hidden");
		$('input[name="filtering_type"]').prop('checked',false);
		$('input[name="filtering_flag"]').prop('checked',false);
	}
}

$(window).load(JS_fw04_fu);
$('input[name="filtering_user"]').click(JS_fw04_fu);

function JS_fw04_ft() {
	var filtering02	= $('input[name="filtering_type"]:checked').val();
	if(filtering02 == 1){
		$('.filtering03').css("visibility","visible");
	} else {
		$('.filtering03').css("visibility","hidden");
		$('#filtering04').css("visibility","hidden");
		$('input[name="filtering_flag"]').prop('checked',false);
	}
}

$(window).load(JS_fw04_ft);
$('input[name="filtering_type"]').click(JS_fw04_ft);

function JS_fw04_ff() {
	var filtering03	= $('input[name="filtering_flag"]:checked').val();
	if(filtering03 == 2){
		$('#filtering04').css("visibility","visible");
	} else {
		$('#filtering04').css("visibility","hidden");
	}
}

$(window).load(JS_fw04_ff);
$('input[name="filtering_flag"]').click(JS_fw04_ff);

/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
/* new_flow05.html /////////////////////////////////////////////////////////////////////////////////////////////*/

// なし

/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////*/
/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////*/

});
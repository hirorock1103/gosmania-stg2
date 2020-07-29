$(document).ready(function() {

  var triggerChild = document.querySelectorAll('.tree-view-child');

  for (trigger of triggerChild) {

    var target = trigger.querySelector('.fa-angle-left');

    trigger.addEventListener('click', function() {
      setTimeout(function() {
        target.classList.toggle('turn');
      }, 100);
    });
    var active = trigger.children[1].querySelectorAll('.active').length;
    if(active != 0) {
      target.classList.add('turn');
    }

  }


	/***************************************/
	// Event: Form送信時のbootstrapトグルボタンチェック
	/***************************************/
	$(document).on("submit", "form", function() {
		return fncBootstrapButtonsCheck();
	});
});


function toHan(ele) {
    var val = $(ele).val();
    var han = val.replace(/[Ａ-Ｚａ-ｚ０-９]/g,function(s){return String.fromCharCode(s.charCodeAt(0)-0xFEE0)});

    if(val.match(/[Ａ-Ｚａ-ｚ０-９]/g)){
        $(ele).val(han);
    }
}


function fncBootstrapButtonsCheck()
{
	$("div[data-toggle='buttons'] label.btn-default").each(function(i) {
		//console.log(i + " " + $(this).hasClass("active") + " " + $("input", this).val() + " " + $("input", this).prop('checked'));
		if ($(this).hasClass("active")) {
			$("input[type='radio'],input[type='checkbox']", this).prop('checked', true);
		}
	});
	
	return true;
}

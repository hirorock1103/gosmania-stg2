// グローバル変数
var _arr_schedule = new Object();
var _arr_fusen = new Object();
var _arr_staff = new Object();
var _def_schedule_status = new Object();

$(function(){


/***************************************/
// 起動時処理
/***************************************/

	//alert("today_schedule.js");

	// phpデータ取得
	_arr_schedule = $.parseJSON($("#php_arr_schedule").html());
	_arr_fusen = $.parseJSON($("#php_arr_fusen").html());
	_arr_staff = $.parseJSON($("#php_arr_staff").html());
	_def_schedule_status = $.parseJSON($("#php_def_schedule_status").html());
	
	// 付箋表示
	generate();




});


/*-------------------------------------*/
/*-------------------------------------*/
// FUNCTION
/*-------------------------------------*/
/*-------------------------------------*/

/***************************************/
// Event: スケジュール表示
/***************************************/
function generate(type = null) 
{
	var tasks = [];
	var staff = [];
	
	for (i in _arr_fusen) {
		for (sr_seq in _arr_fusen[i]) {
			var task = {
				startTime:			_arr_fusen[i][sr_seq].startTime,
				duration:				_arr_fusen[i][sr_seq].duration,
				column:					i,
				id:							sr_seq,
				state:					_arr_fusen[i][sr_seq].state,
				name:						_arr_fusen[i][sr_seq].name,
				address:				_arr_fusen[i][sr_seq].address,
				service:				_arr_fusen[i][sr_seq].service,
				add_class:			_arr_fusen[i][sr_seq].add_class,
			};
			tasks.push(task);
		}
	}
	
	for (i in _arr_staff) {
		staff.push(_arr_staff[i]);
	}
	
/*
	for (var i = 0; i < 10; i++) {
		var startTime = -1;
		var duration = 0.5;

		var index = 0;

		for (var j = 0; j < 4; j++) {
			if (Math.random() * 10 > 5) {
				startTime += 0.5 + duration;
			} else {
				startTime += 1 + duration;
			}
			//startTime += 0.5 + duration;

			if (startTime > 10) {
				break;
			}

			duration = Math.ceil(Math.random() * 2) + (Math.random() * 10 > 5 ? 0 : 0.5);
			//duration = 2.5;

			duration -= startTime + duration > 24 ? (startTime + duration) - 24 : 0;

			var data = customer_data[index];
			++index;
			if(index >= 5) {
				index = 0;
			}


			var add_id = "card";
			var add_class = "";
			switch(data[0]) {
				case "未出発" :
					add_class = "NotDeparted";
					break;
				case "出発済み" :
					add_class = "Departure";
					break;
				case "入室済み" :
					add_class = "Enter";
					break;
				case "退出済み" :
					add_class = "Leave";
					break;
				case "報告済み" :
					add_class = "Report";
					break;
			}
			// var date = new Date();
			//
			// var hour = date.getHours();
			// var minute = date.getMinutes();

			if(data[0] == "未出発" && (startTime + 9) < 16) {
				add_class = "NotDeparted RedLine";
				add_id = "RedLine";
			}

			var task = {
				startTime: startTime,
				duration: duration,
				column: i,
				id: Math.ceil(Math.random() * 100000),
				state: data[0],
				name: data[1],
				address: data[2],
				service: data[3],
				client_number: data[4],
				staff_number: data[5],
				add_id:add_id,
				add_class:add_class
			};

			tasks.push(task);
		}
	}
	*/
	//console.log("tasks count: " + tasks.length);

	//console.log(JSON.stringify(tasks));

	$("#skeduler-container").skeduler({
		headers: staff,
		tasks: tasks,
		//cardTemplate: '<div>${state}</div><div>${name}</div><div>${address}</div><div>${service}</div><div>${client_number}</div><div>${staff_number}</div>',
		cardTemplate: '<div>${state}</div><div>${name}</div><div>${address}</div><div>${service}</div>',

		onClick: function (e, t) { modalOpen(e, t); }
	});
}


/***************************************/
// Event: モーダルオープン
/***************************************/
function modalOpen(e, t)
{
	var data = _arr_schedule[t.id];
	
	// スタッフ
	$("input[name='SR_Seq']").val(data.SR_Seq);
	$("input[name='Sc_Date']").val(data.Sc_Date);
	$("input[name='SR_DepartureCertain']").val(data.SR_DepartureCertain_HI);
	$("input[name='SR_StartdateCertain']").val(data.SR_StartdateCertain_HI);
	$("input[name='SR_EnddateCertain']").val(data.SR_EnddateCertain_HI);
	$("select[name='SR_Status']").val(data.SR_Status);
	
	$("#St_Name").html(data.St_Name);
	$("#SR_StartLocation").html(data.SR_StartLocation);
	$("#SR_MovingTime").html(data.SR_MovingTime);
	$("input[name='St_Name']").val(data.St_Name);
	$("input[name='SR_StartLocation']").val(data.SR_StartLocation);
	$("input[name='SR_MovingTime']").val(data.SR_MovingTime);
	
	
	// スケジュール
	$("input[name='Sc_Seq']").val(data.Sc_Seq);
	$("input[name='Sc_Date']").val(data.Sc_Date);
	$("input[name='Sc_Startdate']").val(data.Sc_Startdate_HI);
	$("input[name='Sc_Enddate']").val(data.Sc_Enddate_HI);
	$("select[name='Sc_WorkHours']").val(parseFloat(data.Sc_WorkHours));
	// 延長時間のOPTION設定
	var Sc_ExtensionTime = parseFloat(data.Sc_ExtensionTime);
	var html = '<option value="0"></option>';
	for (var i = 1; i <= 6; i++) {
		var value = parseFloat(i * data.Pl_ExetendedTimeUnit);
		html += '<option value="' + value + '"';
		if (value == Sc_ExtensionTime) {
			html += ' selected';
		}
		html += '>' + value + '時間</option>';
	}
	$("select[name='Sc_ExtensionTime']").empty().append(html);
	
	$("#Ca_Name").html(data.Ca_Name);
	$("#Ca_Address").html(t.address);
	$("#Sc_Status").html(_def_schedule_status[data.Sc_Status]);
	$("input[name='Ca_Name']").val(data.Ca_Name);
	$("input[name='Ca_Address']").val(t.address);
	$("input[name='Sc_Status']").val(data.Sc_Status);
	$("input[name='Pl_ExetendedTimeUnit']").val(data.Pl_ExetendedTimeUnit);
	
	
	return true;
}



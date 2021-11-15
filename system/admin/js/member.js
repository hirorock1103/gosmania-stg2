function resetInsertForm() {
	document.insertForm.i_MemberID.value = '';
	document.insertForm.i_MemberPassword.value = '';
	document.insertForm.i_MemberMailAddress.value = '';
}
function resetSearchForm() {
	document.searchForm.s_MemberID.value = '';
	document.searchForm.s_MemberPassword.value = '';
	document.searchForm.s_MemberMailAddress.value = '';
}

/******************** 編集に関する処理 ********************/
var globalBtnEdit;
var globalTd;
var globalMemberID;
var globalMemberPassword;
var globalMemberMailAddress;

function editData(btnEdit) {
	if (btnEdit !== globalBtnEdit) {
		// グローバル変数に値が格納されている場合のみ処理
		if (typeof globalTd !== 'undefined') {
			globalBtnEdit.innerHTML = '編集';
			globalTd[0].innerHTML   = globalMemberID;
			globalTd[1].innerHTML   = globalMemberPassword;
			globalTd[2].innerHTML   = globalMemberMailAddress;
		}
		
		var td = btnEdit.parentNode.getElementsByTagName('td');
		var MemberID          = td[0].innerHTML;
		var MemberPassword    = td[1].innerHTML;
		var MemberMailAddress = td[2].innerHTML;
		var MemberSEQ         = td[6].innerHTML;

		// グローバル変数に各情報を格納
		globalTd                = td;
		globalMemberID          = MemberID;
		globalMemberPassword    = MemberPassword;
		globalMemberMailAddress = MemberMailAddress;
		
		td[0].innerHTML = '<input type="text" value="' + MemberID +'" name="u_MemberID" size="15">';
		td[1].innerHTML = '<input type="text" value="' + MemberPassword +'" name="u_MemberPassword" size="15">';
		td[2].innerHTML = '<input type="text" value="' + MemberMailAddress +'" name="u_MemberMailAddress" size="25">';
		
		// 選択中の担当者シークエンスをhiddenとして設定し、編集というテキストをサブミットボタンにする
		btnEdit.innerHTML  = '<input type="hidden" value="' + MemberSEQ + '" name="u_MemberSEQ">';
		btnEdit.innerHTML += '<input type="submit" value="編集" name="update" class="myButton" style="min-width:42px;">';
	}
	// グローバル変数にクリックされたオブジェクトを格納
	globalBtnEdit = btnEdit;
}


/******************** ロード後処理 ********************/
window.onload = function() {
	if (columnName !== '') {
		var orderColumn = document.getElementsByClassName('th-list');
		var re = /DESC$/;
		
		var matchString = re.exec(columnName);
		
		if (matchString) {
			var img = document.createElement('img');
			img.setAttribute('src', 'image/arrow_down_desc.png');
			img.setAttribute('width', '15px');
			orderColumn[columnNum].appendChild(img);
		}
		if (!matchString) {
			var img = document.createElement('img');
			img.setAttribute('src', 'image/arrow_down_asc.png');
			img.setAttribute('width', '15px');
			orderColumn[columnNum].appendChild(img);
		}
	}
}
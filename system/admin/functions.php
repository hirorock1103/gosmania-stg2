<?php


/**********************************************/
// INSERT
/**********************************************/
function InsertCommon($dbh, $table, $params, $pr_key)
{
	$dbh->beginTransaction();
	try {
		$sql = "INSERT INTO " . $table . " (";
		foreach ($params as $key => $val) {
			$sql .= $key . ",";
		}
		$sql = rtrim($sql, ",");
		
		$sql .= ") VALUES (";
		foreach ($params as $key => $val) {
			$sql .= ":" . $key . ",";
		}
		$sql = rtrim($sql, ",");
		$sql .= ")";
		
/* 		echo $sql;
		var_dump($params);
		exit; */
		
		$db = $dbh->prepare($sql);
		foreach ($params as $key => $val) {
			$db->bindValue(':' . $key, $val['value'], $val['type']);
		}
		
		$db->execute();
		$id = $dbh->lastInsertId($pr_key);
		$dbh->commit();
		return $id;
		
	} catch (Exception $e) {
		$dbh->rollBack();
		return $e->getMessage();;
	}
	
	
}


/**********************************************/
// UPDAE
/**********************************************/
function UpdateCommon($dbh, $table, $params, $condition )
{
	$dbh->beginTransaction();
	try {
		$sql = "UPDATE " . $table . " SET ";
		$arr_set = array();
		foreach ($params as $key => $val) {
			$arr_set[] = $key . " = :" . $key;
		}
		$sql .= implode(", ", $arr_set);
		$sql .= " WHERE ";
		
		$arr_where = array();
		foreach ($condition as $key => $val) {
			$arr_where[] = $key . " = :w" . $key;
		}
		$sql .= implode(" AND ", $arr_where);
		
		$db = $dbh->prepare($sql);
		foreach ($params as $key => $val) {
			$db->bindValue(':' . $key, $val['value'], $val['type']);
		}
		
		foreach ($condition as $key => $val) { 
			$db->bindValue(':w' . $key, $val['value'], $val['type']); //paramsに同じカラムあるとバグるのでwを記載
		}
		
		$db->execute();
		$dbh->commit();
		
		return true;
		
	} catch (Exception $e) {
		$dbh->rollBack();
		return $e->getMessage();
	}
	
}


/**********************************************/
// INSERT DUPLICATE UPDAE
/**********************************************/
function InsertUpdateCommon($dbh, $table, &$params, $uniq_key, &$errmsg)
{
	$arr_set = array();
	
	try {
		$sql = "INSERT INTO " . $table . " (";
		foreach ($params as $key => $val) {
			$sql .= $key . ",";
		}
		$sql = rtrim($sql, ",");
		
		$sql .= ") VALUES (";
		foreach ($params as $key => $val) {
			$sql .= ":" . $key . ",";
		}
		$sql = rtrim($sql, ",");
		$sql .= ") ON DUPLICATE KEY UPDATE ";
		foreach ($params as $key => $val) {
			if ($key != $uniq_key && !isset($val['insert'])) {// UNIQキーでなく、かつINSERT時のみのカラムでない場合、UPDATEに追加
				$arr_set[] = $key . " = values(" . $key . ")";
			}
		}
		$sql .= implode(", ", $arr_set);
		/**
		echo $sql;
		var_dump($params);
		/**/
		
		$db = $dbh->prepare($sql);
		foreach ($params as $key => $val) {
			$db->bindValue(':' . $key, $val['value'], $val['type']);
		}
		
		$db->execute();
		
		
	} catch (Exception $e) {
		$errmsg[] = $e->getMessage();
		return false;
	}
	
	return true;
}


/**********************************************/
// DELETE
/**********************************************/
function DeleteCommon($dbh, $table, &$condition, &$errmsg)
{
	try {
		$sql = "DELETE FROM " . $table;
		$sql .= " WHERE ";
		
		$arr_where = array();
		foreach ($condition as $key => $val) {
			$arr_where[] = $key . " = :" . $key;
		}
		$sql .= implode(" AND ", $arr_where);
		/**
		echo $sql;
		var_dump($condition);
		/**/
		
		$db = $dbh->prepare($sql);
		foreach ($condition as $key => $val) {
			$db->bindValue(':' . $key, $val['value'], $val['type']);
		}
		
		$db->execute();
		
		
	} catch (Exception $e) {
		$errmsg[] = $e->getMessage();
		return false;
	}
	
	return true;
}


/**********************************************/
// 共通リスト取得
/**********************************************/
function GetListCommon($dbh, $condition = null, $column = null, $table = null, $pri_key = null, $sort_col = null)
{
	$ret = array();
	
	// SQL
	$sql	= "SELECT " . ($column ? implode(",", $column) : '*') . " FROM " . $table . " ";
	$sql .= "WHERE " . $pri_key . " != '' "; //$pri_keyは必ずしもintとは限らない
	foreach ($condition as $key => $val) {
		$sql .= "AND " . $key . $val['method'] . " :" . $val['placeholder'] . " ";
	}
	if ($sort_col) {
		$sql .= "ORDER BY " . $sort_col . " ";
	} else {
		$sql .= "ORDER BY " . $pri_key . " ASC ";
	}

/* 	echo $sql;
	var_dump($condition);
	exit; */
	
	$db = $dbh->prepare($sql);
	foreach ($condition as $key => $val) {
		if($val['method'] == ' LIKE'){
			$db->bindValue(':' . $val['placeholder'], '%'.$val['value'].'%', $val['type']);
		}else{
			$db->bindValue(':' . $val['placeholder'], $val['value'], $val['type']);
		}
	}
	
	if($db->execute()){
		while ($row = $db->fetch(PDO::FETCH_ASSOC)){
			$ret[$row[$pri_key]] = $row;
		}
	}
	
	return $ret;
}


function getListByQuery($dbh, $sql,  $pdo = array() , &$total_rows)
{
	$ret = array();
	
	$db = $dbh->prepare($sql);
	foreach($pdo as $k => $row ){
		$db->bindValue($row[0], $row[1], $row[2]);
	}

	if($db->execute()){
		while ($row = $db->fetch(PDO::FETCH_ASSOC)){
			$ret[] = $row;
		}
	}

	//calc rows
	$db = $dbh->prepare("SELECT FOUND_ROWS()");
	$db->execute();
	$total_rows = $db->fetchColumn();

	return $ret;
}
function SearchListCommon2($dbh, $condition = null, $column = null, $table = null, $pri_key = null, $sort_col = null, $limit = 0 , &$total_rows)
{
	$ret = array();
	
	// SQL
	$sql	= "SELECT SQL_CALC_FOUND_ROWS " . ($column ? implode(",", $column) : '*') . " FROM " . $table . " ";
	$sql .= "WHERE " . $pri_key . " != '' "; //$pri_keyは必ずしもintとは限らない
	foreach ($condition as $key => $val) {
		$sql .= "AND " . $val['column'] .' '. $val['method'] . " :" . $key . " ";
	}
	if ($sort_col) {
		$sql .= "ORDER BY " . $sort_col . " ";
	} else {
		$sql .= "ORDER BY " . $pri_key . " ASC ";
	}

	if($limit > 0){
		$sql .= " LIMIT :limit ";
	}
	//echo $sql;
/* 	echo $sql;
	var_dump($condition);
	exit; */
	
	$db = $dbh->prepare($sql);
	foreach ($condition as $key => $val) {
		if($val['method'] == ' LIKE'){
			$db->bindValue(':' . $key, '%'.$val['value'].'%', $val['type']);
		}else{
			$db->bindValue(':' . $key, $val['value'], $val['type']);
		}
	}
	
	if($limit > 0){
		$db->bindValue(":limit" , $limit, PDO::PARAM_INT);
	}
	if($db->execute()){
		while ($row = $db->fetch(PDO::FETCH_ASSOC)){
			$ret[$row[$pri_key]] = $row;
		}
	}

	//calc rows
	$db = $dbh->prepare("SELECT FOUND_ROWS()");
	$db->execute();
	$total_rows = $db->fetchColumn();

	return $ret;
}

function SearchListCommon($dbh, $condition = null, $column = null, $table = null, $pri_key = null, $sort_col = null)
{
	$ret = array();
	
	// SQL
	$sql	= "SELECT " . ($column ? implode(",", $column) : '*') . " FROM " . $table . " ";
	$sql .= "WHERE " . $pri_key . " != '' "; //$pri_keyは必ずしもintとは限らない
	foreach ($condition as $key => $val) {
		$sql .= "AND " . $val['column'] .' '. $val['method'] . " :" . $key . " ";
	}
	if ($sort_col) {
		$sql .= "ORDER BY " . $sort_col . " ";
	} else {
		$sql .= "ORDER BY " . $pri_key . " ASC ";
	}

/* 	echo $sql;
	var_dump($condition);
	exit; */
	
	$db = $dbh->prepare($sql);
	foreach ($condition as $key => $val) {
		if($val['method'] == ' LIKE'){
			$db->bindValue(':' . $key, '%'.$val['value'].'%', $val['type']);
		}else{
			$db->bindValue(':' . $key, $val['value'], $val['type']);
		}
	}
	
	if($db->execute()){
		while ($row = $db->fetch(PDO::FETCH_ASSOC)){
			$ret[$row[$pri_key]] = $row;
		}
	}
	
	return $ret;
}


/**********************************************/
// アップデートやインサートのcolumn定義
/**********************************************/
function DefineColumns($dbh, $table, $not_use_columns)
{
		$ret = [];
	
		$sql = "DESC " . $table . " SHOW COLUMNS FROM " . $table;
		$db = $dbh->prepare($sql);
		$db->execute();
		while ($row = $db->fetch(PDO::FETCH_ASSOC)){
			if(isset($not_use_columns[$row['Field']])){
				continue;
			}
			$ret[$row['Field']] = $row;
		}
		return $ret;
}

/**
 * GMOIDで重複するレコードのうち最新かつ csv_output_data がNULLのレコードだけ取得
 * オプションで 出力済も含める、 期間指定する 
 * が可能
 * @param PDOobject $dbh
 * @param bool $includeOutputted true-> 出力済になっているデータもSELECTする
 * @param string-date $since '2020-01-01' or NULL 指定した日より後のデータに限定
 * @param string-date $until '2020-01-01' or NULL 指定した日より前のデータに限定
 * @return Array PaymentInfo レコード + 同じIDを持つCustomer レコード
 */
function getPaymentInfoRecords($dbh, $includeOutputted = false, $since = NULL, $until = NULL) {
	/*$sql = "SElECT Pi.*, Cs.* FROM PaymentInfo AS Pi
	LEFT JOIN PaymentInfo AS Pi2 ON Pi.gmo_id = Pi2.gmo_id AND Pi.seq < Pi2.seq
	LEFT JOIN Customer  AS Cs ON Pi.gmo_id = Cs.Cs_Id
	WHERE Pi2.seq IS NULL ";*/
	$sql = "SELECT Pi.*, Cs.* FROM PaymentInfo AS Pi 
		left join Customer as Cs ON Pi.gmo_id = Cs.Cs_Id
where Pi.seq in (select max(seq) from PaymentInfo group by gmo_id )";

	// クエリの埋め込み
	if($includeOutputted ) {
		// false-> NULLでなくても出力する
		// クエリは変わらない
	}else {
		// true-> NULLしか出力しない
		// WHERE に条件が追加
		$sql .= " AND Pi.csv_output_date IS NULL ";
	}

	if(!empty($since)) {
		$sql .= " AND Pi.createdate >= :since ";
	}
	if(!empty($until)) {
		$sql .= " AND Pi.createdate <= :until ";
	}

	$db = $dbh->prepare($sql);

	// クエリ条件のバインド
	if(!empty($since)) {
		$db->bindValue(':since', $since, PDO::PARAM_STR);
	}
	if(!empty($until)) {
		$db->bindValue(':until', $until, PDO::PARAM_STR);
	}
	$db->execute();
	$data = [];
	while($row = $db->fetch(PDO::FETCH_ASSOC)) {
		$data[$row['seq']] = $row;
	}
	return $data;
}

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
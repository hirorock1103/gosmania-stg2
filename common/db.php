<?php
include("db_config.php");


/***************************************/
// マスタDB接続
/***************************************/
function connect(){
	$con = null;
	try { 
		$dsn = 'mysql:host='.DB_HOST.';dbname='.DB_MASTER_NAME.';charset='.DB_CHARSET;
		$options = array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_STRINGIFY_FETCHES => false
		);
		$con = new PDO($dsn, DB_MASTER_USER, DB_MASTER_PASSWD, $options);
	} catch (Exception $e) {
		echo $e->GetMessage();
		$logger = new Logger();
		$logger->log($e);
		exit;
	}
	return $con;
}


// PDO接続処理
$dbh = connect();

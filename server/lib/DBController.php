<?php

/**
* 数据库的连接类
*/
class DBController
{
	private $serverName = '127.0.0.1';
	private $userName = 'root';
	private $password = 'lwy5721096';
	private $mysqlDatabase = 'campus';
	private $dbConnection = null;

	function __construct()
	{
		# constructor
	}

	public function connDatabase(){
		$this->dbConnection = mysqli_connect($this->serverName, $this->userName, $this->password);
		if(!$this->dbConnection){
			die(mysqli_connect_error());
		}else{
			mysqli_query($this->dbConnection,'set names utf8');
			mysqli_select_db($this->dbConnection, $this->mysqlDatabase);
		}
	}

	public function disConnDatabase(){
		if($this->dbConnection != null){
			mysqli_close($this->dbConnection);
			return TRUE;
		}else{
			return FALSE;
		}
	}

	public function getErrorCode(){
		die(mysqli_error($this->dbConnection));
	}

	public function getConnObject(){
		return $this->dbConnection;
	}
}

?>
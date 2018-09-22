<?php
include 'DBController.php';

/**
* 
*/
class TransController
{
	

	private $longitude;
	private $latitude;
	private $stopID;
	private $schoolID;
	private $openID;
	private $DBController;

	private $curTime;


	function __construct()
	{
		$this->DBController = new DBController();
		$this->DBController->connDatabase();
	}

	public function getBusStopInfoByID(){
		$this->schoolID = $_GET['school_id'];

		$sql = "SELECT stop, route, position, longitude, latitude FROM stop WHERE school_id = ?";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());


		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "i", $this->schoolID);

			// 执行查询
			mysqli_stmt_execute($stmt);

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			echo json_encode($retValue, JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		}

		// 断开与数据库的连接
		$this->DBController->disConnDatabase();
	}

	public function getBusStopInfoByLocation(){
		$this->schoolID = $_GET['good_id'];
		$this->longitude = $_GET['longitude'];
		$this->latitude = $_GET['latitude'];

		// 按照经纬度计算距离，并排序
		$sql = "SELECT stop, route, position, longitude, latitude, 
				ROUND(6378.138*2*ASIN(SQRT(POW(SIN(((?)*PI()/180-latitude*PI()/180)/2),2)+COS((?)*PI()/180)*COS(latitude*PI()/180)*POW(SIN(((?)*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance 
				FROM stop_route WHERE school_id = ? ORDER BY distance ASC";

	    // 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());
        
        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "dddi", $this->latitude, $this->latitude, $this->longitude, $this->schoolID);   

			// 执行查询
			mysqli_stmt_execute($stmt);

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);  

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);   

			// 返回结果
			echo json_encode($retValue, JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);	
        }

		// 断开与数据库的连接
		$this->DBController->disConnDatabase();
	}


	public function getBusStopCardInfo(){
		$this->schoolID = $_GET['school_id'];
		$this->stopID = $_GET['stop_id'];



	}




}

?>
<?php
include_once '../../lib/DBController.php';

/**
* zz的校车查询模块控制类
*/
class TransController
{
	
	// 经纬度坐标信息
	private $longitude;
	private $latitude;

	// 车站的唯一标识符
	private $stationID;

	// 路线的唯一标识符
	private $routeID;

	// 时刻表的唯一标识符
	private $patternID;

	// 学校的唯一标识符
	private $schoolID;

	// 用户身份标识符 openid
	private $openID;

	// 数据库控制类
	private $DBController;

	// 当前要查询的日期
	private $curDate;

	// 当前要查询的具体时刻
	private $curTime;

	// 鸡肋构造函数
	function __construct()
	{
		$this->DBController = new DBController();
		$this->DBController->connDatabase();
	}


	// 初始化若没有获得用户的gps信息，则根据学校返回所有站点的信息
	public function getBusStationInfoByID(){

		$this->schoolID = $_GET['school_id'];

		$sql = "SELECT station_id, station_name FROM station WHERE school_id = ?";

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


	// 初始化根据用户的gps获取站点信息获取距离最近的站点信息
	public function getBusStationInfoByLocation(){
		$this->schoolID = $_GET['school_id'];
		$this->longitude = $_GET['longitude'];
		$this->latitude = $_GET['latitude'];

		// 按照经纬度计算距离，并排序
		$sql = "SELECT station_id, station_name, 
				ROUND(6378.138*2*ASIN(SQRT(POW(SIN(((?)*PI()/180-latitude*PI()/180)/2),2)+COS((?)*PI()/180)*COS(latitude*PI()/180)*POW(SIN(((?)*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance 
				FROM station WHERE school_id = ? ORDER BY distance ASC";

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


	// 根据station_id来获取从该station出发的所有的路线
	public function getRouteInfoByTime(){
		$this->stationID = $_GET['station_id'];
		$this->curDate = $_GET['cur_date'];
		$this->curTime = $_GET['cur_time'];

		// 一条及其复杂的sql语句
		$sql = "SELECT * FROM 
		        (SELECT route_id, pattern_id FROM date_pattern WHERE from_data <= (?) AND (?) <= to_date AND station_id = (?)) 
		        INNER JOIN 
		        (SELECT route_id, end_station FROM route NATURAL JOIN stop WHERE station_id = (?) AND NOT end_station = (?)) 
		        NATURAL JOIN 
		        (SELECT * FROM schedule WHERE time >= (?)) ORDER BY time ASC";

	    // 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());
        
        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "ssiiis", $this->curDate, $this->curDate, $this->stationID, $this->stationID, $this->stationID, $this->curTime);   

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


	// 用来获取二级页面的车站信息
	public function getRouteDetailByID(){
		$this->stationID = $_GET['station_id'];
		$this->routeID = $_GET['route_id'];
		$this->patternID = $_GET['pattern_id'];


		$schedule = NULL;
		$routeInfo = NULL;
		/**************************************************************
		 * 先获取此线路的完整时刻表信息，然后获取车站信息包括停靠站点、经纬度信息
		 * 这里需要使用多SQL查询     过程化风格
		 **************************************************************/

		// 第一条SQL实现时刻表查询
		$sql = "SELECT time FROM schedule WHERE pattern_id = ' . $this->patternID . ' ORDER BY time ASC;" 
			
		// 第二条SQL实现停靠站点和GPS信息查询	
		$sql .=	"SELECT station_id, stop_name, longitude, latitude FROM stop WHERE station_id = ' . $this->stationID . ' AND route_id = ' . $this->routeID . ' ORDER BY position ASC";

		if(mysqli_multi_query($this->DBController->getConnObject(), $sql)) {

			do {

				if($result = mysqli_store_result($this->DBController->getConnObject())){
					if($schedule == NULL){
						$schedule = mysqli_fetch_all($result, MYSQLI_ASSOC);
					}else{
						$routeInfo = mysqli_fetch_all($result, MYSQLI_ASSOC);
					}
				}

			} while(mysqli_next_result($this->DBController->getConnObject()));

		}else{

			$this->DBController->getErrorCode();

		}

		$retVal = array('schedule' => $schedule, 'route_info' => $routeInfo);

		echo json_encode($retVal,  JSON_UNESCAPED_UNICODE);

		// 断开与数据库的连接
		$this->DBController->disConnDatabase();

	}


	// 获取某一学校一定时间范围内的特殊日子
	public function getSpecialDateByID(){
		$this->schoolID = $_GET['school_id'];

		// 获取系统的当前日期
		$systemDate = date('Y-m-d');

		// 获取下个月最后一天的日期
		$nextMonthLastDay = date('Y-m-d',strtotime(date('Y-m-1',strtotime('next month')).'+1 month -1 day'));

		$sql = "SELECT day, descr FROM spec_day WHERE (?) <= day AND day >= (?)";

	    // 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());
        
        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "ss", $systemDate, $$nextMonthLastDay);   

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


}

?>
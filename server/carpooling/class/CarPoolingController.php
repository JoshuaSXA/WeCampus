<?php

include_once '../../lib/DBController.php';

/********************************************
 * 拼车模块控制类
 * 拼车case的状态： 0——正在拼车，1——拼车成功，2——创建者取消当前拼车  carpool_status
 * 拼车人的状态：0——已参与当前拼车，1——已退出当前拼车，2——被踢出     riding_status
 *******************************************/


error_reporting(E_ALL || ~E_NOTICE);

class CarPoolingController {
	
	private $shcoolID;

	private $openID;

	// 鸡肋构造函数
	function __construct() {

		date_default_timezone_set('Asia/Shanghai'); 
		$this->DBController = new DBController();
		$this->DBController->connDatabase();

		// 首先获取header中的内容
		$headers = apache_request_headers();
		// 提取school_id字段的内容
		if(array_key_exists('school_id', $headers)) {

			$this->schoolID = $headers['school_id'];

		}

		// 获取用户的openid
		$this->openID = $_REQUEST['open_id'];
	}

	// 断开与数据库的连接
	public function closeDBConnection() {
		// 断开与数据库的连接
		$this->DBController->disConnDatabase();
	}


	// 获取所有的乘车地点
	public function getAllPickUpPlaces() {

		$sql = "SELECT place_id, place_name FROM pick_up_place WHERE school_id = (?)";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "i", $this->schoolID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {
				echo json_encode(array("success" => FALSE, "places" => array()));
				return;
			}

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			echo json_encode(array("success" => TRUE, "places" => $retValue), JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {

        	//echo $this->DBController->getErrorCode();
        	echo json_encode(array("success" => FALSE, "places" => array()));

        }

	}

	// 对于一个拼车者来说，我们要检查是否与其当前拼车有冲突
	private function checkCarPoolingCollision($startTime, $endTime) {

		$sql = "SELECT carpool_id FROM carpool_case WHERE creator=(?) AND (start_time BETWEEN (?) AND (?) OR end_time BETWEEN (?) AND (?))";

	    // 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());
        
        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "sssss", $this->openID, $startTime, $endTime, $startTime, $endTime);   

			// 执行查询
			mysqli_stmt_execute($stmt);

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);  

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC); 

			if(count($retValue) == 0) {
				// 没有发生冲突
				return array("success" => TRUE, "collide" => FALSE);

			} else {
				// 与当前拼车发生冲突
				return array("success" => TRUE, "collide" => TRUE);

			}

			// 返回结果
			echo json_encode($retValue, JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);	

        } else {

        	return array("success" => FALSE, "collide" => FALSE);

        }

	}

	// 创建一个出发的case
	public function createCarPoolingCase() {

		// 拼车出发地
		$startPlace = $_REQUEST['start_place'];
		// 拼车目的地
		$endPlace = $_REQUEST['end_place'];
		// 拼车时间段，开始时间
		$startTime = $_REQUEST['start_time'];
		// 拼车时间段，结束时间
		$endTime = $_REQUEST['end_time'];
		// 当前拼车人数
		$curNum = $_REQUEST['cur_num'];
		// 最大拼车人数
		$maxNum = $_REQUEST['max_num'];

		// 首先要检查是否与现有拼车产生冲突
		$checkCollision = $this->checkCarPoolingCollision($startTime, $endTime);

		if($checkCollision['success']) {

			if($checkCollision['collide']) {

				echo json_encode(array("success" => FALSE, "collide" => TRUE));
				return;

			}


		} else {

			echo json_encode(array("success" => FALSE, "collide" => FALSE));

			return;

		}

		// 发起一个新的拼车时，默认case的状态是0，即正在拼车

		$sql = "INSERT INTO carpool_case (creator, estab_time, start_place, end_place, start_time, end_time, cur_num, max_num) VALUES((?), NOW(), (?), (?), (?), (?), (?), (?))";

		$stmt = mysqli_stmt_init($this->DBController->getConnObject());
        
        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "siissii", $this->openID, $startPlace, $endPlace, $startTime, $endTime, $curNum, $maxNum);   

			// 执行查询
			if(!mysqli_stmt_execute($stmt)){
				// 查询失败
				echo json_encode(array("success" => FALSE, "collide" => FALSE));
				return;

			}

			// 查询成功，返回结果
			echo json_encode(array("success" => TRUE, "collide" => FALSE));

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);	

        } else {

        	echo json_encode(array("success" => FALSE, "collide" => FALSE));
        	
        }		

	}


    /*已修改*/
	// 查询拼车列表，注意这里需要分页查询
	public function getCarPoolingCaseList() {

		// 这里需要提供case_id作为threshhold，初始化为0
		$preCaseID = $_GET['page_border'];

		$passengerNum = $_GET['passenger_num'];

		$ridingTime = $_GET['riding_time'];

		$startPlace = $_GET['start_place'];

		$endPlace = $_GET['end_place'];

		$sql = "SELECT a.carpool_id, b.nickname, b.avatar, a.estab_time, a.start_place, a.end_place, a.start_time, a.end_time, a.cur_num, a.max_num, EXISTS(SELECT * FROM passenger AS c WHERE c.carpool_id = a.carpool_id AND c.open_id = (?) AND c.riding_status = 2) AS participant FROM (SELECT * FROM carpool_case AS d WHERE d.start_place = (?) AND d.end_place = (?) AND d.carpool_status = 0 AND ((?) BETWEEN d.start_time AND d.end_time) AND ((?) <= d.max_num - d.cur_num)) AS a, user AS b WHERE a.creator = b.open_id AND ((?) NOT IN (SELECT e.open_id FROM passenger AS e WHERE e.carpool_id = a.carpool_id AND e.riding_status = 0)) AND a.carpool_id > (?) ORDER BY a.carpool_id LIMIT 10";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "siisisi", $this->openID, $startPlace, $endPlace, $ridingTime, $passengerNum, $this->openID, $preCaseID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				echo json_encode(array("success" => FALSE, "page_data" => array()));
				return;
			}

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			echo json_encode(array("success" => TRUE, "page_data" => $retValue), JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {

        	//echo json_encode(array("success" => FALSE, "page_data" => array()));
        	echo mysqli_error($this->DBController->getConnObject());

        }
	}


	// 扩展时间段后的查询，注意这里需要分页查询
	public function getExpandedCarPoolingCaseList() {

		// 这里需要提供case_id作为threshhold，初始化为0
		$preCaseID = $_GET['page_border'];

		$passengerNum = $_GET['passenger_num'];

		$ridingTime = $_GET['riding_time'];

		$timeSpan = $_GET['time_span'];

		$startPlace = $_GET['start_place'];

		$endPlace = $_GET['end_place'];


		// 根据范围计算时间
		$minTime = date('Y-m-d H:i:s', strtotime($ridingTime . ' - ' . (string)$timeSpan . ' hours'));

		$maxTime = date('Y-m-d H:i:s', strtotime($ridingTime . ' + ' . (string)$timeSpan . ' hours'));


		$sql = "SELECT a.carpool_id, b.nickname, b.avatar, a.estab_time, a.start_place, a.end_place, a.start_time, a.end_time, a.cur_num, a.max_num, EXISTS(SELECT * FROM passenger AS c WHERE c.carpool_id = a.carpool_id AND c.open_id = (?) AND c.riding_status = 2) AS participant FROM (SELECT * FROM carpool_case AS d WHERE d.start_place = (?) AND d.end_place = (?) AND d.carpool_status = 0 AND (d.start_time BETWEEN (?) AND (?)) AND ((?) <= d.max_num - d.cur_num)) AS a, user AS b WHERE a.creator = b.open_id AND ((?) NOT IN (SELECT e.open_id FROM passenger AS e WHERE e.carpool_id = a.carpool_id AND e.riding_status = 0)) AND a.carpool_id > (?) ORDER BY a.carpool_id LIMIT 10";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "siissisi", $this->openID, $startPlace, $endPlace, $minTime, $maxTime, $passengerNum, $this->openID, $preCaseID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				echo json_encode(array("success" => FALSE, "page_data" => array()));
				return;
			}

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			echo json_encode(array("success" => TRUE, "page_data" => $retValue), JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {

        	//echo json_encode(array("success" => FALSE, "page_data" => array()));
        	echo mysqli_error($this->DBController->getConnObject());

        }
	}


	/*已修改*/
	// 查询拼车和我的拼车的二级页面，拼车详情
	public function getCarPoolDetail() {

		$carPoolCaseID = $_GET['carpool_id'];
        
		$detailInfo = NULL;

		$sql = "SELECT carpool_id, creator, nickname, avatar, phone, auth, estab_time, start_time, end_time, cur_num, max_num, carpool_status, tip, (SELECT place_name FROM pick_up_place AS m WHERE m.place_id = start_place) AS start_name, (SELECT place_name FROM pick_up_place AS n WHERE n.place_id = end_place) AS end_name, CASE creator WHEN " . "'" . $this->openID . "'" . " THEN 0 ELSE (SELECT k.riding_status FROM passenger k WHERE k.open_id = " . "'" . $this->openID . "'" . " AND k.carpool_id=" . $carPoolCaseID . ") END AS riding_status FROM carpool_case a JOIN user b ON a.creator = b.open_id WHERE carpool_id = " . $carPoolCaseID . ";";

		$sql .= "SELECT open_id, nickname, avatar, phone, auth FROM passenger NATURAL JOIN user WHERE carpool_id = " . $carPoolCaseID . " AND riding_status = 0";

		if (mysqli_multi_query($this->DBController->getConnObject(), $sql)) {

			do {

				if ($result = mysqli_store_result($this->DBController->getConnObject())) {
					if($detailInfo == NULL){

						$detailInfo = mysqli_fetch_all($result, MYSQLI_ASSOC);


					}else{

						$detailInfo['passenger'] = mysqli_fetch_all($result, MYSQLI_ASSOC);

					}

					mysqli_free_result($result);
				}

			} while(mysqli_next_result($this->DBController->getConnObject()));

		}else{

			echo mysqli_error($this->DBController->getConnObject());
			$retVal = array('success' => FALSE, 'detail' => array());

		}

		$retVal = array('success' => TRUE, 'detail' => $detailInfo);

		echo json_encode($retVal,  JSON_UNESCAPED_UNICODE);

	}




	/*已修改*/
	// 查询我的历史拼车记录，注意这里需要分页查询，即将开始的拼车
	public function getCarPoolingToStart() {

		$preTimeBound = $_GET['page_border'];

		$sql = "SELECT s.carpool_id, s.creator, s.estab_time, (SELECT place_name FROM pick_up_place m WHERE m.place_id=s.start_place) AS start_name, (SELECT place_name FROM pick_up_place n WHERE n.place_id=s.end_place) AS end_name, s.start_time, s.end_time, s.cur_num, s.max_num, s.carpool_status, CASE s.creator WHEN (?) THEN 0 ELSE (SELECT k.riding_status FROM passenger k WHERE k.carpool_id = s.carpool_id AND k.open_id=(?)) END AS riding_status FROM ((SELECT * FROM carpool_case a WHERE a.creator = (?)) UNION (SELECT * FROM carpool_case b WHERE (?) IN (SELECT c.open_id FROM passenger c WHERE c.carpool_id = b.carpool_id))) AS s WHERE s.end_time >= (?) ORDER BY s.end_time ASC";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "sssss", $this->openID, $this->openID, $this->openID, $this->openID, $preTimeBound);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				echo json_encode(array("success" => FALSE, "page_data" => array()));
				return;
			}

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			echo json_encode(array("success" => TRUE, "page_data" => $retValue), JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {
			echo mysqli_error($this->DBController->getConnObject());
        	//echo json_encode(array("success" => FALSE, "page_data" => array()));

        }

	}


	// 查询我的历史拼车记录，注意这里需要分页查询，已经结束的拼车
	public function getEndedCarPoolingHistory() {

		$preTimeBound = $_GET['page_border'];

		$sql = "SELECT s.carpool_id, s.creator, s.estab_time, (SELECT place_name FROM pick_up_place m WHERE m.place_id=s.start_place) AS start_name, (SELECT place_name FROM pick_up_place n WHERE n.place_id=s.end_place) AS end_name, s.start_time, s.end_time, s.cur_num, s.max_num, s.carpool_status, CASE s.creator WHEN (?) THEN 0 ELSE (SELECT k.riding_status FROM passenger k WHERE k.carpool_id = s.carpool_id AND k.open_id=(?)) END AS riding_status FROM ((SELECT * FROM carpool_case a WHERE a.creator = (?)) UNION (SELECT * FROM carpool_case b WHERE (?) IN (SELECT c.open_id FROM passenger c WHERE c.carpool_id = b.carpool_id))) AS s WHERE s.end_time < (?) ORDER BY s.end_time DESC";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "sssss", $this->openID, $this->openID, $this->openID, $this->openID, $preTimeBound);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				echo json_encode(array("success" => FALSE, "page_data" => array()));
				return;
			}

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			echo json_encode(array("success" => TRUE, "page_data" => $retValue), JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {
			echo mysqli_error($this->DBController->getConnObject());
        	//echo json_encode(array("success" => FALSE, "page_data" => array()));

        }

	}




	// 返回认证信息
	public function getUserAuthInfo() {

		$sql = "SELECT (SELECT COUNT(*) FROM carpool_case WHERE creator = (?)) AS carpool_num, auth, phone FROM user WHERE open_id = (?)";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "ss", $this->openID, $this->openID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				echo json_encode(array("success" => FALSE, "auth_info" => array()));
				return;
			}

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			echo json_encode(array("success" => TRUE, "auth_info" => $retValue), JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {

        	echo json_encode(array("success" => FALSE, "auth_info" => array()));

        }
	}


	// 检查踢人者是不是创建者
	private function checkCreatorStatus($openID, $carPoolingID) {

		$sql = "SELECT carpool_id FROM carpool_case WHERE carpool_id = (?) AND creator = (?)";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "is", $carPoolingID, $openID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				return array("success" => FALSE, "creator" => FALSE);
				
			}

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			if(count($retValue) == 0) {

				return array("success" => TRUE, "creator" => FALSE);

			} else {

				return array("success" => TRUE, "creator" => TRUE);

			}
			

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {

        	return array("success" => FALSE, "creator" => FALSE);

        }

	}


	// 创建者踢人
	public function removePassengerFromCarPooling() {

		$carPoolingID = $_REQUEST['carpool_id'];

		$passengerOpenID = $_REQUEST['passenger_open_id'];

		// 我们首先要确认踢人者身份是不是拼车的创建者
		$checkCreatorRes = $this->checkCreatorStatus($this->openID, $carPoolingID);

		if($checkCreatorRes['success']){

			if(!$checkCreatorRes['creator']) {

				echo json_encode(array("success" => FALSE, "creator" => FALSE));
				return;

			} 

		} else {

			echo json_encode(array("success" => FALSE, "creator" => FALSE));
			return;
		}


		$sql = "UPDATE passenger SET riding_status = 2 WHERE carpool_id = (?) AND open_id = (?)";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "is", $carPoolingID, $passengerOpenID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				echo json_encode(array("success" => FALSE, "creator" => TRUE));
				return;
			}

			// 返回结果
			echo json_encode(array("success" => TRUE, "creator" => TRUE));

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {

        	echo json_encode(array("success" => FALSE, "creator" => TRUE));

        }

	}


	// 创建者解散当前拼车
	public function dissolveCarPoolingCase() {

		$carPoolingID = $_REQUEST['carpool_id'];

		$sql_1 = "UPDATE carpool_case SET carpool_status = 2 WHERE carpool_id = " . $carPoolingID . " AND creator = " . "'" . $this->openID . "'";
		$sql_2 = "UPDATE passenger SET riding_status = 1 WHERE carpool_id = " . $carPoolingID ." AND riding_status = 0";

		mysqli_autocommit($this->DBController->getConnObject(), FALSE);

		$allQuerySucceed = TRUE;	

		mysqli_query($this->DBController->getConnObject(), $sql_1) ? NULL : $allQuerySucceed = FALSE;
		mysqli_query($this->DBController->getConnObject(), $sql_2) ? NULL : $allQuerySucceed = FALSE;

		if ($allQuerySucceed) {

			mysqli_commit($this->DBController->getConnObject());

			echo json_encode(array("success" => TRUE));

		} else {

			mysqli_rollback($this->DBController->getConnObject());

			echo json_encode(array("success" => FALSE));
			//echo mysqli_error($this->DBController->getConnObject());

		}

	}	


	// 拼车者主动退出当前拼车
	public function quitCarPoolingCase() {

		$carPoolingID = $_REQUEST['carpool_id'];

		$sql = "UPDATE passenger SET riding_status = 1 WHERE carpool_id = (?) AND open_id = (?)";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "is", $carPoolingID, $this->openID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				echo json_encode(array("success" => FALSE));
				return;
			}

			// 返回结果
			echo json_encode(array("success" => TRUE));

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {

        	echo json_encode(array("success" => FALSE));

        }
	}


	// 查询用户是否是退出拼车的
	private function checkIfQuitCarpooling($carPoolingID) {

		$sql = "SELECT COUNT(*) AS num FROM passenger WHERE carpool_id = (?) AND open_id = (?)";


		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "is", $carPoolingID, $this->openID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				return array("success" => FALSE, "quit" => FALSE);
				
			}

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			if($retValue[0]['num'] == 0) {

				return array("success" => TRUE, "quit" => FALSE);

			} else {

				return array("success" => TRUE, "quit" => TRUE);

			}
			
			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

		} else {

        	return array("success" => FALSE, "quit" => FALSE);

        }

	}


    /*已修改*/
	// 加入当前拼车
	public function joinCarPoolingCase() {

		$carPoolingID = $_REQUEST['carpool_id'];

		// 检查用户是否曾经参与过此次拼车，后退出
		$ifQuitCarpooling = $this->checkIfQuitCarpooling($carPoolingID);

		mysqli_autocommit($this->DBController->getConnObject(), FALSE);

		$sql_1 = '';

		$sql_2 = "UPDATE carpool_case SET cur_num = cur_num + 1 WHERE carpool_id = " . $carPoolingID;

		if ($ifQuitCarpooling['success'] == FALSE) {

			echo json_encode(array("success" => FALSE));
			return;

		} else {

			if ($ifQuitCarpooling['quit']) {

				$sql_1 = "UPDATE passenger SET riding_status = 0 WHERE carpool_id = " . $carPoolingID . " AND open_id = " . "'" . $this->openID . "'";

			} else {

				$sql_1 = "INSERT INTO passenger (carpool_id, open_id, riding_status) VALUES ($carPoolingID, '$this->openID', 0)";
			}
		}

		$allQuerySucceed = TRUE;

		mysqli_query($this->DBController->getConnObject(), $sql_1) ? NULL : $allQuerySucceed = FALSE;
		mysqli_query($this->DBController->getConnObject(), $sql_2) ? NULL : $allQuerySucceed = FALSE;

		if ($allQuerySucceed) {

			mysqli_commit($this->DBController->getConnObject());

			echo json_encode(array("success" => TRUE));

		} else {

			mysqli_rollback($this->DBController->getConnObject());

			echo json_encode(array("success" => FALSE));

		}

	}


	// 获取某个拼车case的乘客列表
	





}

?>
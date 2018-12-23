<?php

include_once '../../lib/DBController.php';


/********************************************
 * 拼车模块控制类
 * 拼车case的状态： 0——正在拼车，1——拼车成功，2——创建者取消当前拼车
 * 拼车人的状态：0——已参与当前拼车，1——已退出当前拼车，2——被踢出
 *******************************************/

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
			mysqli_stmt_bind_param($stmt, "i", $this->school_id);

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

	// 查询拼车列表，注意这里需要分页查询
	public function getCarPoolingCaseList() {

		// 这里需要提供case_id作为threshhold，初始化为0
		$preCaseID = $_GET['page_border'];

		$passengerNum = $_GET['passenger_num'];

		$ridingTime = $_GET['riding_time'];

		$startPlace = $_GET['start_place'];

		$endPlace = $_GET['end_place'];

		$sql = "SELECT carpool_id, nickname, avatar, estab_time, start_place, end_place, start_time, end_time, cur_num, max_num FROM (SELECT * FROM carpool_case WHERE start_place = (?) AND end_place = (?) AND carpool_status = 0 AND ((?) BETWEEN start_time AND end_time) AND ((?) <= max_num - cur_num)) AS a JOIN user AS b ON a.creator = b.open_id WHERE carpool_id > (?) ORDER BY carpool_id LIMIT 10";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "iisii", $startPlace, $endPlace, $ridingTime, $passengerNum, $preCaseID);

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

        	echo json_encode(array("success" => FALSE, "page_data" => array()));

        }
	}


	// 查询拼车的二级页面，拼车详情
	public function getCarPoolDetail() {

		$carPoolCaseID = $_GET['carpool_id'];

		$sql_1 = "SELECT s.carpool_id, s.creator, s.nickname, s.avatar, s.phone, s.auth, s.estab_time, (SELECT place_name FROM pick_up_place AS m WHERE m.place_id = s.start_place) AS start_name, (SELECT place_name FROM pick_up_place AS n WHERE n.place_id = s.end_place) AS end_name, s.start_time, s.end_time, s.cur_num, s.max_num, s.carpool_status, EXISTS(SELECT * FROM passenger AS a WHERE a.carpool_id=(?) AND a.open_id=(?)) AS participant, (SELECT b.riding_status FROM passenger AS b WHERE b.carpool_id=(?) AND b.open_id=(?)) AS exit FROM (carpool_case JOIN user ON carpool_case.creator = user.open_id) AS s WHERE s.carpool_id = (?)";

		$sql_2 = "SELECT open_id, nickname, avatar, phone, auth FROM passenger NATURAL JOIN user WHERE carpool_id = (?) AND riding_status = 0";

		// 关闭数据库的自动提交功能，保持原子性
		mysqli_autocommit($this->DBController->getConnObject(), FALSE);

		// 创建预处理语句
		$stmt_1 = mysqli_stmt_init($this->DBController->getConnObject());
		$stmt_2 = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt_1, $sql_1) && mysqli_stmt_prepare($stmt_2, $sql_2)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt_1, "isisi", $carPoolCaseID, $this->openID, $carPoolCaseID, $this->openID, $carPoolCaseID);  

			mysqli_stmt_bind_param($stmt_2, "s", $carPoolCaseID); 
			// 执行查询
			$res_1 = mysqli_stmt_execute($stmt_1);
			$res_2 = mysqli_stmt_execute($stmt_2);
	
			if($res_1 && $res_2) {

				mysqli_commit($this->DBController->getConnObject());

				// 获取查询结果
				$result_1 = mysqli_stmt_get_result($stmt_1);
				$result_2 = mysqli_stmt_get_result($stmt_2);
				// 获取值
				$retValue_1 =  mysqli_fetch_all($result_1, MYSQLI_ASSOC);
				$retValue_2 =  mysqli_fetch_all($result_2, MYSQLI_ASSOC);

				$retArray = $retValue_1[0];
				$retArray['passenger'] = $retValue_2;

				echo json_encode(array("success" => TRUE, 'detail' => $retArray), JSON_UNESCAPED_UNICODE);

			} else {

				mysqli_rollback($this->DBController->getConnObject());
				echo json_encode(array("success" => FALSE, 'detail' => array()));

			}

			// 释放结果
			mysqli_stmt_free_result($stmt_1);
			mysqli_stmt_free_result($stmt_2);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt_1);
			mysqli_stmt_close($stmt_2);
			
        } else {

        	echo json_encode(array("success" => FALSE, 'detail' => array()));
        	
        }
	}


	// 查询我的历史拼车记录，注意这里需要分页查询
	public function getMyCarPoolingHistory() {

		$preCaseID = $_GET['page_border'];

		$sql = "SELECT k.carpool_id, (SELECT place_name FROM pick_up_place AS a WHERE a.place_id = k.start_place) AS start_name, (SELECT place_name FROM pick_up_place AS b WHERE b.place_id = k.end_place) AS end_name, k.start_time, k.end_time, k.cur_num, k.max_num, k.carpool_status FROM (carpool_case NATURAL JOIN (SELECT s.carpool_id FROM passenger AS s WHERE s.open_id =(?))) AS k WHERE k.carpool_id > (?) ORDER BY k.carpool_id LIMIT 10";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "si", $this->openID, $preCaseID);

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

        	echo json_encode(array("success" => FALSE, "page_data" => array()));

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



		$sql = "UPDATE passenger SET riding_status = 1 WHERE carpool_id = (?) AND open_id = (?)";

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

/*
	// 创建者解散当前拼车
	public function dissoveCarPoolingCase() {

		$carPoolingID = $_REQUEST['carpool_id'];

		$sql_1 = "UPDATE carpool_case SET carpool_status = 1";


	}	
*/

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


	public function joinCarPoolingCase() {





	}

}

?>
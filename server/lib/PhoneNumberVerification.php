<?php
include_once 'GlobalVar.php';
include_once 'DBController.php';
include_once 'qcloudsms_php/src/index.php';

use Qcloud\Sms\SmsSingleSender;

/**
 * 短信验证控制类，负责验证用户的手机号
 */

class PhoneNumberVerificationController {

	private $redis;

	private $openID;
	
	function __construct() {
		// 设置时区为上海时间
		date_default_timezone_set('Asia/Shanghai'); 

		// 创建数据库连接控制类
		$this->DBController = new DBController();
		// 连接数据库
		$this->DBController->connDatabase();

		// 实例化一个redis类
		$this->redis = new Redis();
		// 连接到redis_server，端口号为6379， timeout为10s
		$this->redis->connect('127.0.0.1', 6379, 10);

	}


	public function sendVerificationMessage() {

		$this->openID = $_REQUEST['open_id'];

		$phoneNumber = $_REQUEST['phone_number'];

		// 首先要检查该用户今天发送的短信是否超过限制，默认一天最多不超过5条
		$timesIndex = $this->openID . "_times";

		$retVal = $this->redis->get($timesIndex);

		if($retVal) {
			

			// 今日用户已经验证过
			if ((int)$retVal > 5) {

				echo json_encode(array("success"=>FALSE, "time_exceeded"=>TRUE, "err_code"=>-1));

				return;

			} else {

				$retVal += 1;

				$this->redis->set($timesIndex, $retVal);

			}

		} else {

			// 今日用户还未验证
			$this->redis->set($timesIndex, 1);
			// 设置过期时间
			$expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
        	// 设置键的过期时间
			$this->redis->expireAt($timesIndex, $expireTime); 

		}

		global $smsAppID, $smsAppKey, $smsTemplateId, $smsSign, $smsValidTime;

		// 随机生成四位验证码
		$verCode = (string)rand(999, 9999);

		try {
    		$ssender = new SmsSingleSender($smsAppID, $smsAppKey);
    		$params = [$verCode, $smsValidTime];
    		$result = $ssender->sendWithParam("86", $phoneNumber, $smsTemplateId, $params, $smsSign, "", "");
    		$rsp = json_decode($result, TRUE);

    		if($rsp["result"] != 0) {
    			echo json_encode(array("success"=>FALSE, "time_exceeded"=>FALSE, "err_code"=>$rsp["result"]));
    			return;
    		}
    		
		} catch(\Exception $e) {
    		//echo var_dump($e);
    		echo json_encode(array("success"=>FALSE, "time_exceeded"=>FALSE, "err_code"=>-1));
    		return;
		}

		$storedHash = json_encode(array("ver_code"=>$verCode, "phone_number"=>$phoneNumber));

		// 存入redis缓存
		$this->redis->setex($this->openID, (int)$smsValidTime * 60, $storedHash);

		echo json_encode(array("success"=>TRUE, "time_exceeded"=>FALSE, "err_code"=>0));

	}


	private function updateUserPhoneNumber($openID, $phoneNumber) {

		$sql = "UPDATE user SET phone = (?) WHERE open_id = (?)";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

		if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "ss", $phoneNumber, $openID);

			// 执行查询
			if(!mysqli_stmt_execute($stmt)) {

				return FALSE;
		
			}

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

			return TRUE;

		} else {

        	return FALSE;

        }

	}


	public function checkVerificationCode() {

		$this->openID = $_REQUEST['open_id'];

		$verCode = $_REQUEST['ver_code'];

		$retVal = $this->redis->get($this->openID);

		if($retVal) {

			$retVal = json_decode($retVal, TRUE);

		} else {

			echo json_encode(array("success" => FALSE));
			return;

		}

		if($retVal['ver_code'] != $verCode) {

			echo json_encode(array("success" => FALSE));
			return;

		}

		if($this->updateUserPhoneNumber($this->openID, $retVal['phone_number'])) {

			echo json_encode(array("success" => TRUE));
			return;

		}

		echo json_encode(array("success" => FALSE));

	}


	public function closeService() {

		// 断开与数据库的连接
		$this->DBController->disConnDatabase();	

		// 断开与redis_server的连接
		$this->redis->close();

	}

}


?>
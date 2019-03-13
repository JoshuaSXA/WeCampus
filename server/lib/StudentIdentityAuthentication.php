<?php
include_once 'DBController.php';
include_once 'UploadImageController.php';


/**
 *  身份认证类 
 *  负责实现用户的学生身份的认证
 *  约定认证的四个状态：0——未认证、1——已认证、2——正在认证、3——认证失败
 */

class StudentIdentityAuthentication {

	private $openID;

    // 临时缓存路径
    private $cachePath = '../data/cache/';

    // 图片的保存路径
    private $savePath = '../data/card/';

	// StudentIdentityAuthentication类的构造函数
	function __construct() {

		// 设置时区
		date_default_timezone_set('Asia/Shanghai'); 
		
		// 创建数据库接口类 DBController
		$this->DBController = new DBController();
		// 连接数据库
		$this->DBController->connDatabase();

		// 提前获取用户的open_id
		$this->openID = $_REQUEST['open_id']; 
		//echo $this->openID;
	}

	// 用户上传认证信息
	public function uploadAuthInfor() {

		$name = $_REQUEST['name'];

		$studentID = $_REQUEST['student_id'];

		echo $studentID;

		// 实例化图片上传类
		$uploadImageControllerObj = new UploadImageController();

		// 设置图片存储路径
		$uploadImageControllerObj->setSavePath($this->cachePath);

		// 设置图片压缩程度
		$uploadImageControllerObj->setCompressValue(80);

		$imgName = 'card_' . $this->openID . '_' . time() . '.jpg';

		// 上传图片
		if(!$uploadImageControllerObj->uploadImg('card', $imgName)) {

			// 上传失败的处理
			echo json_encode(array('success' => FALSE, 'temp_url' => ''));
			// 断开与数据库的连接
			$this->DBController->disConnDatabase();
			return;

		} 

		// 关闭数据库的自动提交功能
		mysqli_autocommit($this->DBController->getConnObject(), FALSE);

		$sql_1 = "INSERT INTO student_auth (begin_time, open_id, name, student_id, card) VALUES(NOW(), (?), (?), (?), (?))";
		$sql_2 = "UPDATE user SET auth=2 WHERE open_id=(?)";
		// 创建预处理语句
		$stmt_1 = mysqli_stmt_init($this->DBController->getConnObject());
		$stmt_2 = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt_1, $sql_1) && mysqli_stmt_prepare($stmt_2, $sql_2)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt_1, "ssss", $this->openID, $name, $studentID, $imgName);  

			mysqli_stmt_bind_param($stmt_2, "s", $this->openID); 
			// 执行查询
			$res_1 = mysqli_stmt_execute($stmt_1);
			$res_2 = mysqli_stmt_execute($stmt_2);
	
			if($res_1 && $res_2) {

				mysqli_commit($this->DBController->getConnObject());
				echo json_encode(array("success" => TRUE, 'temp_url' => $imgName));

			} else {

				mysqli_rollback($this->DBController->getConnObject());
				echo json_encode(array("success" => FALSE, 'temp_url' => ''));

			}

			// 释放结果
			mysqli_stmt_free_result($stmt_1);
			mysqli_stmt_free_result($stmt_2);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt_1);
			mysqli_stmt_close($stmt_2);
			
        } else {

        	echo json_encode(array("success" => FALSE, 'temp_url' => ''));
        	
        }	

        $this->DBController->disConnDatabase();	
	}


}




?>
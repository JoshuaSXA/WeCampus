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
	}

	// 用户上传认证信息
	public function uploadAuthInfor() {

		$name = $_REQUEST['name'];

		$studentID = $_REQUEST['student_id'];

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

		$sql = "INSERT INTO student_auth (open_id, name, student_id, card) VALUES((?), (?), (?), (?))";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "ssss", $this->openID, $name, $studentID, $imgName);   
			// 执行查询
			
			if(mysqli_stmt_execute($stmt)) {

				echo json_encode(array('success' => TRUE, 'temp_url' => $imgName));

			} else {

				echo json_encode(array("success" => FALSE, 'temp_url' => ''));

			}

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);
			
        } else {

        	echo json_encode(array("success" => FALSE));
        	
        }	

        $this->DBController->disConnDatabase();	
	}


}




?>
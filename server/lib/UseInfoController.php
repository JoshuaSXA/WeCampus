<?php
include_once 'DBController.php';

/**
* 用户信息控制类
* 包括基本的注册、信息的增删查改等功能
*/
class UserInfoController {

	// 用户的open_id
	private $openID;

	// 用户所在的学校的标识
	private $schoolID;

	// 用户的手机号
	private $phone;

	// 用户的学号
	private $studentID;

	// 用户的昵称
	private $nickname;

    // 用户的头像URL
    private $avatarUrl;

    // 用户的性别
    private $gender;

    // 用户的真实姓名
    private $name;

    // 缓存路径
    private $cachePath = '../data/cache/';

    // 图片的保存路径
    private $savePath = '../data/avatar/';

	// UserInfoController类的构造函数
	function __construct()
	{
		
		//date_default_timezone_set('Asia/Shanghai'); 

		// 创建数据库接口类 DBController
		$this->DBController = new DBController();
		// 连接数据库
		$this->DBController->connDatabase();

		// 提前获取用户的open_id
		$this->openID = $_REQUSET['open_id']； 
	}


	// 用户注册
	public function userRegister() {

		$this->schoolID = $_REQUSET['school_id'];

		$this->nickname = $_REQUSET['nickname'];

		$this->avatar = $_REQUSET['avatar'];

		$sql = "INSERT INTO user (open_id, school_id, nickname, avatar) VALUES((?), (?), (?), (?))";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "siss", $this->openID, $this->schoolID, $this->nickname, $this->avatar);   

			// 执行查询
			mysqli_stmt_execute($stmt);

			// 返回结果
			echo json_encode(array("success" => TRUE), JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);

			// 这里需要将用户上传的头像从cache文件夹中移出



        } else {

        	// echo $this->DBController->getErrorCode();

        	echo json_encode(array("success" => FALSE), JSON_UNESCAPED_UNICODE);
        	
        }	

        // 断开与数据库的连接
		$this->DBController->disConnDatabase();	

	}


	// 用户上传头像
	public uploadAvatar() {

		// 首先判断上传的文件是否出错
		if($_FILE['file']['error']) {

			echo json_encode(array("success" => FALSE, "errorMsg" => $_FILE['file']['error']), JSON_UNESCAPED_UNICODE);

		} else {
			$allowType = array('image/png', 'image/jpg', 'image/jpeg');
			// 判断文件类型
			if(!in_array($_FILE['file']['type'], $allowType)) {

				echo json_encode(array("success" => FALSE, "errorMsg" => 'Invalid image type'), JSON_UNESCAPED_UNICODE);

			} else {



			}

		}


	}





}



?>
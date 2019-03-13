<?php
include_once 'DBController.php';
include_once 'UploadImageController.php';
include_once 'CurlRequestController.php';

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
		$this->openID = $_REQUEST['open_id']; 
	}


	// 用户注册
	public function userRegister() {

		# $this->schoolID = $_REQUEST['school_id'];
		$this->schoolID = 1;

		$this->nickname = $_REQUEST['nickname'];

		$this->avatar = $_REQUEST['avatar'];

		// 保存到数据库中的文件名
		$imgName = '';

		if(strpos($this->avatar,'https') !==false) {

			// 如果是微信头像则生成新的头像名称

			$imgName = 'avatar_' . $this->openID . '_' . time() . '.jpg';

			if(!$this->downloadAvatar($this->avatar, $imgName)) {

				echo json_encode(array("success" => FALSE), JSON_UNESCAPED_UNICODE);

				return;

			} 

		} else {

			// 如果是用户上传的头像，则使用默认的

			$imgName = $this->avatar;

			// 这里需要将用户上传的头像从cache文件夹中移出
			$orgPath = $this->cachePath . $this->avatar;
			$targetPath = $this->savePath . $this->avatar;

			rename($orgPath, $targetPath);

		}

		$sql = "INSERT INTO user (open_id, school_id, nickname, avatar) VALUES((?), (?), (?), (?))";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "siss", $this->openID, $this->schoolID, $this->nickname, $imgName);   
			// 执行查询
			
			if(mysqli_stmt_execute($stmt)) {

				// 返回结果
				echo json_encode(array("success" => TRUE));

			} else {

				echo json_encode(array("success" => FALSE));

			}

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);
			
        } else {

        	echo json_encode(array("success" => FALSE));
        	
        }	

        // 断开与数据库的连接
		$this->DBController->disConnDatabase();	

	}


	// 用户上传头像
	public function uploadAvatar() {
		// 完整的路径，返回给前端
		//$serverCachePath = 'https://www.we-campus.cn/WeCampus/data/cache/';

		// 实例化图片上传类
		$uploadImageControllerObj = new UploadImageController();

		// 设置图片存储路径
		$uploadImageControllerObj->setSavePath('../data/cache/');

		// 设置图片压缩程度
		$uploadImageControllerObj->setCompressValue(60);

		// 图片名称
		$imgName = 'avatar_' . $this->openID . '_' . time() . '.jpg';

		// 上传图片
		if($uploadImageControllerObj->uploadImg('avatar', $imgName)) {

			echo json_encode(array('success' => TRUE, 'temp_url' => $imgName), JSON_UNESCAPED_UNICODE);

		} else {

			echo json_encode(array('success' => FALSE, 'temp_url' => ''), JSON_UNESCAPED_UNICODE);
		}

		return;
	}


	// 如果用户的头像是微信头像，则下载图片并保存到avatar文件夹
	private function downloadAvatar($avatarUrl, $imgName) {

		$curlRequestControllerObj = new CurlRequestController();

		if($curlRequestControllerObj->curlDownloadFile($avatarUrl, $this->savePath . $imgName)) {

			return TRUE;

		} else {

			return FALSE;
		}

	}


	// 获取最近的学校
	public function getTheNearestSchool() {

		$longitude = $_GET['longitude'];

		$latitude = $_GET['latitude'];

		// 按照经纬度计算距离，并排序
		$sql = "SELECT school_id, school_name, 
				ROUND(6378.138*2*ASIN(SQRT(POW(SIN(((?)*PI()/180-latitude*PI()/180)/2),2)+COS((?)*PI()/180)*COS(latitude*PI()/180)*POW(SIN(((?)*PI()/180-longitude*PI()/180)/2),2)))*1000) AS distance 
				FROM school NATURAL JOIN campus ORDER BY distance ASC LIMIT 1";

	    // 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());
        

        if(mysqli_stmt_prepare($stmt, $sql)){

			mysqli_stmt_bind_param($stmt, "ddd", $latitude, $latitude, $longitude);   
			   
			// 执行查询
			mysqli_stmt_execute($stmt);

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);  

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);  

			// 我们要根据学校的编号获取学校的logo
			$logoName = $retValue[0]['school_id'] . ".jpg";

			// 读取第一行数据
			$assocData = $retValue[0];

			// 返回结果
			echo json_encode(array('school_id' => $assocData['school_id'], 'school_name' => $assocData['school_name'], 'school_logo' => $logoName), JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);	
	
        } else {

        	echo $this->DBController->getErrorCode();

        }

		// 断开与数据库的连接
		$this->DBController->disConnDatabase();

	}

	public function getSchoolList() {

		$sql = "SELECT * FROM school ORDER BY school_name DESC";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

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
			
        } else {

        	echo $this->DBController->getErrorCode();
        	
        }	

        // 断开与数据库的连接
		$this->DBController->disConnDatabase();	

	}


	// 获取所有的用户信息
	public function getUserInfo() {
		// 获取的参量只有用户的openid

		$sql = "SELECT school_id, school_name, student_id, name, nickname, avatar, gender, phone FROM user NATURAL JOIN school WHERE open_id = (?)";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数
			mysqli_stmt_bind_param($stmt, "s", $this->openID);   
			// 执行查询
			mysqli_stmt_execute($stmt);

			// 获取查询结果
			$result = mysqli_stmt_get_result($stmt);

			// 获取值
			$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

			// 返回结果
			echo json_encode($retValue[0], JSON_UNESCAPED_UNICODE);

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);
			
        } else {

        	echo $this->DBController->getErrorCode();
        	
        }	

        // 断开与数据库的连接
		$this->DBController->disConnDatabase();	

	}


	// 负责修改用户的基本信息
	public function modifyUserInfo() {

		// 可供修改的用户信息有nickname、name、gender、student_id、school_id、phone
		$attributeIndexMap = array('nickname', 'name', 'gender', 'student_id', 'school_id', 'phone');
		$modifyAttributeMap = array();

		$sqlParameter = '';
		$sqlBindParaType = '';

		// 遍历map，将待修改的属性添加到数组中
		foreach ($attributeIndexMap as $attr) {
			if(!empty($_REQUEST[$attr])) {
				$sqlBindParaType .= ($attr == 'school_id' ? 'i' : 's');
				$sqlParameter .= ($attr . '=(?),'); 
				array_push($modifyAttributeMap, $attr);
			}
		}

		// 待修改的属性数量
		$modifyAttributeNum = count($modifyAttributeMap);
		if($modifyAttributeNum == 0) {
			// 没有需要修改的属性，nmsl
			echo json_encode(array('success' => TRUE));
			return;
		}

		// 去除最右侧一个逗号 
		$sqlParameter = rtrim($sqlParameter, ',');
		$sqlBindParaType .= 's';

		$sql = "UPDATE user SET " . $sqlParameter . " WHERE open_id=(?)";

		// 创建预处理语句
		$stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

			// 绑定参数，因为参数的数量是动态变化的，所以这里比较麻烦（留个坑）
			if($modifyAttributeNum == 1) {

				mysqli_stmt_bind_param($stmt, $sqlBindParaType, $_REQUEST[$modifyAttributeMap[0]], $this->openID);
				
			} elseif($modifyAttributeNum == 2) {

				mysqli_stmt_bind_param($stmt, $sqlBindParaType, $_REQUEST[$modifyAttributeMap[0]], $_REQUEST[$modifyAttributeMap[1]], $this->openID);

			} elseif($modifyAttributeNum == 3) {

				mysqli_stmt_bind_param($stmt, $sqlBindParaType, $_REQUEST[$modifyAttributeMap[0]], $_REQUEST[$modifyAttributeMap[1]], $_REQUEST[$modifyAttributeMap[2]], $this->openID);

			} elseif($modifyAttributeNum == 4) {

				mysqli_stmt_bind_param($stmt, $sqlBindParaType, $_REQUEST[$modifyAttributeMap[0]], $_REQUEST[$modifyAttributeMap[1]], $_REQUEST[$modifyAttributeMap[2]], $_REQUEST[$modifyAttributeMap[3]], $this->openID);

			} elseif($modifyAttributeNum == 5) {

				mysqli_stmt_bind_param($stmt, $sqlBindParaType, $_REQUEST[$modifyAttributeMap[0]], $_REQUEST[$modifyAttributeMap[1]], $_REQUEST[$modifyAttributeMap[2]], $_REQUEST[$modifyAttributeMap[3]], $_REQUEST[$modifyAttributeMap[4]], $this->openID);

			} elseif ($modifyAttributeNum == 6) {
				
				mysqli_stmt_bind_param($stmt, $sqlBindParaType, $_REQUEST[$modifyAttributeMap[0]], $_REQUEST[$modifyAttributeMap[1]], $_REQUEST[$modifyAttributeMap[2]], $_REQUEST[$modifyAttributeMap[3]], $_REQUEST[$modifyAttributeMap[4]], $_REQUEST[$modifyAttributeMap[5]], $this->openID);

			}

			// 执行查询
			$updateStatus = mysqli_stmt_execute($stmt);

			// 返回结果
			if($updateStatus) {

				echo json_encode(array('success' => TRUE));

			} else {

				echo json_encode(array('success' => FALSE));

			}

			// 释放结果
			mysqli_stmt_free_result($stmt);

			// 关闭mysqli_stmt类
			mysqli_stmt_close($stmt);
			
        } else {

        	echo json_encode(array('success' => FALSE));
        	
        }	

        // 断开与数据库的连接
		$this->DBController->disConnDatabase();
	}


	// 负责实现用户修改头像
	public function changeUserAvatar() {

		// 原始的用户的头像文件名
		$oldAvatarFileName = $_REQUEST['old_avatar'];

		// 实例化图片上传类
		$uploadImageControllerObj = new UploadImageController();

		// 设置图片存储路径
		$uploadImageControllerObj->setSavePath('../data/cache/');

		// 设置图片压缩程度
		$uploadImageControllerObj->setCompressValue(60);

		// 图片名称
		$imgName = 'avatar_' . $this->openID . '_' . time() . '.jpg';

		// 上传图片，并判断上传是否成功，如果失败则返回错误信息
		if(!$uploadImageControllerObj->uploadImg('avatar', $imgName)) {

			echo json_encode(array('success' => FALSE, 'avatar' => ''));

			$this->DBController->disConnDatabase();

			return;

		}

		// 这里需要将用户新上传的头像从cache文件夹中移出
		$orgPath = $this->cachePath . $imgName;
		$targetPath = $this->savePath . $imgName;

		// 将原始的文件从avatar文件夹中删除
		$deletePath = $this->savePath . $oldAvatarFileName;

		if(rename($orgPath, $targetPath)){

			unlink($deletePath);


			$sql = 'UPDATE user SET avatar=(?) WHERE open_id=(?)';

			$stmt = mysqli_stmt_init($this->DBController->getConnObject());

	        if(mysqli_stmt_prepare($stmt, $sql)){

				// 绑定参数
				mysqli_stmt_bind_param($stmt, "ss", $imgName, $this->openID);   
				// 执行查询
				mysqli_stmt_execute($stmt);

				if(mysqli_stmt_execute($stmt)) {

					// 更新成功
					echo json_encode(array("success" => TRUE, 'avatar' => $imgName));

				} else {
					// 更新失败
					echo json_encode(array("success" => FALSE, 'avatar' => ''));

				}

				// 释放结果
				mysqli_stmt_free_result($stmt);

				// 关闭mysqli_stmt类
				mysqli_stmt_close($stmt);
				
	        } else {

	        	//echo $this->DBController->getErrorCode();
	        	echo json_encode(array("success" => FALSE, 'avatar' => ''));
	        	
	        }	

		} else {

			echo json_encode(array('success' => FALSE, 'avatar' => ''));

		}

		// 断开与数据库的连接
		$this->DBController->disConnDatabase();

	}

}

?>
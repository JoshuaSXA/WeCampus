<?php

include_once '../lib/Code2SessionController.php';
include_once '../lib/DBController.php';

/*****************************************************
 *
 * 该api用来将用户登录的code凭证兑换为需要的session信息
 * 补：新增检查用户的注册状态
 *
 ****************************************************/

// 实例化Code2SessionController类
$code2SessionControllerObj = new Code2SessionController();

// 调用getSessionByCode()方法
$retVal = $code2SessionControllerObj->getSessionByCode();

$retVal = json_decode(json_encode($retVal), TRUE);

// 检测返回值
if($retVal && array_key_exists('openid', $retVal)) {

	// 获取openid
	$openID = $retVal['openid'];

	// 创建数据库接口类 DBController
	$DBController = new DBController();
	// 连接数据库
	$DBController->connDatabase();

	// 查询该openid对应的用户是否已经注册
	$sql = "SELECT school_id FROM user WHERE open_id = (?)";

	// 创建预处理语句
	$stmt = mysqli_stmt_init($DBController->getConnObject());

    if(mysqli_stmt_prepare($stmt, $sql)){

		// 绑定参数
		mysqli_stmt_bind_param($stmt, "s", $openID); 

		// 执行查询
		mysqli_stmt_execute($stmt);

		// 获取查询结果
		$result = mysqli_stmt_get_result($stmt);  

		// 获取值
		$retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC); 

		echo json_encode(array('success' => TRUE, 'openid' => $retVal['openid'], 'school_id' => count($retValue) == 0 ? 0 : $retValue[0]['school_id']));

		// 释放结果
		mysqli_stmt_free_result($stmt);

		// 关闭mysqli_stmt类
		mysqli_stmt_close($stmt);
			
    } else {

        echo json_encode(array("success" => FALSE));
        	
    }	

	// 断开与数据库的连接
	$DBController->disConnDatabase();	

} else {

	echo json_encode(array('success' => FALSE));

}

?>
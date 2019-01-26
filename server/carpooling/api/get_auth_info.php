<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 获取用户的认证信息，包括：发起过多少次拼车、是否认证、手机号码
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用getUserAuthInfo()方法
$carPoolingControllerObj->getUserAuthInfo();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
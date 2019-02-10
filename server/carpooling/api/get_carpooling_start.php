<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 用户可以通过调用此api查询即将开始的拼车
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用getCarPoolingToStart()方法
$carPoolingControllerObj->getCarPoolingToStart();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
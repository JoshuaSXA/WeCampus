<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 该api用来帮助用户发起一个拼车
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用createCarPoolingCase()方法
$carPoolingControllerObj->createCarPoolingCase();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
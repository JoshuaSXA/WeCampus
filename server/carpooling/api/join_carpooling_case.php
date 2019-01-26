<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 用户可以通过调用此api来参与一个拼车
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用joinCarPoolingCase()方法
$carPoolingControllerObj->joinCarPoolingCase();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
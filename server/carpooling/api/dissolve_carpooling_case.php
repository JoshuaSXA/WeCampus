<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 拼车发起者可以通过此api来解散当前的拼车
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用dissolveCarPoolingCase()方法
$carPoolingControllerObj->dissolveCarPoolingCase();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
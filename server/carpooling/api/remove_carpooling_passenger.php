<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 拼车发起者可以通过此api来将某一位参与者提出当前拼车
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用removePassengerFromCarPooling()方法
$carPoolingControllerObj->removePassengerFromCarPooling();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
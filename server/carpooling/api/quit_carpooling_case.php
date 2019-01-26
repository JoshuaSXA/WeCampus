<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 拼车参与者可以通过此api来主动退出当前的拼车
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用quitCarPoolingCase()方法
$carPoolingControllerObj->quitCarPoolingCase();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
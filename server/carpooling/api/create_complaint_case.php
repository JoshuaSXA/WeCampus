<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 该api用来帮助投诉者创建一个投诉的case
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用createComplaintCase()方法
$carPoolingControllerObj->createComplaintCase();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
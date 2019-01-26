<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 该api用来获取所有的乘车点信息
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用getAllPickUpPlaces()方法
$carPoolingControllerObj->getAllPickUpPlaces();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
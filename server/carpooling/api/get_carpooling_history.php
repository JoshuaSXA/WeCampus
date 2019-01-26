<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 获取当前用户的拼车记录，返回分页后的结果
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用getMyCarPoolingHistory()方法
$carPoolingControllerObj->getMyCarPoolingHistory();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
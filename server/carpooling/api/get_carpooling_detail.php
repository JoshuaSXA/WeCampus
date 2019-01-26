<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 获取当前拼车case的详细信息，该详情页包含了搜索详情和拼车记录详情两个页面
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用getCarPoolDetail()方法
$carPoolingControllerObj->getCarPoolDetail();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
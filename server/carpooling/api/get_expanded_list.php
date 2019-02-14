<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 用户根据条件查询拼车，扩展时间段，返回分页查询结果
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用getExpandedCarPoolingCaseList()方法
$carPoolingControllerObj->getExpandedCarPoolingCaseList();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
<?php
/**
 * Created by PhpStorm.
 * User: 70756
 * Date: 2019/3/17
 * Time: 13:56
 */
include_once '../class/StopController.php';

// 实例化RouteController类
$stopControllerObj = new StopController();

// 调用cancelTripTicket()方法
$stopControllerObj->updateStop();

// 断开与数据库的连接
$stopControllerObj->closeDBConnection();

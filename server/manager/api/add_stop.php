<?php
/**
 * Created by PhpStorm.
 * User: 70756
 * Date: 2019/3/16
 * Time: 17:25
 */
include_once '../manager/class/StopController.php';

// 实例化RouteController类
$stopControllerObj = new StopController();

// 调用cancelTripTicket()方法
$stopControllerObj->addStop();

// 断开与数据库的连接
$stopControllerObj->closeDBConnection();

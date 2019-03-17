<?php
/**
 * Created by PhpStorm.
 * User: 70756
 * Date: 2019/3/17
 * Time: 13:57
 */
include_once '../class/StationController.php';

// 实例化RouteController类
$stationControllerObj = new StationController();

// 调用cancelTripTicket()方法
$stationControllerObj->updateStation();

// 断开与数据库的连接
$stationControllerObj->closeDBConnection();
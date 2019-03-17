<?php
/**
 * Created by PhpStorm.
 * User: 70756
 * Date: 2019/3/17
 * Time: 13:56
 */
include_once '../class/RouteController.php';

// 实例化RouteController类
$routeControllerObj = new RouteController();

// 调用cancelTripTicket()方法
$routeControllerObj->updateRoute();

// 断开与数据库的连接
$routeControllerObj->closeDBConnection();


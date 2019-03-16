<?php
/**
 * Created by PhpStorm.
 * User: 70756
 * Date: 2019/3/16
 * Time: 17:10
 */
include_once 'RouteController.php';

// 实例化RouteController类
$routeControllerObj = new RouteController();

// 调用cancelTripTicket()方法
$routeControllerObj->deleteRoute();

// 断开与数据库的连接
$routeControllerObj->closeDBConnection();


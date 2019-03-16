<?php
/**
 * Created by PhpStorm.
 * User: 70756
 * Date: 2019/3/16
 * Time: 17:27
 */

// 实例化RouteController类
$stopControllerObj = new StopController();

// 调用cancelTripTicket()方法
$stopControllerObj->deleteStop();

// 断开与数据库的连接
$stopControllerObj->closeDBConnection();

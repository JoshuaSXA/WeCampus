<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-13
 * Time: 23:47
 */

include_once '../class/TicketingController.php';

/**********************************************
 *
 * 用户通过此api来获取用户的订购历史数量
 *
 ************t*********************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用getUserTripNumber()方法
$ticketingControllerObj->getUserTripNumber();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-13
 * Time: 23:49
 */

include_once '../class/TicketingController.php';

/**********************************************
 *
 * 用户通过此api来获取用户的订购历史
 *
 ************t*********************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用getUserTripList()方法
$ticketingControllerObj->getUserTripList();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
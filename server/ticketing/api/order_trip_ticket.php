<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-13
 * Time: 23:48
 */

include_once '../class/TicketingController.php';

/**********************************************
 *
 * 用户通过此api来订购车票
 *
 ************t*********************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用orderTripTicket()方法
$ticketingControllerObj->orderTripTicket();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
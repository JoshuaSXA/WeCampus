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
 * 用户通过此api来更改预定状态
 *
 ************t*********************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用cancelTripTicket()方法
$ticketingControllerObj->cancelTripTicket();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
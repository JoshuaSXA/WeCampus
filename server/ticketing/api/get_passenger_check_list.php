<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-17
 * Time: 15:48
 */

include_once '../class/TicketingController.php';

/**********************************************
 *
 * 通过此api来获取某一班次的所有的用户状态数量
 *
 **********************************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用getPassengerCheckList()方法
$ticketingControllerObj->getPassengerCheckList();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
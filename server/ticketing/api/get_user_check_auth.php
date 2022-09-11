<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-17
 * Time: 15:30
 */

include_once '../class/TicketingController.php';

/**********************************************
 *
 * 通过此api来检查用户是否有验票的权限
 *
 **********************************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用getUserCheckAuth()方法
$ticketingControllerObj->getUserCheckAuth();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
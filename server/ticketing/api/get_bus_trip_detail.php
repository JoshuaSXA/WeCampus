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
 * 用户通过此api来获取某一乘车的具体信息
 *
 ************t*********************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用getBusTripDetail()方法
$ticketingControllerObj->getBusTripDetail();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
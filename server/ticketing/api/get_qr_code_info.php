<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-16
 * Time: 23:22
 */

include_once '../class/TicketingController.php';

/**********************************************
 *
 * 用户通过此api来获取二维码加密信息
 *
 **********************************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用getQRCodeInfo()方法
$ticketingControllerObj->getQRCodeInfo();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
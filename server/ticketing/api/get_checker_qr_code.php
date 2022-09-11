<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-17
 * Time: 17:51
 */

include_once '../class/TicketingController.php';

/**********************************************
 *
 * 通过此api来获取检查者的二维码密文
 *
 **********************************************/

// 实例化TicketingController类
$ticketingControllerObj = new TicketingController();

// 调用getCheckerQRCode()方法
$ticketingControllerObj->getCheckerQRCode();

// 断开与数据库的连接
$ticketingControllerObj->closeDBConnection();
<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-02-27
 * Time: 18:45
 */

include_once '../class/CardSeekingController.php';

/**********************************************
 *
 * 该api用来帮助找到卡的用户创建一个case
 *
 *********************************************/

// 实例化CardSeekingController类
$carPoolingControllerObj = new CardSeekingController();

// 调用createCardSeekingCase()方法
$carPoolingControllerObj->createCardSeekingCase();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();



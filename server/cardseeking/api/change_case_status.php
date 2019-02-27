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
 * 用户通过此api来更改找卡案例的信息
 *
 *********************************************/

// 实例化CardSeekingController类
$carPoolingControllerObj = new CardSeekingController();

// 调用changeCaseStatus()方法
$carPoolingControllerObj->changeCaseStatus();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();
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
 * 用户通过此api来获取初始化信息
 *
 *********************************************/

// 实例化CardSeekingController类
$carPoolingControllerObj = new CardSeekingController();

// 调用getUserInitialInfo()方法
$carPoolingControllerObj->getUserInitialInfo();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();
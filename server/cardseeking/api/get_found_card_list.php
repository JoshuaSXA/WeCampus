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
 * 用户通过此api来获取自己找到的卡的列表
 *
 *********************************************/

// 实例化CardSeekingController类
$carPoolingControllerObj = new CardSeekingController();

// 调用getMyFoundCardList()方法
$carPoolingControllerObj->getMyFoundCardList();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();
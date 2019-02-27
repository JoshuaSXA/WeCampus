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
 * 该api用来帮助找到卡的用户上传图片
 *
 *********************************************/

// 实例化CardSeekingController类
$carPoolingControllerObj = new CardSeekingController();

// 调用uploadCardImage()方法
$carPoolingControllerObj->uploadCardImage();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();
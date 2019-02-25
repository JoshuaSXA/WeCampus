<?php

include_once '../class/CarPoolingController.php';

/**********************************************
 *
 * 通过此api来获取与该拼车有关的所有用户的头像、昵称和oid
 *
 *********************************************/

// 实例化CarPoolingController类
$carPoolingControllerObj = new CarPoolingController();

// 调用getRelevantUserList()方法
$carPoolingControllerObj->getRelevantUserList();

// 断开与数据库的连接
$carPoolingControllerObj->closeDBConnection();

?>
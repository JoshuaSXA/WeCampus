<?php

include_once '../class/TransController.php';

/**********************************************
 *
 * 接收客户端传来的用户反馈信息，存储到数据库中
 *
 *********************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用getUserFeedback()方法
$transControllerObj->getUserFeedback();

?>

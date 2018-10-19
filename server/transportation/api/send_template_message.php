<?php 

include_once '../class/TransController.php';

/**********************************************
 *
 * 该api用来将模板消息的信息存储到数据库
 *
 *********************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用addTemplateMessageInfo()方法
$transControllerObj->addTemplateMessageInfo();

?>
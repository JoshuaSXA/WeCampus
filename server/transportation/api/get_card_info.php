<?php

include_once '../class/TransController.php';

/**********************************************
 *
 * 该api用来根据学校的school_id获取该学校的特殊卡片
 *
 *********************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用getTipCardInfo()方法
$transControllerObj->getTipCardInfo();


?>
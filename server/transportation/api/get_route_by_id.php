<?php

include_once '../class/TransController.php';

/**********************************************
 *
 * 该api用来根据学校的school_id获取从该站点出发的路线信息信息
 *
 *********************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用getRouteInfoByID()方法
$transControllerObj->getRouteInfoByID();

?>
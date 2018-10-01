<?php

include_once '../class/TransController.php';

/*****************************************************
 *
 * 该api用来根据station_id、route_id、pattern_id获取路线详情
 *
 ****************************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用getRouteDetailByID()方法
$transControllerObj->getRouteDetailByID();

?>
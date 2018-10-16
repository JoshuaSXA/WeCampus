<?php

include_once '../class/TransController.php';

/**********************************************
 *
 * 该api用来根据学校的school_id获取学校周围的车站信息
 *
 *********************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用getBusStationInfoByID()方法
$transControllerObj->getRouteInfoByID();

?>
<?php

include_once '../class/TransController.php';

/*****************************************************
 *
 * 该api用来根据station_id以及cur_date、cur_time获取路线信息
 *
 ****************************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用getRouteInfoByTime()方法
$transControllerObj->getRouteInfoByTime();

?>
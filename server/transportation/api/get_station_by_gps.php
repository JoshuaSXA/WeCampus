<?php

include_once '../class/TransController.php';

/*****************************************************
 *
 * 该api用来根据学校的school_id以及gps获取学校附近的车站信息
 *
 ****************************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用getBusStationInfoByLocation()方法
$transControllerObj->getBusStationInfoByLocation();

?>
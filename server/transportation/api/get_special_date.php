<?php

include_once '../class/TransController.php';

/*****************************************************
 *
 * 该api用来根据school_id获取该学校近期的节假日详情
 *
 ****************************************************/



// 实例化TransController类
$transControllerObj = new TransController();

// 调用getSpecialDateByID()方法
$transControllerObj->getSpecialDateByID();

?>
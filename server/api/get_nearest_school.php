<?php

include_once '../lib/UserInfoController.php';

/*****************************************************
 *
 * 该api用来获取离用户最近的学校信息
 *
 ****************************************************/

$userInfoControllerObj = new UserInfoController();

$userInfoControllerObj->getTheNearestSchool();

?>
<?php

include_once '../lib/UserInfoController.php';

/*****************************************************
 *
 * 该api用来获取完整的学校列表
 *
 ****************************************************/

$userInfoControllerObj = new UserInfoController();

$userInfoControllerObj->getSchoolList();

?>
<?php

include_once '../lib/UserInfoController.php';

/*****************************************************
 *
 * 该api用来获取用户的所有信息
 *
 ****************************************************/

$userInfoControllerObj = new UserInfoController();

$userInfoControllerObj->getUserInfo();

?>
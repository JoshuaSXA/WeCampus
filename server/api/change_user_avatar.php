<?php

include_once '../lib/UserInfoController.php';

/*****************************************************
 *
 * 该api用来修改用户的头像
 *
 ****************************************************/

$userInfoControllerObj = new UserInfoController();

$userInfoControllerObj->changeUserAvatar();

?>
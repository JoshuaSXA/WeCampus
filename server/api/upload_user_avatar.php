<?php

include_once '../lib/UserInfoController.php';

/*****************************************************
 *
 * 该api用来接收用户上传的头像
 *
 ****************************************************/

$userInfoControllerObj = new UserInfoController();

$userInfoControllerObj->uploadAvatar();

?>
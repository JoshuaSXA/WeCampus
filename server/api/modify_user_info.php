<?php

include_once '../lib/UserInfoController.php';

/*****************************************************
 *
 * 该api用来修改用户的nickname、name、gender、student_id、school_id
 *
 ****************************************************/
//echo 'hahaha';

$userInfoControllerObj = new UserInfoController();

$userInfoControllerObj->modifyUserInfo();

?>
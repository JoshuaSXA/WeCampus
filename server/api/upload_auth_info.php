<?php

include_once '../lib/StudentIdentityAuthentication.php';

/*****************************************************
 *
 * 该api用来上传用户的学生身份认证信息
 *
 ****************************************************/

$studentIdentityAuthenticationObj = new StudentIdentityAuthentication();

$studentIdentityAuthenticationObj->uploadAuthInfor();

?>
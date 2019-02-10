<?php

include_once '../lib/PhoneNumberVerification.php';

/*****************************************************
 *
 * 该api用来向用户发送短信验证码
 *
 ****************************************************/

// 实例化PhoneNumberVerificationController类
$PhoneNumberVerificationObj = new PhoneNumberVerificationController();

$PhoneNumberVerificationObj->sendVerificationMessage();

$PhoneNumberVerificationObj->closeService();

?>
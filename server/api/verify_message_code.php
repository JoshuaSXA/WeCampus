<?php

include_once '../lib/PhoneNumberVerification.php';

/*****************************************************
 *
 * 该api用来验证用户的验证码是否正确
 *
 ****************************************************/

// 实例化PhoneNumberVerificationController类
$PhoneNumberVerificationObj = new PhoneNumberVerificationController();

$PhoneNumberVerificationObj->checkVerificationCode();

$PhoneNumberVerificationObj->closeService();

?>
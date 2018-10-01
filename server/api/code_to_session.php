<?php

include_once '../lib/Code2SessionController.php';

/*****************************************************
 *
 * 该api用来将用户登录的code凭证兑换为需要的session信息
 *
 ****************************************************/

// 实例化Code2SessionController类
$code2SessionControllerObj = new Code2SessionController();

// 调用getSessionByCode()方法
$code2SessionControllerObj->getSessionByCode();

?>
<?php

include_once '../lib/Code2SessionController.php';

/*****************************************************
 *
 * 该api用来将用户登录的code凭证兑换为需要的session信息
 * 补：新增用户的注册状态验证功能
 *
 ****************************************************/

// 实例化Code2SessionController类
$code2SessionControllerObj = new Code2SessionController();

// 调用getSessionByCode()方法
$retVal = $code2SessionControllerObj->getSessionByCode();


// 校验请求是否成功
if($retVal) {

	

}


?>
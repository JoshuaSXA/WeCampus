<?php

include_once '../lib/TokenController.php';

/*****************************************************
 *
 * 该api用来向用户提供小程序的access_token
 *
 ****************************************************/

// 实例化TokenController类
$tokenControllerObj = new TokenController();

$retVal = array('access_token' => NULL);

if($tokenControllerObj->getAccessToken()){

	$retVal['access_token'] = $tokenControllerObj->getAccessToken();
	
}

echo json_encode($retVal);

?>
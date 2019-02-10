<?php

// 小程序的AppID
$appID = 'wxc06d3e6075749a79';

// 小程序的Secret
$secret = '24882e2077bcb8f7b724eff5100aa852';



function arrayRemove(&$input, $key) {


	// 判断是否存在该key
	if(!array_key_exists($key, $input)){

		return FALSE;

	}

	// 获取key组成的数组
	$keys = array_keys($input);

	// 搜索该key在key数组中的索引
	$index = array_search($key, $keys);

	// 删除该键值对
	if($index !== FALSE){

		array_splice($input, $index, 1);

	}

	return TRUE;

}

// 短信应用SDK AppID
$smsAppID = 1400174752; // 1400开头

// 短信应用SDK AppKey
$smsAppKey = "5a4f4c0fdfa19b0b067a1256b3a10dca";

// 短信模板ID，需要在短信应用中申请
$smsTemplateId = 277034;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请

$smsSign = "微校高校生活";	 // NOTE: 这里的签名只是示例，请使用真实的已申请的签名，签名参数使用的是`签名内容`，而不是`签名ID`

$smsValidTime = "5";

?>
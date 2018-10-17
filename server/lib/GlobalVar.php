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

?>
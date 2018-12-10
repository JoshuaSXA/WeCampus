<?php

// 引用全局变量
include_once 'GlobalVar.php';

// 引用curl请求类
include_once 'CurlRequestController.php';

/**
*  实现将用户的登录校验凭证code转换为有用信息
*/

class Code2SessionController
{
	
	private $code;
	private $curlRequestControllerObj;


	function __construct()
	{
		$this->code = $_GET['code'];
		$this->curlRequestControllerObj = new CurlRequestController();
	}


	public function getSessionByCode() {
		global $appID, $secret; 
		// 设置访问的Url
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appID . '&secret=' . $secret . '&js_code=' . $this->code . '&grant_type=authorization_code';

		// 调用curl请求
		if($this->curlRequestControllerObj->curlGetInformation($url)) {

			// 请求成功时返回数据
			return $this->curlRequestControllerObj->getReturnData();

		} else {

			// 请求失败
			return FALSE;

		}
	}

}

?>
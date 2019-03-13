<?php

// 需要引用全局变量
include_once 'GlobalVar.php';

// 这里需要调用curl方法
include_once 'CurlRequestController.php';

/**
* 该类负责实现小程序access_token的定时更新
* 同时提供获取access_token的接口
*/
class TokenController
{
	private $accessToken;
	private $accessTokenStatus;

	private $redis;


	private $curlRequestControllerObj;
	
	function __construct()
	{

		$this->accessToken = NULL;
		$this->accessTokenStatus = FALSE;

		// 实例化一个redis类
		$this->redis = new Redis();

		// 连接到redis_server，端口号为6379， timeout为10s
		$this->redis->connect('127.0.0.1', 6379, 10);

		// 检查access_token的状态
		$this->checkAccessTokenStatus();

		// 断开与redis_server的连接
		$this->redis->close();
	}

	// 检查redis中access_token的状态信息，准备好待获取的access_token
	private function checkAccessTokenStatus() {
		$retVal = $this->redis->get('access_token');

		if($retVal) {

			// access_token 存在，且未过期
			$this->accessToken = $retVal;
			$this->accessTokenStatus = TRUE;

		} else {

			// access_token 未获取，或者已经过期
			$this->accessTokenStatus = FALSE;

			// 重新获取access_token
			$this->refreshAccessToken();

		}
	}

	private function refreshAccessToken() {
		global $appID, $secret;

		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appID . '&secret=' . $secret;

		$this->curlRequestControllerObj = new CurlRequestController();

		if($this->curlRequestControllerObj->curlGetInformation($url)) {
			// 获取GET的返回值
			$retData = $this->curlRequestControllerObj->getReturnData();

			// 优先更新accessToken
			$this->accessToken = $retData->access_token;

			// 将新的access_token的值添加到redis中，并设置过期时间（提前5分钟过期）
			$this->redis->setex('access_token', (int)$retData->expires_in - 300, $retData->access_token);

			return TRUE;

		} else {

			return FLASE;

		}
	}

	// 外部调用接口
	public function getAccessToken() {
		/*
		if(!$this->accessTokenStatus) {

			$this->checkAccessTokenStatus();

		}
		*/
		// 直接返回access_token
		return $this->accessToken;
	}

}
	


?>
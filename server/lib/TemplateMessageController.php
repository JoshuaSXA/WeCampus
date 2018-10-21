<?php
include_once 'TokenController.php';

include_once 'CurlRequestController.php'; 

/**
* 改类负责控制模板消息的发送
*/
class TemplateMessageController
{
	// 接口调用凭证
	private $accessTokenController;

	private $curlRequestControllerObj;

	// 接收者用户的openid
	private $openID;

	// 所需下发的模板ID
	private $templateID;

	// 点击模板卡片后的跳转页面
	private $page;

	// 表单提交场景下的formid
	private $formID;

	// 模板内容
	private $data;

	// 模板需要放大的关键词，不填能则默认无放大
	private $emphasisKey;

	// 构造函数赋值
	function __construct($data)
	{
		// 实例化TokenController 对象
		$this->accessTokenController = new TokenController();

		// 实例化curl对象
		$this->curlRequestControllerObj = new CurlRequsetController();

		// 变量赋值
		$this->openID = $data['openid'];

		$this->templateID = $data['template_id'];

		$this->page = $data['page'];

		$this->formID = $data['form_id'];

		$this->data = $data['data'];

		$this->emphasisKey = $data['emphasis_keyword'];

	}


	public function sendTemplateMessage() {

		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$this->accessTokenController->getAccessToken();

		$data = array(
			"touser" => $this->openID,

			"template_id" => $this->templateID,

			"page" => $this->page,

			"form_id" => $this->formID,

			"data" => $this->data,

			"emphasis_keyword" => $this->emphasisKey

		);

		if($this->curlRequestControllerObj->curlPostInformation($url, json_encode($data))) {

			return TRUE;

		} else {

			return FALSE;

		}

	}

}

?>
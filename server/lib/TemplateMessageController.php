<?php
include_once 'TokenController.php';


/**
* 改类负责控制模板消息的发送
*/
class TemplateMessageController
{
	// 接口调用凭证
	private $accessTokenController;

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

		// 变量赋值
		$this->openID = $data['openid'];

		$this->templateID = $data['template_id'];

		$this->page = $data['page'];

		$this->formID = $data['form_id'];

		$this->data = $data['data'];

		$this->emphasisKey = $data['emphasis_key']
	}


	public function sendTemplateMessage() {

	}

}

?>
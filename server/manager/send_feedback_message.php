<?php

/**********************************************
 *
 * 该api负责实现管理员与transportation模块
 * 针对用户的反馈发送模板消息
 *
 **********************************************/

include_once '../lib/TemplateMessageController.php';

$openID = $_REQUEST['open_id'];

$templateID = "SxK0XVsS7q7oyd4lqRxJthX89n2F5wyzURoW4L3N4-g";


$formID = $_REQUEST['form_id'];

$page = "pages/transportation/index/index";

$keyword_1 = $_REQUEST['keyword_1'];

$keyword_2 = $_REQUEST['keyword_2'];

$msgData = array(
	'openid' => $openID,
	'template_id' => $templateID,
	'form_id' => $formID,
	'page' => $page,
	'data' => array(
		'keyword1' => array("value" => $keyword_1),
		'keyword2' => array("value" => $keyword_2)
	),
	'emphasis_keyword' => "keyword1.DATA"
);

$templateMessageControllerObj = new TemplateMessageController($msgData);

if($templateMessageControllerObj->sendTemplateMessage()) {

	echo "msg send succeed!";

} else {

	echo "msg send fail!";

}

?>
<?php 

include_once '../../Workerman/Autoloader.php'; 
include_once '../../lib/TemplateMessageController.php';

use Workerman\Worker;

date_default_timezone_set("Asia/Shanghai");

// 配置SSL证书选项
$context = array(
	'ssl' => array(
		'local_cert' => '../../SSLcert/wecampus.pem',
		'local_pk' => '../../SSLcert/wecampus.key',
		'verify_peer' => FALSE
	)

);

// 设置WebSocket协议
$worker = new Worker('websocket://0.0.0.0:443', $context);


// 设置transport开启ssl
$worker->transport = 'ssl';


$worker->onWorkerStart = function($worker) {
	// global $templateMessageController = new TemplateMessageController();
	echo 'bingo';
}

$worker->onConnect = function($connection) {
	echo "new connection from ip " . $connection->getRemoteIp();
}


$worker->onMessage = function($connection, $data) {
	if($data == NULL || $data == '') {
		return;
	}

	// 将前端传过来的数据转成关联数组的形式
	$json_data = json_decode($data, TRUE);

	// 初始化模板消息类
	$templateMessageController = new TemplateMessageController($json_data);


	// 定时器每隔一分钟执行一次
	$timeInterval = 60;

	// 模板消息的发送时间
	$timeToSend = $json_data['time'];

	$timer_id = Timer::add($timeInterval, function()use(&$timer_id) {

		// 检查当前时间是否为发送时间
		if(date('d H:i') == $timeToSend) {

			// 发送模板消息
			$templateMessageController->sendTemplateMessage();

			// 销毁当前定时器
            Timer::del($timer_id);
		}


	};
}


worker::runAll();

?>
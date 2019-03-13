<?php


/**
* 封装常规的curl请求方法
*/
class CurlRequestController
{
	// curl请求的状态信息，0为请求正确，其余为错误码
	private $curlStatus;

	// curl请求获取的数据
	private $retData;

	// 构造函数变量初始化
	function __construct()
	{
		$this->curlStatus = -1;
		$this->retData = NULL;
	}

	public function getCurlStatus() {
		return $this->curlStatus;
	}

	public function getReturnData(){
		return $this->retData;
	}

    // 使用curl来实现GET请求
	public function curlGetInformation($getInfoUrl, $timeOut = 10) {
		// 初始化
		$curl = curl_init();

		// 设置curl请求的超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT,$timeOut);

		// 设置url
		curl_setopt($curl, CURLOPT_URL, $getInfoUrl);

		// debug专用，输出头文件信息
        //curl_setopt($curl, CURLOPT_HEADER, TRUE);

        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        // 执行curl
        $data = curl_exec($curl);

        // 获取错误码
        $this->curlStatus = curl_errno($curl);

        // 判断curl请求是否成功
        if($this->curlStatus) {

    		return FALSE;

		}else{
			// 存储data，转化为json格式
			$this->retData = json_decode($data);
			return TRUE;

		}

        // 关闭请求
        curl_close($curl);
	}


	// 使用curl来实现POST请求
	public function curlPostInformation($sendInfoUrl, $postData, $timeOut = 20) {

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_TIMEOUT,$timeOut);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);  

		curl_setopt($curl, CURLOPT_URL, $sendInfoUrl);

		// 禁止curl验证peer's certificate
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);  

		// 不检查服务器的common name
    	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);

    	// 发送post请求
    	curl_setopt($curl,CURLOPT_POST, TRUE); 

    	// 发送的数据
    	curl_setopt($curl,CURLOPT_POSTFIELDS,$postData);  

    	$data = curl_exec($curl);

        $this->curlStatus = curl_errno($curl);

        // 判断curl请求是否成功
        if($this->curlStatus) {

    		return FALSE;

		}else{
			// 存储data，转化为json格式
			$this->retData = json_decode($data);
			return TRUE;

		}

		curl_close($curl);
	}

	// 使用curl来实现图片下载，就问怕不怕
	public function curlDownloadFile($fileUrl, $savePath, $timeOut = 80) {

		$curl = curl_init();

		curl_setopt($curl,CURLOPT_POST, FALSE);

		curl_setopt($curl, CURLOPT_URL, $fileUrl);

		curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE); 

		// 获取文件内容
		$fileContent = curl_exec($curl);

        $this->curlStatus = curl_errno($curl);

        curl_close($curl);

        // 判断curl请求是否成功
        if($this->curlStatus) {

    		return FALSE;

		}else{

			$downloadFile = fopen($savePath, 'w');

			fwrite($downloadFile, $fileContent);

			fclose($downloadFile);

			return TRUE;

		}

	}

}

?>
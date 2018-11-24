<?php

include_once 'CurlRequestController.php'

$url = 'https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83epmsqu4O32XY7U9cGI6wEhy7u58VnJTRfobiczficDkxiazITlGQ8UgDcMVeRI9gDdU6dVpK3dm2Hayw/132';

$curlRequestControllerObj = new CurlRequestController();

echo $curlRequestControllerObj->getReturnData();


?>
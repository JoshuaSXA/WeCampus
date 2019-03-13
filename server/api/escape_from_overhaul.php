<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-08
 * Time: 10:46
 */


$echoArray = array(
    array(
        'title' => "出行助手",
        'icon' => '../../../lib/moduleIcon/transportation.png',
        'backgroundColor' => "#3399FF",
        'url' => '../../../pages/transportation/index/index',
        'loginRequest' => TRUE
    ),
    array(
        'title' => "拼车出行",
        'icon' => '../../../lib/moduleIcon/carpool.png',
        'backgroundColor' => "#F9B700",
        'url' => '../../../pages/carpool/index/index',
        'loginRequest' => TRUE,
        'phoneRequest' => TRUE
    ),
    array(
        'title' => "卡丢了",
        'icon' => '../../../lib/moduleIcon/card.png',
        'backgroundColor' => "#886AEA",
        'url' => '../../../pages/findCard/index/index',
        'loginRequest' => TRUE,
    ),
    array(
        'title' => "我的微校",
        'icon' => '../../../lib/moduleIcon/my.png',
        'backgroundColor' => "#FF6F61",
        'url' => '../../../pages/personalCenter/personalCenter',
        'loginRequest' => TRUE,
    ),
    array(
        'title' => "课咋样",
        'icon' => '../../../lib/moduleIcon/course.png',
        'backgroundColor' => "#827B79",
        'url' => ''
    ),
    // array(
    //     'title' => "我要签到",
    //     'icon' => '../../../lib/moduleIcon/checkin.png',
    //     'backgroundColor' => "#827B79",
    //     'url' => ''
    // )
);

$devArray=  array(
        'title' => "课咋样",
        'icon' => '../../../lib/moduleIcon/course.png',
        'backgroundColor' => "#0AB76D",
        'url' => '../../../pages/courseEvaluation/index/index'
    );

$statusCode = '';

if(array_key_exists('code', $_GET)) {

    $statusCode = $_GET['code'];

}

if($statusCode == '20190310001') {

    //echo json_encode(array($echoArray[4]), JSON_UNESCAPED_UNICODE);
    echo json_encode(array($devArray), JSON_UNESCAPED_UNICODE);

} else {

    echo json_encode($echoArray, JSON_UNESCAPED_UNICODE);

}
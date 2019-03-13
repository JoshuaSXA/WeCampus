<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-10
 * Time: 15:49
 */


header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:OPTIONS, GET, POST'); 
header('Access-Control-Allow-Headers:x-requested-with,content-type');

include_once "../lib/DBController.php";

// 连接数据库
$DBController = new DBController();
$DBController->connDatabase();

// 返回第一条待验证信息
$sql = "SELECT auth_id, begin_time, open_id, name, student_id, card, status FROM student_auth WHERE status = 2 ORDER BY auth_id ASC LIMIT 1";

$retval = mysqli_query($DBController->getConnObject(), $sql );

if($retval){
    $queryRes=mysqli_fetch_assoc($retval);

    echo json_encode($queryRes, JSON_UNESCAPED_UNICODE);
}

$DBController->disConnDatabase();


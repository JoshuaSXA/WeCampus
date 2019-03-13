<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-10
 * Time: 16:20
 */
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:OPTIONS, GET, POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');

include_once "../lib/DBController.php";



$postData = file_get_contents('php://input');
$stringItems = explode('&', $postData);
$requests = array();
for($i = 0; $i < count($stringItems); ++$i) {
    $pair = explode('=', $stringItems[$i]);
    $requests[$pair[0]] = $pair[1];
}

$openID = $requests['open_id'];
$verifyStatus = $requests['pass'];

// 连接数据库
$DBController = new DBController();
$DBController->connDatabase();


function moveCardFile($srcFile, $dstFile) {

    global $DBController;

    global $openID;

    $sql = "SELECT card FROM student_auth WHERE open_id = (?)";

    // 创建预处理语句
    $stmt = mysqli_stmt_init($DBController->getConnObject());

    if(mysqli_stmt_prepare($stmt, $sql)){

        // 绑定参数
        mysqli_stmt_bind_param($stmt, "s", $openID);

        // 执行查询
        if(!mysqli_stmt_execute($stmt)) {

            return FALSE;

        }

        // 获取查询结果
        $result = mysqli_stmt_get_result($stmt);


        // 获取值
        $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

        $srcFile .= $retValue[0]['card'];

        $dstFile .= $retValue[0]['card'];

        copy($srcFile, $dstFile);

        unlink($srcFile);

        // 释放结果
        mysqli_stmt_free_result($stmt);

        // 关闭mysqli_stmt类
        mysqli_stmt_close($stmt);

        return TRUE;

    } else {

        return TRUE;

    }

}


if($verifyStatus) {

    $sql = "UPDATE student_auth SET status = 1 WHERE open_id=(?)";

    $stmt = mysqli_stmt_init($DBController->getConnObject());

    if(mysqli_stmt_prepare($stmt, $sql)){

        // 绑定参数
        mysqli_stmt_bind_param($stmt, "s", $openID);

        // 执行查询
        if(!mysqli_stmt_execute($stmt)){
            // 查询失败
            echo json_encode(array("success" => FALSE));
            $DBController->disConnDatabase();
            return;

        }

        moveCardFile("../data/cache/", "../data/card/");

        // 查询成功，返回结果
        echo json_encode(array("success" => TRUE));

        // 释放结果
        mysqli_stmt_free_result($stmt);

        // 关闭mysqli_stmt类
        mysqli_stmt_close($stmt);

    } else {

        # echo $this->DBController->getErrorCode();

        echo json_encode(array("success" => FALSE));

    }

} else {

    $sql = "UPDATE student_auth SET status = 3 WHERE open_id=(?)";

    $stmt = mysqli_stmt_init($DBController->getConnObject());

    if(mysqli_stmt_prepare($stmt, $sql)){

        // 绑定参数
        mysqli_stmt_bind_param($stmt, "s", $openID);

        // 执行查询
        if(!mysqli_stmt_execute($stmt)){
            // 查询失败
            echo json_encode(array("success" => FALSE));
            $DBController->disConnDatabase();
            return;

        }
        
        // 查询成功，返回结果
        echo json_encode(array("success" => TRUE));

        // 释放结果
        mysqli_stmt_free_result($stmt);

        // 关闭mysqli_stmt类
        mysqli_stmt_close($stmt);

    } else {

        # echo $this->DBController->getErrorCode();

        echo json_encode(array("success" => FALSE));

    }
    
}

$DBController->disConnDatabase();

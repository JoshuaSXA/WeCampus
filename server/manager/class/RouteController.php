<?php
/**
 * Created by PhpStorm.
 * User: 70756
 * Date: 2019/3/16
 * Time: 15:55
 */
include_once '../../lib/DBController.php';
include_once '../../lib/GlobalVar.php';
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:OPTIONS, GET, POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');


class RouteController
{
    function __construct(){

        date_default_timezone_set('Asia/Shanghai');
        $this->DBController = new DBController();
        $this->DBController->connDatabase();

        // 首先获取header中的内容
        $headers = apache_request_headers();

    }

    // 断开与数据库的连接
    public function closeDBConnection() {
        // 断开与数据库的连接
        $this->DBController->disConnDatabase();
    }

    //添加信息
    public function addRoute(){
        $postData = file_get_contents('php://input');
        $stringItems = explode('&', $postData);
        $requests = array();
        for($i = 0; $i < count($stringItems); ++$i) {
            $pair = explode('=', $stringItems[$i]);
            $requests[$pair[0]] = $pair[1];
        }
        $schoolID = $requests['school_id'];
        $routeName = $requests['routeName'];
        $startStation = $requests['start_station'];
        $endStation = $requests['end_station'];

        $sql = "INSERT INTO route (school_id, route_name, start_station, end_station) VALUES ((?),(?),(?),(?))";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "isii",$schoolID, $routeName, $startStation, $endStation);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)){
                // 查询失败
                echo json_encode(array("success" => FALSE));
                return;
            }
            // 查询成功，返回结果
            echo json_encode(array("success" => TRUE));
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            //echo $this->DBController->getErrorCode();
            echo json_encode(array("success" => FALSE));
        }
    }

    //删除信息
    public function deleteRoute(){
        $postData = file_get_contents('php://input');
        $stringItems = explode('&', $postData);
        $requests = array();
        for($i = 0; $i < count($stringItems); ++$i) {
            $pair = explode('=', $stringItems[$i]);
            $requests[$pair[0]] = $pair[1];
        }
        $routeID = $requests['route_id'];

        $sql = "DELETE FROM route WHERE route_id = (?)";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "i",$routeID);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)){
                // 查询失败
                echo json_encode(array("success" => FALSE));
                return;
            }
            // 查询成功，返回结果
            echo json_encode(array("success" => TRUE));
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            //echo $this->DBController->getErrorCode();
            echo json_encode(array("success" => FALSE));
        }
    }

    //修改路线信息
    public function updateRoute(){
        $postData = file_get_contents('php://input');
        $stringItems = explode('&', $postData);
        $requests = array();
        for($i = 0; $i < count($stringItems); ++$i) {
            $pair = explode('=', $stringItems[$i]);
            $requests[$pair[0]] = $pair[1];
        }
        $routeID = $requests['route_id'];
        $schoolID = $requests['school_id'];
        $routeName = $requests['routeName'];
        $startStation = $requests['start_station'];
        $endStation = $requests['end_station'];

        $sql = "UPDATE route SET school_id = (?),route_name = (?),start_station = (?),end_station = (?) WHERE route_id = (?)";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "isiii",$schoolID, $routeName, $startStation, $endStation,$routeID);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)){
                // 查询失败
                echo json_encode(array("success" => FALSE));
                return;
            }
            // 查询成功，返回结果
            echo json_encode(array("success" => TRUE));
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(array("success" => FALSE));
        }
    }
}
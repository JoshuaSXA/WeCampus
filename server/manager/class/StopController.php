<?php
/**
 * Created by PhpStorm.
 * User: 70756
 * Date: 2019/3/16
 * Time: 17:12
 */
include_once '../../lib/DBController.php';
include_once '../../lib/GlobalVar.php';

class StopController
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

    //添加停站信息
    public function addStop(){
        $stationID = $_REQUEST['station_id'];
        $routeID = $_REQUEST['route_id'];
        $stopName = $_REQUEST['stop_name'];
        $longitude = $_REQUEST['longitude'];
        $latitude = $_REQUEST['latitude'];
        $warning = $_REQUEST['warning'];

        $sql = "INSERT INTO stop(station_id, route_id, stop_name,longitude, latitude,warning) VALUES ((?),(?),(?),(?),(?),(?))";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "iisiis",$stationID, $routeID, $stopName, $longitude,$latitude,$warning);
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

    //删除停站信息
    public function deleteStop(){
        $stationID = $_REQUEST['station_id'];
        $routeID = $_REQUEST['route_id'];

        $sql = "DELETE FROM stop WHERE route_id = (?) AND station_id = (?)";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "ii",$routeID,$stationID);
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

    //修改停站信息
    public function updateStop(){
        $stationID = $_REQUEST['station_id'];
        $routeID = $_REQUEST['route_id'];
        $stopName = $_REQUEST['stop_name'];
        $longitude = $_REQUEST['longitude'];
        $latitude = $_REQUEST['latitude'];
        $warning = $_REQUEST['warning'];

        $sql = "UPDATE stop SET stop_name = (?), longitude = (?), latitude = (?), warning = (?) WHERE station_id = (?) AND route_id = (?)";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "siisii",$stopName, $longitude, $latitude, $warning, $stationID, $routeID);
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
}
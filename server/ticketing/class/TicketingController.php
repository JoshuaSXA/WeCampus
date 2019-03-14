<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-13
 * Time: 23:35
 */

include_once '../../lib/DBController.php';
include_once '../../lib/GlobalVar.php';


/*******************************
 *
 * status 已购票没验票——0、已购票验票了——1、已购票但是退票——2
 *
 *
 ******************************/

class TicketingController
{

    private $schoolID;

    private $openID;

    function __construct(){

        date_default_timezone_set('Asia/Shanghai');
        $this->DBController = new DBController();
        $this->DBController->connDatabase();

        // 首先获取header中的内容
        $headers = apache_request_headers();
        // 提取school_id字段的内容
        if(array_key_exists('school_id', $headers)) {

            $this->schoolID = $headers['school_id'];

        }

        // 获取用户的openid
        if(array_key_exists('open_id', $_REQUEST)) {

            $this->openID = $_REQUEST['open_id'];

        }

    }

    // 断开与数据库的连接
    public function closeDBConnection() {
        // 断开与数据库的连接
        $this->DBController->disConnDatabase();
    }


    // 获取当前用户即将出行的行程数量、历史数量、违规的数量
    public function getUserTripNumber() {

        $sql = "SELECT (SELECT COUNT(*) FROM s WHERE NOW() < s.start_time AND s.open_id=(?)) AS trip_num, (SELECT COUNT(*) FROM s WHERE NOW() >= s.start_time AND s.open_id=(?)) AS history_num, (SELECT COUNT(*) FROM s WHERE NOW() > s.ticket_check_time AND s.status = 0 AND s.open_id=(?)) AS violate_num FROM (ticket_case NATURAL JOIN ticket_schedule) s";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "sss", $this->openID, $this->openID, $this->openID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "data" => array()));
                return;
            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            echo json_encode(array("success" => TRUE, "data" => $retValue[0]), JSON_UNESCAPED_UNICODE);

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            echo json_encode(array("success" => FALSE, "data" => array()));

        }

    }


    // 对于一个学校来说，获取车的种类，包括线路名、起点终点、备注
    public function getBusRouteList() {



    }


    // 给定车的ID，把第二个接口的内容加上班次列表（开票时间、结票时间、剩余票数）
    public function getBusTripList() {


    }


    // 发给班次和车种类的编号，返回起点终点和班次时间和备注和剩余票数、开票结票时间、最晚退票时间、是否买票了
    public function getBusTripDetail() {

        $scheduleID = $_GET['schedule_id'];

        $sql = "SELECT b.schedule_id, a.ticket_name, a.start_place, a.end_place, b.start_time, b.sell_start_time, b.sell_end_time, b.ticket_cancel_time, b.left_ticket_num, EXISTS(SELECT * FROM ticket_case s WHERE s.schedule_id = b.schedule_id AND s.open_id = (?)) AS participate FROM school_ticket a NATURAL JOIN ticket_schedule b WHERE b.schedule_id = (?)";


        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "si", $this->openID, $scheduleID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "detail" => array()));
                return;
            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            echo json_encode(array("success" => TRUE, "detail" => $retValue[0]), JSON_UNESCAPED_UNICODE);

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            echo json_encode(array("success" => FALSE, "detail" => array()));

        }

    }


    // 班次id、openid———>订票（惩罚）
    public function orderTripTicket() {



    }

    // 检查验票者的权限
    private function getCheckerIdentity($checkerOpenId, $scheduleID) {

        $sql = "SELECT COUNT(*) AS num FROM check_access s WHERE s.ticket_id in (SELECT p.ticket_id FROM ticket_schedule p WHERE p.schedule_id = (?)) AND s.open_id = (?)";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "is", $scheduleID, $checkerOpenId);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                return array("success" => FALSE, "checker" => FALSE);

            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            if($retValue[0]['num'] == 0) {

                return array("success" => TRUE, "checker" => FALSE);

            } else {

                return array("success" => TRUE, "checker" => TRUE);

            }

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            return array("success" => FALSE, "checker" => FALSE);

        }

    }

    // 验票接口：收到oid和班次id，检查是否有效，返回给你加密信息
    public function verifyTripTicket() {

        $qrMessage = $_REQUEST['qr_msg'];
        
        // 持票者主扫是0, 被扫是1
        $checkerIdentity = (int)$_REQUEST['checker'];

        // 解密暗文，产生的明文是json string
        $qrMessage = authcode($qrMessage, 'DECODE', 'wecampusticketing', 0);

        // 转array对象
        $qrMessage = json_decode($qrMessage, TRUE);

        // 声明
        $ticketHolder = NULL;
        $ticketChecker = NULL;
        $scheduleID = $qrMessage['schedule_id'];

        if($checkerIdentity) {
            /*被扫入口*/
            $ticketHolder = $qrMessage['open_id'];
            $ticketChecker = $this->openID;

        } else {
            /*主扫入口*/
            $ticketHolder = $this->openID;
            $ticketChecker = $qrMessage['open_id'];

        }

        $checkRes = $this->getCheckerIdentity($ticketChecker, $scheduleID);

        if(!$checkRes['success']){
            echo array("success" => FALSE, "time_exceeded" => FALSE, "wrong_checker" => FALSE);
            return;
        }

        if(!$checkRes['checker']) {
            echo array("success" => FALSE, "time_exceeded" => FALSE, "wrong_checker" => TRUE);
            return;
        }

        $sql = "UPDATE ticket_case SET status = 1 WHERE schedule_id = (?) AND open_id=(?)";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "is", $scheduleID, $ticketHolder);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)){
                // 查询失败
                echo array("success" => FALSE, "time_exceeded" => FALSE, "wrong_checker" => FALSE);
                return;

            }

            // 查询成功，返回结果
            echo array("success" => TRUE, "time_exceeded" => FALSE, "wrong_checker" => FALSE);

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            //echo $this->DBController->getErrorCode();

            echo array("success" => FALSE, "time_exceeded" => FALSE, "wrong_checker" => FALSE);

        }

    }


    // 列表，根据发车时间排序，分页。返回起点终点、班次时间、车的名字、当前票的状态
    public function getUserTripList() {

        $pageBorder = $_GET['page_border'];

        $sql = "SELECT ticket_id, schedule_id, ticket_name, start_place, end_place, start_time, status FROM school_ticket NATURAL JOIN ticket_schedule NATURAL JOIN ticket_case WHERE open_id=(?) AND start_time > (?) ORDER BY start_time DESC LIMIT 10";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "ss", $this->openID, $pageBorder);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "page_data" => array()));
                return;
            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            echo json_encode(array("success" => TRUE, "page_data" => $retValue), JSON_UNESCAPED_UNICODE);

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            echo json_encode(array("success" => FALSE, "page_data" => array()));

        }

    }


    // 取消订票的接口、oid和班次id
    public function cancelTripTicket() {



    }

}
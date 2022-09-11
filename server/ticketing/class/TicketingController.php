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

        $sql = "SELECT (SELECT COUNT(*) FROM ticket_case a NATURAL JOIN ticket_schedule b WHERE NOW() < b.start_time AND a.open_id=(?)) AS trip_num, (SELECT COUNT(*) FROM ticket_case c NATURAL JOIN ticket_schedule d WHERE NOW() >= d.start_time AND c.open_id=(?)) AS history_num, (SELECT COUNT(*) FROM ticket_case e NATURAL JOIN ticket_schedule f WHERE NOW() > f.ticket_check_time AND e.status = 0 AND e.open_id=(?)) AS violate_num";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "sss", $this->openID, $this->openID, $this->openID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "data" => NULL));
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
            //echo $this->DBController->getErrorCode();
            echo json_encode(array("success" => FALSE, "data" => NULL));

        }

    }


    // 对于一个学校来说，获取车的种类，包括线路名、起点终点、备注
    public function getBusRouteList() {
        $sql = "SELECT ticket_id,ticket_name, start_place, end_place, tip FROM school_ticket WHERE school_id = (?)";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "i", $this->schoolID);
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
            echo json_encode(array("success" => TRUE, "detail" => $retValue), JSON_UNESCAPED_UNICODE);
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(array("success" => FALSE, "detail" => array()));
        }


    }



    private function getBusTripInfo($ticket_id) {
        $sql = "SELECT ticket_name, start_place, end_place, tip FROM school_ticket WHERE ticket_id = (?)";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "i", $ticket_id);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {
                return array("success" => FALSE, "detail" => NULL);
            }
            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);
            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);
            // 返回结果
            return array("success" => TRUE, "detail" => $retValue[0]);
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            return array("success" => FALSE, "detail" => NULL);
        }
    }


    // 给定车的ID，把第二个接口的内容加上班次列表（开票时间、结票时间、剩余票数）
    public function getBusTripList() {
        $ticketID = $_GET['ticket_id'];

        $sql = "SELECT schedule_id, sell_start_time ,sell_end_time , left_ticket_num, start_time FROM ticket_schedule WHERE ticket_id = (?) AND (NOW() BETWEEN ticket_show_time AND ticket_hide_time)";

        $busTripInfoRes = $this->getBusTripInfo($ticketID);

        if(!$busTripInfoRes['success']) {
            echo json_encode(array("success" => FALSE, "data" => NULL));
            return;
        }

        $busTripInfo = $busTripInfoRes['detail'];

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "i", $ticketID);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {
                echo json_encode(array("success" => FALSE, "data" => NULL));
                return;
            }
            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);
            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);
            // 返回结果

            $busTripInfo['schedule'] = $retValue;

            echo json_encode(array("success" => TRUE, "data" => $busTripInfo), JSON_UNESCAPED_UNICODE);
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(array("success" => FALSE, "data" => NULL));
        }

    }


    // 发给班次和车种类的编号，返回起点终点和班次时间和备注和剩余票数、开票结票时间、最晚退票时间、是否买票了
    public function getBusTripDetail() {

        $scheduleID = $_GET['schedule_id'];

        $sql = "SELECT b.schedule_id, a.ticket_name, a.start_place, a.end_place, a.tip, b.start_time, b.sell_start_time, b.sell_end_time, b.ticket_cancel_time, b.left_ticket_num, CASE EXISTS(SELECT * FROM ticket_case s WHERE s.schedule_id = b.schedule_id AND s.open_id = (?)) WHEN 0 THEN -1 ELSE (SELECT k.status FROM ticket_case k WHERE k.schedule_id = (?) AND k.open_id = (?)) END AS status FROM school_ticket a NATURAL JOIN ticket_schedule b WHERE b.schedule_id = (?)";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "sisi", $this->openID, $scheduleID, $this->openID, $scheduleID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "detail" => NULL));
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
            //echo $this->DBController->getErrorCode();
            echo json_encode(array("success" => FALSE, "detail" => NULL));

        }

    }



    private function checkOrderTicketStatus($scheduleID) {

        $sql = "SELECT (SELECT a.limits FROM school_ticket a WHERE a.ticket_id = b.ticket_id) AS limits, COUNT(*) AS num FROM ticket_schedule b WHERE b.schedule_id = (?) AND (NOW() BETWEEN b.sell_start_time AND b.sell_end_time)";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "i", $scheduleID);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {
                return array("success" => FALSE, "time_exceeded" => FALSE, "limits" => FALSE);
            }
            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);
            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            if($retValue[0]['num'] == 0) {
                return array("success" => TRUE, "time_exceeded" => TRUE, "limits" => $retValue[0]['limits']);
            } else {
                return array("success" => TRUE, "time_exceeded" => FALSE, "limits" => $retValue[0]['limits']);
            }
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            echo $this->DBController->getErrorCode();
            return array("success" => FALSE, "time_exceeded" => FALSE, "limits" => FALSE);
        }
    }


    private function checkOrderUserAuth($userOid, $scheduleID) {

        $sql = "SELECT COUNT(*) AS num FROM ticket_access a NATURAL JOIN user b WHERE b.open_id = (?) AND a.ticket_id IN (SELECT c.ticket_id FROM ticket_schedule c WHERE c.schedule_id = (?))";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "si", $userOid, $scheduleID);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {
                return array("success" => FALSE, "auth" => FALSE);
            }
            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);
            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);
            // 返回结果
            if($retValue[0]['num'] == 0) {
                return array("success" => TRUE, "auth" => FALSE);
            } else {
                return array("success" => TRUE, "auth" => TRUE);
            }
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            //echo $this->DBController->getErrorCode();
            return array("success" => FALSE, "auth" => FALSE);
        }

    }


    // 班次id、openid———>订票（惩罚）
    public function orderTripTicket() {
        $scheduleID = $_REQUEST['schedule_id'];

        $checkRes = $this->checkOrderTicketStatus($scheduleID);


        if(!$checkRes['success']) {
            echo json_encode(array("success" => FALSE,"time_exceed" => FALSE, "auth" => TRUE));
            return;
        }

        // 检查是否超时
        if($checkRes['time_exceeded']) {
            echo json_encode(array("success" => FALSE,"time_exceed" => TRUE, "auth" => TRUE));
            return;
        }

        // 检查是否有权限限制
        if($checkRes['limits']) {
            // 有权限要求
            $authRes = $this->checkOrderUserAuth($this->openID, $scheduleID);

            if(!$authRes['success']) {
                echo json_encode(array("success" => FALSE,"time_exceed" => FALSE, "auth" => TRUE));
                return;
            }

            if(!$authRes['auth']) {
                echo json_encode(array("success" => FALSE,"time_exceed" => FALSE, "auth" => FALSE));
                return;
            }

        }

        $sql = "INSERT INTO ticket_case (schedule_id, open_id, order_time, check_time) VALUES ((?),(?),NOW(),0) ON DUPLICATE KEY UPDATE status = 0";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "is",$scheduleID, $this->openID);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)){
                // 查询失败
                echo json_encode(array("success" => FALSE,"time_exceed" => FALSE, "auth" => TRUE));
                return;
            }
            // 查询成功，返回结果
            echo json_encode(array("success" => TRUE,"time_exceed" => FALSE, "auth" => TRUE));
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            //echo $this->DBController->getErrorCode();
            echo json_encode(array("success" => FALSE,"time_exceed" => FALSE, "auth" => TRUE));
        }

    }


    // 检查验票者的权限
    private function getCheckerIdentityBySchedule($checkerOpenId, $scheduleID) {

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


    // 获取该用户是否有某个车次的验票权限，ticket_id
    private function getCheckerIdentityByTikcet($checkerOpenId, $ticketID) {

        $sql = "SELECT COUNT(*) AS num FROM check_access WHERE open_id = (?) AND ticket_id = (?)";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "si", $this->openID, $ticketID);

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


    // 检票之前，检查用户状态是否满足标准
    private function checkUserStatus($userOid, $scheduleID) {

        $sql = "SELECT COUNT(*) AS num FROM ticket_case a NATURAL JOIN ticket_schedule b WHERE a.open_id = (?) AND a.schedule_id = (?) AND (NOW() BETWEEN b.ticket_check_start AND b.ticket_check_time) AND status = 0";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "si", $userOid, $scheduleID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                return array("success" => FALSE, "exists" => FALSE);

            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            if($retValue[0]['num'] == 0) {

                return array("success" => TRUE, "exists" => FALSE);

            } else {

                return array("success" => TRUE, "exists" => TRUE);

            }

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {
            echo $this->DBController->getErrorCode();
            return array("success" => FALSE, "exists" => FALSE);

        }

    }


    public function getUserCheckAuth() {

        $ticketID = $_GET['ticket_id'];

        $checkRes = $this->getCheckerIdentityByTikcet($this->openID, $ticketID);

        echo json_encode($checkRes);

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
        $scheduleID = NULL;

        if($checkerIdentity) {
            /*被扫入口*/
            $scheduleID = $qrMessage['schedule_id'];
            $ticketHolder = $qrMessage['open_id'];
            $ticketChecker = $this->openID;

        } else {
            /*主扫入口*/
            $scheduleID = $_REQUEST['schedule_id'];
            $ticketHolder = $this->openID;
            $ticketChecker = $qrMessage['open_id'];

        }

        $checkRes = $this->getCheckerIdentityBySchedule($ticketChecker, $scheduleID);

        $userStatus = $this->checkUserStatus($ticketHolder, $scheduleID);

        if(!$checkRes['success'] || !$userStatus['success'] || !$userStatus['exists']){
            echo json_encode(array("success" => FALSE, "time_exceeded" => FALSE, "wrong_checker" => FALSE, "schedule_id" => $scheduleID));
            return;
        }

        if(!$checkRes['checker']) {
            echo json_encode(array("success" => FALSE, "time_exceeded" => FALSE, "wrong_checker" => TRUE, "schedule_id" => $scheduleID));
            return;
        }

        $sql = "UPDATE ticket_case SET status = 1 WHERE schedule_id = (?) AND open_id=(?) AND status = 0";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "is", $scheduleID, $ticketHolder);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)){
                // 查询失败
                echo json_encode(array("success" => FALSE, "time_exceeded" => FALSE, "wrong_checker" => FALSE, "schedule_id" => $scheduleID));
                return;

            }

            // 查询成功，返回结果
            echo json_encode(array("success" => TRUE, "time_exceeded" => FALSE, "wrong_checker" => FALSE, "schedule_id" => $scheduleID));

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            //echo $this->DBController->getErrorCode();

            echo json_encode(array("success" => FALSE, "time_exceeded" => FALSE, "wrong_checker" => FALSE, "schedule_id" => $scheduleID));

        }

    }



    public function getCheckerQRCode() {

        $ticketID = $_GET['ticket_id'];

        $checkRes = $this->getCheckerIdentityByTikcet($this->openID, $ticketID);

        if(!$checkRes['success']) {
            echo json_encode(array("success" => FALSE, "checker" => FALSE, "qr_msg" => ""));
            return;
        }

        if(!$checkRes['checker']) {
            echo json_encode(array("success" => TRUE, "checker" => FALSE, "qr_msg" => ""));
            return;
        }

        $qrMessage = json_encode(array("open_id" => $this->openID));

        // 解密暗文，产生的明文是json string
        $qrMessage = authcode($qrMessage, 'ENCODE', 'wecampusticketing', 0);

        echo json_encode(array("success" => TRUE, "checker" => TRUE, "qr_msg" => $qrMessage));

    }


    // 列表，根据发车时间排序，分页。返回起点终点、班次时间、车的名字、当前票的状态
    public function getUserTripList() {

        $pageBorder = $_GET['page_border'];

        $sql = "SELECT ticket_id, schedule_id, ticket_name, start_place, end_place, ticket_check_time, start_time, status FROM school_ticket NATURAL JOIN ticket_schedule NATURAL JOIN ticket_case WHERE open_id=(?) AND start_time < (?) ORDER BY start_time DESC LIMIT 10";

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


    private function checkCancelTicketStatus($scheduleID) {
        $sql = "SELECT COUNT(*) AS num FROM ticket_schedule WHERE schedule_id = (?) AND (NOW() BETWEEN sell_start_time AND ticket_cancel_time)";
        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "i", $scheduleID);
            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {
                return array("success" => FALSE, "time_exceeded" => FALSE);
            }
            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);
            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);
            // 返回结果
            if($retValue[0]['num'] == 0) {
                return array("success" => TRUE, "time_exceeded" => TRUE);
            } else {
                return array("success" => TRUE, "time_exceeded" => FALSE);
            }
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            return array("success" => FALSE, "time_exceeded" => FALSE);
        }
    }


    // 取消订票的接口、oid和班次id
    public function cancelTripTicket() {
        $scheduleID = $_REQUEST['schedule_id'];
        $checkRes = $this->checkCancelTicketStatus($scheduleID);
        $userStatus = $this->checkUserStatus($this->openID, $scheduleID);

        if(!$checkRes['success'] || !$userStatus['success']) {
            echo json_encode(array("success" => FALSE, "time_exceeded" => FALSE));
            return;
        }
        if($checkRes['time_exceeded']) {
            echo json_encode(array("success" => FALSE, "time_exceeded" => TRUE));
            return;
        }
        $sql = "UPDATE ticket_case SET status = 2 WHERE open_id = (?) AND schedule_id = (?) AND status = 0";
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());
        if(mysqli_stmt_prepare($stmt, $sql)){
            // 绑定参数
            mysqli_stmt_bind_param($stmt, "si", $this->openID, $scheduleID);
            // 执行查询
            if(!mysqli_stmt_execute($stmt) || !$userStatus['exists']){
                // 查询失败
                echo json_encode(array("success" => FALSE, "time_exceeded" => FALSE));
                return;
            }
            // 查询成功，返回结果
            echo json_encode(array("success" => TRUE, "time_exceeded" => FALSE));
            // 释放结果
            mysqli_stmt_free_result($stmt);
            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);
        } else {
            //echo $this->DBController->getErrorCode();
            echo json_encode(array("success" => FALSE, "time_exceeded" => FALSE));
        }

    }


    // 返回加密后的二维码
    public function getQRCodeInfo() {

        $scheduleID = $_GET['schedule_id'];

        $qrMessage = json_encode(array("open_id" => $this->openID, "schedule_id" => $scheduleID));

        $qrMessage = authcode($qrMessage, 'ENCODE', 'wecampusticketing', 0);

        $sql = "SELECT a.ticket_name, a.start_place, a.end_place, a.tip, b.ticket_check_time, b.start_time, c.status FROM school_ticket a NATURAL JOIN ticket_schedule b NATURAL JOIN ticket_case c WHERE b.schedule_id = (?) AND c.open_id = (?)";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "is", $scheduleID, $this->openID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "data" => NULL));
                return;
            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);


            if(count($retValue)) {

                $retValue[0]['qr_msg'] = $qrMessage;

            } else {
                $retValue[0] = NULL;
            }

            // 返回结果
            echo json_encode(array("success" => TRUE, "data" => $retValue[0]), JSON_UNESCAPED_UNICODE);

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            echo json_encode(array("success" => FALSE, "data" => NULL));

        }

    }


    // 获取某班次的乘客、检票和未检票的数量
    public function getPassengerCheckList() {

        $scheduleID = $_GET['schedule_id'];

        $sql = "SELECT (SELECT COUNT(*) FROM ticket_case a WHERE a.schedule_id = (?) AND a.status = 0) AS to_check_num, (SELECT COUNT(*) FROM ticket_case b WHERE b.schedule_id = (?) AND b.status = 1) AS checked_num";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "ii", $scheduleID, $scheduleID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "data" => NULL));
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

            //echo $this->DBController->getErrorCode();
            echo json_encode(array("success" => FALSE, "data" => NULL));

        }

    }

}
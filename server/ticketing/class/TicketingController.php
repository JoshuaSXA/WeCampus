<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-13
 * Time: 23:35
 */

include_once '../../lib/DBController.php';


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


    # 获取当前用户即将出行的行程数量、历史数量、违规的数量
    public function getUserTripNumber() {



    }


    # 对于一个学校来说，获取车的种类，包括线路名、起点终点、备注
    public function getBusRouteList() {



    }


    # 给定车的ID，把第二个接口的内容加上班次列表（开票时间、结票时间、剩余票数）
    public function getBusTripList() {


    }


    # 发给班次和车种类的编号，返回起点终点和班次时间和备注和剩余票数、开票结票时间、最晚退票时间、是否买票了
    public function getBusTripDetail() {


    }


    # 班次id、openid———>订票（惩罚）
    public function orderTripTicket() {



    }


    # 验票接口：收到oid和班次id，检查是否有效，返回给你加密信息
    public function verifyTripTicket() {



    }


    # 返回起点终点、班次时间、车的名字、当前票的状态
    public function getUserTripList() {



    }


    # 取消订票的接口、oid和班次id
    public function cancelTripTicket() {



    }

}
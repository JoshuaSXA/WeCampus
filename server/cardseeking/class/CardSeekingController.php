<?php
include_once '../../lib/DBController.php';
include_once '../../lib/UploadImageController.php';
include_once '../../lib/TemplateMessageController.php';
include_once '../../lib/FormidPoolController.php';

/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-02-27
 * Time: 13:48
 */

/********************************************
 * 找卡模块控制类
 * 找卡case的状态： 0——未读，1——已读，2——已读并感谢  case_status
 *******************************************/

class CardSeekingController
{
    private $schoolID;

    private $openID;

    // 缓存路径
    private $cachePath = '../../data/cache/';

    // 图片的保存路径
    private $savePath = '../../data/lost_card/';

    // 构造函数
    function __construct() {

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

    // 检查该失主是否在已注册用户中，且认证状态=1
    private function checkIfOwnerExists($ownerCardID) {

        $sql = "SELECT avatar, nickname, open_id FROM user WHERE school_id=(?) AND student_id=(?) AND auth=1";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "is", $this->schoolID, $ownerCardID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                return array("success" => FALSE, "exists" => FALSE, "owner_info" => array());

            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            if(count($retValue) == 0) {

                return array("success" => TRUE, "exists" => FALSE, "owner_info" => array());

            } else {

                return array("success" => TRUE, "exists" => TRUE, "owner_info" => $retValue[0]);

            }

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            return array("success" => FALSE, "exists" => FALSE, "owner_info" => array());

        }

    }


    // 向失主发送模板消息
    private function sendLoserTemplateMessage($ownerCardId, $detail) {

        $redis = new Redis();

        $redis->connect('127.0.0.1', 6379, 10);

        $cardSeekingKey = $this->schoolID . "_" . $ownerCardId;

        $retVal = $redis->get($cardSeekingKey);

        if ($retVal) {

            return array("success" => TRUE, "exists" => TRUE);

        } else {

            $checkRes = $this->checkIfOwnerExists($ownerCardId);

            if(!$checkRes["success"]) {

                return array("success" => FALSE, "exists" => FALSE);

            }

            if(!$checkRes["exists"]) {

                return array("success" => TRUE, "exists" => FALSE);

            }

            $ownerOpenID = $checkRes["owner_info"]["open_id"];

            $formIdPoolControllerObj = new FormidPoolController("formId", $ownerOpenID);

            $formID = $formIdPoolControllerObj->getFormId();

            if(!$formID) {

                return array("success" => FALSE, "exists" => TRUE);

            }

            $jumpPage = "pages/functionalPages/index/index";
            
            $keyword2 = "有人在" . $detail['lost_place'] . "捡到了你的校园卡喏~ 赶紧去微校高校生活-卡丢了看看吧";

            $templateMessageData = array(
                'openid' => $ownerOpenID,
                'template_id' => "3WNb1KROWstAN9-cNilbBLU15O3kxRHw7IrWcrim2ic",
                'form_id' => $formID,
                'page' => $jumpPage,
                'data' => array(
                    'keyword1' => array("value" => $detail['lost_time']),
                    'keyword2' => array("value" => $keyword2),
                ),
                'emphasis_keyword' => ""
            );

            $templateMessageControllerObj = new TemplateMessageController($templateMessageData);

            if(!$templateMessageControllerObj->sendTemplateMessage()) {

                return array("success" => FALSE, "exists" => TRUE);

            }

            // 今日用户还未被找到卡
            $redis->set($cardSeekingKey, "yes");
            // 设置过期时间
            $expireTime = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
            // 设置键的过期时间
            $redis->expireAt($cardSeekingKey, $expireTime);

            return array("success" => TRUE, "exists" => TRUE);

        }


        $redis->close();

    }


    // 创建找卡案例
    public function createCardSeekingCase(){

        $ownerCardID = $_REQUEST['owner_card_id'];

        $ownerName = $_REQUEST['owner_name'];

        $lostPlace = $_REQUEST['lost_place'];

        $lostTime = $_REQUEST['lost_time'];

        $cardImageUrl = $_REQUEST['card_url'];

        $agreeToCall = $_REQUEST['agree_to_call'];

        // 首先检查是否有该用户
        $checkOwner = $this->checkIfOwnerExists($ownerCardID);

        if(!$checkOwner['success']) {

            echo json_encode(array("success" => FALSE, "owner_info" => array()));

            return;

        }

        $ownerExists = $checkOwner['exists'];

        $ownerInfo = $checkOwner['owner_info'];

        $sql = "INSERT INTO cardseeking_case (school_id, finder, student_id, student_name, lost_place, lost_time, card_image, phone_call) VALUES ((?), (?), (?), (?), (?), (?), (?), (?))";

        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "issssssi", $this->schoolID, $this->openID, $ownerCardID, $ownerName, $lostPlace, $lostTime, $cardImageUrl, $agreeToCall);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)){
                // 查询失败
                echo json_encode(array("success" => FALSE, "owner_exists" => $ownerExists, "owner_info" => $ownerInfo));
                return;

            }

            rename($this->cachePath . $cardImageUrl, $this->savePath . $cardImageUrl);

            // 查询成功，返回结果
            echo json_encode(array("success" => TRUE, "owner_exists" => $ownerExists, "owner_info" => $ownerInfo), JSON_UNESCAPED_UNICODE);

            $this->sendLoserTemplateMessage($ownerCardID, array("lost_time" => $lostTime, "lost_place" => $lostPlace));

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            echo $this->DBController->getErrorCode();

            echo json_encode(array("success" => FALSE, "owner_exists" => $ownerExists, "owner_info" => $ownerInfo));

        }

    }


    // 上传校园卡照片
    public function uploadCardImage() {

        // 实例化图片上传类
        $uploadImageControllerObj = new UploadImageController();

        // 设置图片存储路径
        $uploadImageControllerObj->setSavePath($this->cachePath);

        // 设置图片压缩程度
        $uploadImageControllerObj->setCompressValue(70);

        // 图片名称，由于前端不能传过来open_id，这里采用随机数和时间戳避免文件名冲突
        $imgName = 'lost_card_' . (string)rand(1000, 9999) . '_' . time() . '.jpg';

        // 上传图片
        if($uploadImageControllerObj->uploadImg('card_img', $imgName)) {

            echo json_encode(array('success' => TRUE, 'temp_url' => $imgName), JSON_UNESCAPED_UNICODE);

        } else {

            echo json_encode(array('success' => FALSE, 'temp_url' => ''), JSON_UNESCAPED_UNICODE);
        }

        return;

    }


    // 我找卡的列表，返回我找到的卡，分页查询
    public function getMyFoundCardList() {

        $pageBorder = $_GET['page_border'];

        // 初始化设置page_border的值为最大值
        if($pageBorder == -1) {

            $pageBorder = 4294967294;

        }

        $sql = "SELECT * FROM cardseeking_case WHERE finder=(?) AND case_id<(?) ORDER BY case_id DESC LIMIT 10";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "si", $this->openID, $pageBorder);

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


    // 找到我的卡的列表, 分页查询
    public function getMyCardFinderCaseList() {

        $pageBorder = $_GET['page_border'];

        // 初始化设置page_border的值为最大值
        if($pageBorder == -1) {

            $pageBorder = 4294967294;

        }

        $sql = "SELECT a.case_id, a.finder, a.lost_place, a.lost_time, a.phone_call, a.case_status, b.avatar, b.nickname FROM cardseeking_case a INNER JOIN user b ON a.finder = b.open_id WHERE a.student_id in (SELECT s.student_id FROM user s WHERE s.open_id=(?) AND auth=1) AND a.case_id<(?) ORDER BY a.case_id DESC LIMIT 10";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "si", $this->openID, $pageBorder);

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


    // 感谢 && 已读
    public function changeCaseStatus() {

        $caseID = $_REQUEST['case_id'];

        $newStatus = $_REQUEST['new_status'];

        $sql = "UPDATE cardseeking_case SET case_status=(?) WHERE case_id=(?)";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "ii", $newStatus, $caseID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE));
                return;
            }

            // 返回结果
            echo json_encode(array("success" => TRUE));

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            echo json_encode(array("success" => FALSE));

        }

    }


    // 捡卡的详情页面
    public function getCardSeekingCaseDetail() {

        $caseID = $_GET['case_id'];

        $sql = "SELECT a.finder, a.student_id, a.student_name, a.lost_place, a.lost_time, a.card_image, a.phone_call, a.case_status, b.phone, b.nickname, b.avatar FROM cardseeking_case a INNER JOIN user b ON a.finder = b.open_id WHERE a.case_id=(?)";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "i", $caseID);

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

    // 初始化 获取用户未读信息的个数
    public function getUncheckedMessageNum(){

        $sql = "SELECT COUNT(*) AS num FROM cardseeking_case a WHERE a.school_id=(?) AND a.case_status=0 AND a.student_id in (SELECT b.student_id FROM user b WHERE b.open_id=(?) AND b.auth=1)";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "is", $this->schoolID, $this->openID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "message_num" => 0));

                return;

            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            echo json_encode(array("success" => TRUE, "message_num" => $retValue[0]['num']));


            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            echo json_encode(array("success" => FALSE, "message_num" => 0));

        }

    }

    // 初始化 获取用户捡到他人卡的次数，被捡到卡的次数，捡到卡且已经被感谢的次数
    public function getUserInitialInfo() {

        $sql = "SELECT (SELECT COUNT(*) FROM cardseeking_case a WHERE a.finder=(?)) AS find_time, (SELECT COUNT(*) FROM cardseeking_case b INNER JOIN user c ON b.student_id=c.student_id WHERE c.auth=1 AND c.open_id=(?)) AS found_time, (SELECT COUNT(*) FROM cardseeking_case d WHERE d.finder=(?) AND d.case_status=2) AS finish_time";

        // 创建预处理语句
        $stmt = mysqli_stmt_init($this->DBController->getConnObject());

        if(mysqli_stmt_prepare($stmt, $sql)){

            // 绑定参数
            mysqli_stmt_bind_param($stmt, "sss", $this->openID, $this->openID, $this->openID);

            // 执行查询
            if(!mysqli_stmt_execute($stmt)) {

                echo json_encode(array("success" => FALSE, "info" => array()));
                return;

            }

            // 获取查询结果
            $result = mysqli_stmt_get_result($stmt);

            // 获取值
            $retValue =  mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 返回结果
            echo json_encode(array("success" => TRUE, "info" => $retValue[0]));

            // 释放结果
            mysqli_stmt_free_result($stmt);

            // 关闭mysqli_stmt类
            mysqli_stmt_close($stmt);

        } else {

            echo json_encode(array("success" => FALSE, "info" => array()));

        }

    }

}
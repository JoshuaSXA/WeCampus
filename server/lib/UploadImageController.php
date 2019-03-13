<?php

/**
 * 该类负责控制用户上传图片
 */
class UploadImageController {


    // 上传图片的类型
    private $allowType;

    // 存储图片的路径
    private $savePath;

    // 压缩图片
    private $compressImage;

    // 构造函数
    function __construct() {

        // 默认支持png、jpg、jpeg三种格式
        $this->allowType = array('image/png', 'image/jpg', 'image/jpeg');

        // 默认为缓存路径
        $this->savePath = '../data/cache/';

        // 图片压缩程度，默认值为100，即不压缩
        $this->compressImage = 100;

    }

    // 设置上传图片的路径
    public function setSavePath($path) {

        $this->savePath = $path;

    }

    // 设置上传图片的格式
    public function setImageType($imgType) {

        // 参数必须是数组
        if(is_array($imgType)) {

            return FALSE;

        } else {

            $this->allowType = $imgType;

            return TRUE;

        }

    }

    // 设置是否压缩文件（图片）
    public function setCompressValue($compressValue = 100) {

        $this->compressImage = $compressValue;

    }


    // 获取上传文件
    public function uploadImg($imgUploadName, $imgName) {

        if($_FILES[$imgUploadName]['error'] > 0){
            // echo "Error code: ". $_FILES[$imgUploadName]['error'];
            return FALSE;

        } else {

            // 获取图片的临时存储路径
            $tempPath = $_FILES[$imgUploadName]['tmp_name'];


            // 文件的保存完整路径
            $imgPath = $this->savePath . $imgName;

            // 从临时文件夹缓存到本地文件
            $this->transferTempImage($tempPath, $imgPath);

            return TRUE;

        }

    }


    // 从临时路径保存到本地文件
    private function transferTempImage($imgSrc, $imgDst) {

        move_uploaded_file($imgSrc, $imgDst);

        return;

        // 获得图片的长宽和类型
        list($width,$height,$type)=getimagesize($imgDst);

        // 获取新的画布
        $imageWp=imagecreatetruecolor($width, $height);

        // 创建临时图片
        $image = imagecreatefromjpeg($imgDst);

        // 复制图片到画布
        imagecopyresampled($imageWp, $image, 0, 0, 0, 0, $width, $height, $width, $height);

        // 压缩图片
        imagejpeg($imageWp, $imgDst, $this->compressImage);

        // 销毁图片
        imagedestroy($imageWp);

        return;
    }


}

?>
<?php
/**
 * Created by PhpStorm.
 * User: shenxiaoang
 * Date: 2019-03-01
 * Time: 15:14
 */

/*
 *  实现对对应模块的用户的formID的存储池的管理
 * */

class FormidPoolController
{

    private $formIdPoolKey;

    // 单条formId 的过期时间，六天半
    private $expireTime = 7 * 24 * 3600 - 3600;

    private $redis;

    function __construct($moduleTag, $userOpenId)
    {

        $this->formIdPoolKey = $moduleTag . '_' . $userOpenId;

        // 实例化一个redis类
        $this->redis = new Redis();

        // 连接到redis_server，端口号为6379， timeout为10s
        $this->redis->connect('127.0.0.1', 6379, 10);

    }


    function __destruct() {

        // 断开与redis_server的连接
        $this->redis->close();

    }


    // 获取当前formId存储池中可用的form_id个数
    public function getAvailFormIdLen() {

        $formIdPoolSize = $this->redis->llen($this->formIdPoolKey);

        $formIdKeyList = $this->redis->lrange($this->formIdPoolKey, 0, $formIdPoolSize - 1);

        $invalidFormIdCounter = 0;

        foreach($formIdKeyList as &$formIdKey) {

            $formId = $this->redis->get($formIdKey);

            if($formId) {
                break;
            }

            ++$invalidFormIdCounter;

        }

        return $formIdPoolSize - $invalidFormIdCounter;

    }


    // 获取一个可用的form_id
    public function getFormId() {

        while ($this->redis->llen($this->formIdPoolKey)) {

            $formIdKey = $this->redis->rpop($this->formIdPoolKey);

            $formId = $this->redis->get($formIdKey);

            if($formId) {

                $this->redis->del($formIdKey);

                return $formId;

            }
        }

        $this->redis->del($this->formIdPoolKey);

        return "";

    }


    // 插入一个form_id到存储池中
    public function addFormId($formId) {

        $formIdKey = $this->formIdPoolKey . '_' . (string)time();

        $this->redis->setex($formIdKey, $this->expireTime, $formId);

        return $this->redis->lpush($this->formIdPoolKey, $formIdKey);

    }

}
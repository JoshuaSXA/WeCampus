/**************************
 *
 * 此js文件负责实现发送模板消息
 * 定时从数据库中查询数据数据
 *
 *************************/
// 定义全局变量

// 模板消息ID列表
var templateIDList = {
    "carpool" : "dRcj03kSEQMmKYwhVjQRbvq5Lsbeuj2JKTSSsHiOC1I",
    "cardseek" : "3WNb1KROWstAN9-cNilbBLU15O3kxRHw7IrWcrim2ic",
};

var jumpUrlList = {
    "carpool" : "pages/functionalPages/index/index",
    "carseek" : "pages/functionalPages/index/index",
}

var carpoolMessage = "您参与/发起的拼车状态已更新，点击进入微校高校生活查看详情";

// 加载日志模块
var log4js = require('log4js');

// 设置日志配置
log4js.configure({
    appenders: {
        xcLogFile: {
            type: "dateFile",
            filename: __dirname +'/logs/template_message/LogFile',//
            alwaysIncludePattern: true,
            pattern: "-yyyy-MM-dd.log",
            encoding: 'utf-8',//default "utf-8"，文件的编码
            maxLogSize: 104800
        },
        xcLogConsole: {
            type: 'console'
        }
    },
    categories: {
        default: {
            appenders: ['xcLogFile'],
            level: 'all'
        },
        xcLogFile: {
            appenders: ['xcLogFile'],
            level: 'all'
        },
        xcLogConsole: {
            appenders: ['xcLogConsole'],
            level: log4js.levels.ALL
        }
    }
});

// 加载日志
module.exports = log4js.getLogger('xcLogConsole');
var logger = log4js.getLogger('log_file');


// 时间模块
var sd = require('silly-datetime');

// request模块
var request = require('request');

// 串行化模块
var async = require('async');



// redis对象
const redis = require("redis");

// 建立一个redis客户端连接
const client = redis.createClient(6379, 'localhost');

client.on('error', function (err) {
    // 输出到日志
});

// MySQL数据库配置

var mysql = require('mysql');

var connection = mysql.createConnection({

    host     : 'localhost',
    user     : 'wecampus',
    password : 'J6MQYfVH8MibCrK7H56KUxVWezteDe',
    database : 'wecampus'

});

// 建立与数据库的连接
connection.connect();

// 循环定时查询数据库


var schedule = require('node-schedule');

var rule = new schedule.RecurrenceRule();

// 每天晚上21点发消息推送
var hours = [23];
rule.hour = hours;

schedule.scheduleJob(rule, function(){

    var time = sd.format(new Date(), 'YYYY-MM-DD HH:mm');

    connection.query('SELECT * FROM template_message WHERE send_time = ' + "'" + time + "'", function (error, results, fields) {

        if(error) {

            // 输出错误到日志文件
            logger.error("SQL query error!");

        }

        getAccessToken(client, function(access_token){

            if(access_token == null) {

                logger.error("Get access_token failed!");
                return;
            }

            var data;

            // 遍历结果
            for(var i = 0; i < results.length; ++i) {

                data = results[i]

                var formIdPoolKey = "formId_" + data.open_id;

                getUserFormId(client, formIdPoolKey, data, function (form_id, data) {

                    if(form_id == null) {

                        logger.warn(data.module_tag + " : Not enough formID for " + data.open_id);

                        return;

                    }

                    // 发送模板消息
                    sendTemplateMessage(data, form_id, access_token);

                })

            }

        });


    });

});


function sendTemplateMessage(data, form_id, access_token) {


    // 拼接URL，作为小程序跳转时的参数传递
    var transferPage = jumpUrlList[data.module_tag];


    var postData = {

        "touser" : data.open_id,

        "template_id" : templateIDList[data.module_tag],

        "page" : transferPage,

        "form_id" : form_id,

        "data" : {

            "keyword1": {
                "value": data.keyword_1
            },
            "keyword2": {
                "value": data.keyword_2
            },
            "keyword3": {
                "value": data.keyword_3
            } ,
            "keyword4": {
                "value": (data.module_tag == "carpool" ? carpoolMessage : data.keyword_4)
            },
            "keyword5": {
                "value": data.keyword_5
            }
        },


    }


    request({

        url: "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" + access_token,
        method: "POST",
        json: true,
        headers: {
            "content-type": "application/json",
        },
        body: postData

    }, function(error, response, body) {
        console.log(body);

        if (!error && response.statusCode == 200) {

            logger.info(data.module_tag + " : Template message sending succeeded!");

        } else {

            // 输出错误日志
            logger.error(data.module_tag + " : Template message sending failed for " + data.open_id);
        }

    });

}



// 获取access_token
function getAccessToken(redis_client, callback) {

    redis_client.get('access_token', function(err, value){
        if(err) {
            // 写出错误到日志

            logger.error("Reids get access_token error!");

            callback(null);

        }

        if(value) {

            callback(value);

        } else {

            refreshAccessToken(redis_client, callback);

        }

    });

}


// 更新access_token，并存储到redis中
function refreshAccessToken(redis_client, callback) {

    const appID = "wxc06d3e6075749a79";

    const secret = "24882e2077bcb8f7b724eff5100aa852";

    request({

        url: "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" + appID + "&secret=" +secret,
        method: "GET",
        json: true,
        headers: {
            "content-type": "application/json",
        }

    }, function(error, response, body) {

        if (!error && response.statusCode == 200) {

            // 存储到redis
            redis_client.set("access_token", body.access_token);

            // 设置过期时间
            redis_client.expire("access_token", body.expires_in - 300);


            callback(body.access_token);

        } else {

            // 输出错误日志
            logger.error("Access_token request error");

            callback(null);
        }

    });
}


// 获取form_id
function getUserFormId(redis_client, formIdPoolKey, data, callback) {

    redis_client.rpop(formIdPoolKey, function(err, value){

        if(err) {
            // 写出错误到日志

            logger.error("Reids rpop " + formIdPoolKey + " error!");

            callback(null, data);

        }

        if(value) {

            redis_client.get(value, function (err, res) {

                if(err) {

                    logger.error("Reids get " + value + " error!");
                    callback(null, data);

                }

                if(res) {

                    redis_client.del(value);
                    callback(res, data);

                } else {

                    getUserFormId(redis_client, formIdPoolKey, callback);

                }
            });

        } else {

            callback(null, data);
        }

    });

}













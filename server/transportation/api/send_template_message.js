/**************************
 *
 * 此js文件负责实现发送模板消息
 * 定时从数据库中查询数据数据
 *
 *************************/

// 定义全局变量

// 模板消息ID
var templateID = "ZH8VsjP3Qp-nqu9BfRl8eH-6gDSNwUzCbuf6v6KE2fQ";

// 温馨提示
var warmPrompt = "这是一条温馨提示！";

// 加载日志模块
var log4js = require('log4js');

// 设置日志配置
log4js.configure({
    appenders: {
        xcLogFile: {
            type: "dateFile",
            filename: __dirname +'/logs/LogFile',//
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
  user     : 'admin',
  password : 'WeCampus1234',
  database : 'wecampus'

});

// 建立与数据库的连接
connection.connect();

// 循环定时查询数据库


var schedule = require('node-schedule');
 
var rule = new schedule.RecurrenceRule();
var times = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55];
rule.minute  = times;

schedule.scheduleJob(rule, function(){

	var time = sd.format(new Date(), 'YYYY-MM-DD HH:mm');

	connection.query('SELECT * FROM trans_message WHERE time = ' + "'" + time + "'", function (error, results, fields) {

		if(error) {

			// 输出错误到日志文件
			logger.error("SQL query error!");

		}



		getAccessToken(client, function(access_token){

			if(access_token == null) {

				console.log("none access_token");
				return;
			}

			// 遍历结果
			for(var i = 0; i < results.length; ++i) {
				//console.log(results[i]);

				// 发送模板消息
				sendTemplateMessage(results[i], access_token);
				//console.log(result[i].openid);

			}

		});
		

	});

});


function sendTemplateMessage(data, access_token) {
	//console.log(data);

	// 这里要判断日期是否加1
	var deptTime = (data.keyword_5).split(':');
	var deptDateArray = ((data.time).split(' ')[0]).split('-');
	var deptDate = '';

	if(deptTime[0] == '0' && parseInt(deptTime[1]) < 15) {
		var date = new Date(parseInt(deptDateArray[0]), parseInt(deptDateArray[1]), parseInt(deptDateArray[2])); 

		date = new Date(date.getTime() + 24 * 60 * 60 * 1000);

		deptDate = date.getFullYear() + '年' + (date.getMonth() + 1) + '月' + date.getDate() + '日'; 
 
	} else {

		deptDate = deptDateArray[0] + '年' + parseInt(deptDateArray[1]) + '月' + parseInt(deptDateArray[2]) + '日';

	}

	var transferPage = "pages/transportation/index/index";

	/*
	// 拼接URL，作为小程序跳转时的参数传递
	transferPage += ("?scheduleTime=" + data.keyword_5);
	transferPage += ("&routeName=" + data.keyword_1);
	transferPage += ("&deptDate=" + deptDate);
	transferPage += ("&routeID=" + data.route_id);
	transferPage += ("&boundFor=" + data.keyword_3);
	transferPage += ("&deptStop=" + data.keyword_2);
	transferPage += ("&deptStopID=" + data.station_id);
	transferPage += ("&patternID=" + data.pattern_id);
	*/

	var postData = {

		"touser" : data.open_id,

		"template_id" : templateID,

		"page" : transferPage,

		"form_id" : data.form_id,

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
          		"value": warmPrompt
      		},
      		"keyword5": {
          		"value": data.keyword_5
      		}
		},

		"emphasis_keyword": "keyword5.DATA"

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
            
            logger.info("Success!");

        } else {

        	// 输出错误日志
        	logger.error("Template message request error!");
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














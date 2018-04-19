<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.thinkphp.cn>
// +----------------------------------------------------------------------

/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */
return array(

    // 预先加载的标签库
    'TAGLIB_PRE_LOAD' => 'OT\\TagLib\\Article,OT\\TagLib\\Think',

    /* 主题设置 */
    'DEFAULT_THEME' => 'default', // 默认模板主题名称


    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
        '__IMG__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/images',
        '__CSS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/css',
        '__JS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/js',
        '__ZUI__' => __ROOT__ . '/Public/zui',
        '__CORE_IMAGE__'=>__ROOT__.'/Application/Core/Static/images',
        '__CORE_CSS__'=>__ROOT__.'/Application/Core/Static/css',
        '__CORE_JS__'=>__ROOT__.'/Application/Core/Static/js',
        '__APPLICATION__'=>__ROOT__.'/Application/'
    ),
    //微信接口银行编号
    "BANK_CODE"=>array(
    		'1002'=>"工商银行",
    		'1005'=>"农业银行",
    		'1026'=>"中国银行",
    		'1003'=>"建设银行",
    		'1001'=>"招商银行",
    		'1066'=>"邮储银行",
    		'1020'=>"交通银行",
    		'1004'=>"浦发银行",
    		'1006'=>"民生银行",
    		'1009'=>"兴业银行",
    		'1010'=>"平安银行",
    		'1021'=>"中信银行",
    		'1025'=>"华夏银行",
    		'1027'=>"广发银行",
    		'1022'=>"光大银行",
    		'1032'=>"北京银行",
    		'1056'=>"宁波银行",
    ),
    "WX_CONFIG"=>array(
    	'token'=>'kCH5vkcuJEv6kvwhntlD',
    	'encodingaeskey'=>'30xKKBy8CdyJfnRouIaLuZRMSJ7atJrKXtVmlvPUeT2',
    	'appid'=>'wx1040c62d14e0868f',
    	'appsecret'=>'51710e0a7abd047ba80296bb1abc6136',
    	'logcallback'=>'http://yujitj.qmga365.com/index.php',
    ),
    "MALL_DB_CONFIG1"=>array('DB_TYPE'   => 'mysql', // 数据库类型
    		'DB_HOST'   => 'localhost', // 服务器地址
    		'DB_NAME'   => 'qpbak', // 数据库名
    		'DB_USER'   => 'root', // 用户名
    		'DB_PWD'    => 'root',  // 密码
    		'DB_PORT'   => '3306', // 端口
    		'DB_PREFIX' => '' // 数据库表前缀)
    ),
    "MALL_DB_CONFIG"=>array('DB_TYPE'   => 'mysql', // 数据库类型
 										'DB_HOST'   => 'rm-wz97q89c7nx24vq4lo.mysql.rds.aliyuncs.com', // 服务器地址
 										'DB_NAME'   => 'bkgame', // 数据库名
										'DB_USER'   => 'tgame', // 用户名
 										'DB_PWD'    => 'Yujigame123',  // 密码
 										'DB_PORT'   => '3306', // 端口
 										'DB_PREFIX' => '' // 数据库表前缀)
     									),
"MALL_R_DB_CONFIG"=>array('DB_TYPE'   => 'mysql', // 数据库类型
     											'DB_HOST'   => 'rm-wz97q89c7nx24vq4lo.mysql.rds.aliyuncs.com', // 服务器地址
     											'DB_NAME'   => 'game', // 数据库名
     											'DB_USER'   => 'mall', // 用户名
     											'DB_PWD'    => 'Yuji1518GameCenter',  // 密码
     											'DB_PORT'   => '3306', // 端口
     											'DB_PREFIX' => '' // 数据库表前缀)
     									)
);
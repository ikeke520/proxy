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
    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX' => 'onethink_', // 缓存前缀
    'DATA_CACHE_TYPE' => 'File', // 数据缓存类型
    'URL_MODEL' => 3, //URL模式
    /* 文件上传相关配置 */
    'DOWNLOAD_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' => 5 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Download/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //下载模型上传配置（文件上传类配置）

    /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' => 2 * 1024 * 1024, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Picture/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）


    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
        '__IMG__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/images',
        '__CSS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/css',
        '__JS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/js',
        '__ZUI__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/zui',
    ),
    'UPDATE_PATH'=>'./Data/Update/',
    'CLOUD_PATH'=>'./Data/Cloud/',
    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => 'opencenter_admin', //session前缀
    'COOKIE_PREFIX' => 'opencenter_admin_', // Cookie前缀 避免冲突
    'VAR_SESSION_ID' => 'session_id',    //修复uploadify插件无法传递session_id的bug

    /* 后台错误页面模板 */
    'TMPL_ACTION_ERROR' => MODULE_PATH . 'View/default/Public/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => MODULE_PATH . 'View/default/Public/success.html', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE' => MODULE_PATH . 'View/default/Public/exception.html',// 异常页面的模板文件
    'DEFAULT_THEME' => 'default', // 默认模板主题名称
    
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
    'MALL_DBCONFIG' => array(
    		'DB_TYPE'   => 'mysql', // 数据库类型
    		'DB_HOST'   => 'rm-wz97q89c7nx24vq4lo.mysql.rds.aliyuncs.com', // 服务器地址
    		'DB_NAME'   => 'game', // 数据库名
    		'DB_USER'   => 'mall', // 用户名
    		'DB_PWD'    => 'Yuji1518GameCenter',  // 密码
    		'DB_PORT'   => '3306', // 端口
    		'DB_PREFIX' => '' // 数据库表前缀)
    ),
    "MALL_TEST"=>array(/* 数据库配置 */
	'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => 'rm-wz97q89c7nx24vq4lo.mysql.rds.aliyuncs.com', // 服务器地址
	'DB_NAME'   => 'bkgame', // 数据库名
	'DB_USER'   => 'tgame', // 用户名
	'DB_PWD'    => 'Yujigame123',  // 密码
	'DB_PORT'   => '3306', // 端口
	'DB_PREFIX' => '', // 数据库表前缀	)
	)
);
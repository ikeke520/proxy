<?php
/**
 * 所属项目 110.
 * 开发者: 陈一枭
 * 创建日期: 2014-11-18
 * 创建时间: 10:14
 * 版权所有 想天软件工作室(www.ourstu.com)
 */

return array(
    //模块名
    'name' => 'Statistics',
    //别名
    'alias' => '数据中心',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '数据中心，主要用于统计数据',
    //开发者
    'developer' => '湖南杠杆科技有限公司',
    //开发者网站
    'website' => 'http://www.ourstu.com',
    //前台入口，可用U函数
    'entry' => 'Statistics/index/index',
	
    'admin_entry' => 'Admin/index/index',

    'icon'=>'people',

    'can_uninstall' => 0

);
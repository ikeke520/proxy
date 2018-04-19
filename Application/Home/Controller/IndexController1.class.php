<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Home\wxjsModel;
use Home\wechatModel;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController
{

    //系统首页
    public function index()
    {
        hook('homeIndex');
        $default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != '') {
            redirect(get_nav_url($default_url));
        }
		//$wxjs=new \Home\Model\wxjsModel("wx1040c62d14e0868f","51710e0a7abd047ba80296bb1abc6136");
		//$wxconfig=$wxjs->getSignPackage();
		$option=array('appid'=>'wx1040c62d14e0868f','appsecret'=>'51710e0a7abd047ba80296bb1abc6136');
		$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		
		$wx=new \Home\Model\wechatModel($option);
		$a=$wx->getJsTicket();
		$wxconfig=$wx->getJsSign($url);
		
		/* print_r($a);echo "<br/>";
		print_r($wxconfig);exit; */
		
		//$wxshare['title']="娱记麻将";
		$wxshare['title']="【娱记游戏】官方网站-长沙麻将|跑得快|常德跑胡子|新宁麻将";
		$wxshare['url']=$url;
		$c=C('TMPL_PARSE_STRING');
		$wxshare['imgUrl']='http://'.$_SERVER['HTTP_HOST'].$c['__IMG__']."/yjmj.png";
		$wxshare['desc']="娱记麻将,专心、专注、专业做本土化棋牌游戏";
		//print_r($wxshare);exit;
		$this->assign("wxconfig",$wxconfig);
		$this->assign("wxshare",$wxshare);
		$this->assign();
        $this->display();
    }

    public function h5game(){
		hook('homeIndex');
        $default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
		if ($default_url != '') {
            redirect(get_nav_url($default_url));
        }
		//$wxjs=new \Home\Model\wxjsModel("wx1040c62d14e0868f","51710e0a7abd047ba80296bb1abc6136");
		//$wxconfig=$wxjs->getSignPackage();
		$option=array('appid'=>'wx1040c62d14e0868f','appsecret'=>'51710e0a7abd047ba80296bb1abc6136');
		$url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		
		$wx=new \Home\Model\wechatModel($option);
		$a=$wx->getJsTicket();
		$wxconfig=$wx->getJsSign($url);
		
		/* print_r($a);echo "<br/>";
		print_r($wxconfig);exit; */
		
		//$wxshare['title']="娱记麻将";
		$wxshare['title']="【娱记游戏】H5小游戏，释放你的无聊";
		$wxshare['url']=$url;
		$c=C('TMPL_PARSE_STRING');
		$wxshare['imgUrl']='http://'.$_SERVER['HTTP_HOST'].$c['__IMG__']."/yjmj.png";
		$wxshare['desc']="娱记H5小游戏，让等候时间变得更加有趣";
		//print_r($wxshare);exit;
		$this->assign("wxconfig",$wxconfig);
		$this->assign("wxshare",$wxshare);
        $this->display();
		
    }

}
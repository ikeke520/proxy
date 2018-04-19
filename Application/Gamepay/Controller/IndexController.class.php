<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Gamepay\Controller;

use Think\Controller;
use Think\wechat;
/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends Controller
{

    //系统首页
    public function index()
    {
		//获取信息
		 if(!is_weixin()){
			echo "请用微信打开该页面";exit;
		} 
		
		$wxuser=wxconstruct();	
		//print_r($wxuser);exit;
		$url="http://passport2.stg3.1768.com/pass-info/oauth2/3rdPartAuthView.shtml";
		//$url="http://dl.91yuji.com/index.php?s=/Gamepay/index/test.html";
		$signkey="d1bd3f7d099b49e7a77ea8e32a69c2d5";
		$param['client_id']="EX_000130";
		$param['response_type']="code";
		$param['media_source']="dngame";
	
		$param['openId']=$wxuser['openid'];
		$param['timestamp']=Date("Y-m-d H:i:s",time());
		$param['redirectUrl']="http://9test8-wap.stg3.1768.com/?act=landing&st=goto_page&track_u=dngame&goUrl=http%3a%2f%2f9test8-wap.stg3.1768.com%2f%3fact%3dgame_collection%26track_u%3ddngame";
		$param['platform']="ADNDROID";
		$param['alias']="TEST3";
		$param['algorithm']="SHA1";
		$param['userInfo']="15898516559;;;;;";
		$sign=SHA1($param['client_id'].$param['openId'].$param['timestamp'].$signkey);
		//echo $param['client_id']+$param['openId']+$param['timestamp']+$param['userInfo']+$signkey;exit;
		$param['sign']=$sign;
		
		
		//get
		$redirectUrl=urlencode($param['redirectUrl']);
		//$redirectUrl=$param['redirectUrl'];
		$postUrl = $url."?client_id=EX_000130&response_type=code&media_source=dngame&sign=".$sign."&openId=".$param['openId']."&amp;timestamp=".$param['timestamp']."&redirectUrl=".$redirectUrl."&platform=ANDROID&alias=test3";
		 echo $postUrl;exit;
		 $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
		
		print_r($data);exit;
		
		$r=request_post($url,$param);
		print_r($r);exit;
		
		$default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != '') {
            redirect(get_nav_url($default_url));
        }

        $this->display();
    }
	
	public function test(){
		print_r($_REQUEST);
		exit;
	}
	
}
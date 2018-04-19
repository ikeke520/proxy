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
use Common\Api;
use Gamepay\Model;
/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class PaynowController extends Controller
{
		
    /* 空操作，用于输出404页面 */
    public function _empty()
    {
        $this->redirect('Index/index');
    }

     protected function _initialize()
    {
        //读取站点配置
        $config = api('Config/lists');
        C($config); //添加配置

        if (!C('WEB_SITE_CLOSE')) {
            $this->error('站点已经关闭，请稍后访问~');
        }
    } 

   public function index(){
   
	//解密提交数据
	
	//存入订单数据表
	$data['uid']="1";
	$data['timestamp']="1434233211";
	$data['tradeno']="33333";
	$data['payno']="1fdfsdafsad1123124";
	$data['redirect_url']="fdafdsa";
	$data['ownno']="100.00";
	$data['status']="0";
	$data['ownid']="13311111";
	
	//$r=M("gamepayorder")->add($data);
	if(!$r){
		//
	}
	//通过网页授权获取openid
	
	
	//调用微信接口统一下单
	$wxpay=D("Gamepay/Html5wxpay");
	$r=$wxpay->test();
	$z=$wxpay->createJsBizPackage("", "0.1", "TEST0000001", "娱记游戏充值", "http://gagooo.com", time());
	print_r($z);exit;
	
	//提交微信
	
	//返回
   }
}

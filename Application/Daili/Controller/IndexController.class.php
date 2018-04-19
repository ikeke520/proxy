<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Daili\Controller;


/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends DailiController
{

    //系统首页
    public function index()
    {
        hook('homeIndex');
		$default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != '') {
            redirect(get_nav_url($default_url));
        }

        $this->display();
    }
    public function dailisub(){
    	$data['name']=I('name');
    	$data['phone']=I('phone');
    	$data['areaid']=I('areaid');
    	$data['weixin']=I('weixin');
    	$data['type']=I('type');
    	$data['time']=time();
    	//print_r($data);exit;
    	if(empty($data['phone'])){
    		$this->error("未填写电话号码，请重新填写");
    		$this->index();exit;
    	}
    	$r=M('daili')->where('phone="'.$data['phone'].'"')->find();
    	if($r){
    		$this->error("该电话号码已经申请，请勿重复申请");
    	}
    	print_r($data);
    	$r=M('daili')->add($data);
    	if($r){
    		$this->success("申请成功，稍后由娱记游戏客服联系您！");
    	}else{
    		$this->error("申请失败，请联系公众号客服！");
    	}
    }
/*    public function test(){
        action_log('reg','member',1);
    }*/
	 public function advice(){
		 
		$this->display('advice');
	 
	 }
	 public function nianhui(){
		$this->display('nianhui');
	 }
}
<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Award\Controller;

use Think\Controller;
use Award\Model\ZhuanpanModel;

/**
 * 后台管理控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class IndexController extends Controller
{
	private  $key="o5@Dpje@03VeaFyZGgc996kzTn%@MSct";
	//抽奖
	public function GetAwardDo(){
		
		$data['unionid']=I("unionid");
		//根据unionid抽奖。
		//参数验证
		$data['source']=I('source');
		$sign=I('sign');
		$check=checkSign($data,$sign,$this->key);
		if(!$check){
			//$this->returnData(0,"参数验证失败",array(),4012);
		}
		$zhuanpan=D("Zhuanpan");
		$ret=$zhuanpan->zhuanpanDo($data['unionid'],$data['source']);
		if($ret!=0){
			return $this->returnData(1,"中奖",$ret,2001);
		}
		return $this->returnData(0,"没中奖",'',4011);
	}
	public function test(){
		$_POST['unionid']="aaaa";
		$_POST['source']="yjyx";
		//$_POST['awardid']=15;
		$_POST['sign']=getSignStr($_POST);
		//$r=request_post("http://".$_SERVER['SERVER_NAME']."/index.php?s=/Award/index/GetAwardDo.html",$_POST);
		$r=request_post("http://127.0.0.1/Game/proxy/index.php?s=/Award/index/GetAwardDo.html",$_POST);
		echo $r;exit;
	}
	//使奖品的使用状态可用
	public function SetAwardAble(){
		$data['unionid']=I("unionid");
		//参数验证
		$data['source']=I('source');
		$data['awardid']=I('awardid');
		$sign=I('sign');
		$check=checkSign($data,$sign,$this->key);
		if(!$check){
			$this->returnData(0,"参数验证失败",array(),4012);
		}
		$map['id']=$data['awardid'];
		$r=D()->table("accounts_award")->where($map)->save(array('status'=>0));
		if($r){
			$this->returnData(1,"处理成功",array(),2001);
		}else{
			$this->returnData(0,"处理失败",array(),4021); 	
		}
	}
}

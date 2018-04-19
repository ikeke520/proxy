<?php

namespace Award\Model;

use Think\Model;

class ZhuanpanModel extends Model{
	
	public function zhuanpanDo($unionid,$source){
		//抽奖
		if(!$unionid){
			return false;
		}
		$map['source']="yjyx";
		$map['repertory']=array("egt",1);
		$awardlist=D()->table("zhuanpan_award")->where($map)->select();
		
		if(!$awardlist){
			return false;
		}
		$arr=array();
		$x=0;
		$z=rand(0,10000);
		foreach($awardlist as $k =>$v){
			$map=array();
			//如果奖品一天出货阈值已经满了，及不再参与抽奖
			if($this->checkAward($v['id'])){
				continue;
			}
			//如果当前用户已经中该奖品，该奖品今天不再获得
			if($this->checkOwnAward($unionid,$v['id'])){
				break;
			}
			if($z>=$x&&$z<=$x+$v['rate']*100){
				$arr=$v;
				break;
			}
			$x=$x+$v['rate']*100;
		}
		
		//如果$arr 有值即中奖
		if($arr){
			//增加中奖者记录//并消减库存
			$r=$this->ZhongJiangLog($unionid,$arr['id'],-1);
			
			//返回参数
			$arr['awardid']=$r;
			$arr['unionid']=$unionid;
			return $arr;
		}
		//返回中奖商品跟中奖纪录id
		return 0;
	}
	//中奖记录
	private function ZhongJiangLog($unionid,$awardid,$status=-1){
		$map['id']=$awardid;
		$r=D()->table("zhuanpan_award")->where($map)->find();
		$data['unionid']=$unionid;
		$data['awardid']=$awardid;
		$data['start_time']=time();
		$data['goodsid']=$r['product_id'];
		$data['dead_time']=$r['dead_time'];
		$data['status']=$status;
		$r=D()->table("accounts_award")->add($data);
		if($r){
			$this->setRepertoryDec($awardid);
			return $r;
		}
		return false;
	}
	//减库存
	public function setRepertoryDec($id,$num=1){
		$map['id']=$id;
		$r=D()->table("zhuanpan_award")->where($map)->setDec("repertory",$num);
		if($r){
			return true;
		}
		return false;
	}
	//验证是否已经满阈值
	public function checkAward($id){
		$where['id']=$id;
		$award=D()->table("zhuanpan_award")->where($where)->find();
		$map['awardid']=id;
		$start_time=strtotime(date("Y-m-d 00:00:00",time()));
		$map['start_time']=array('BETWEEN',array($start_time,time()));
		$r=D()->table("accounts_award")->where($map)->count();
		if($r>=$award['threshold']){
			//阈值已满
			return true;
		}else{
			//还可以玩
			return false;
		}
	}
	//验证中奖次数是否满了 true 满了  false 没满
	public function checkOwnAward($unionid,$awardid){
		
		$map['id']=$awardid;
		$award=D()->table("zhuanpan_award")->where($where)->find();
		$people_day_award_num=$award['people_day_award_num'];
		//查看记录里面有没有
		$map=array();
		$map['awardid']=$awardid;
		$map['unionid']=$unionid;
		$start_time=strtotime(date("Y-m-d 00:00:00",time()));
		$map['start_time']=array('BETWEEN',array($start_time,time()));
		$r=D()->table("accounts_award")->where($map)->count();
		
		if($people_day_award_num==""){
			return false;
		}
		if($r>=$people_day_award_num){
			return true;
		}else{
			return false;
		}
	}
}
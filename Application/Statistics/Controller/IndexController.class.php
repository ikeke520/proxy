<?php
// +----------------------------------------------------------------------
// | 欧阳长空
// +----------------------------------------------------------------------
namespace Statistics\Controller;
use Think\Controller;

/**
 *   数据中心 控制器
 */
class IndexController extends Controller
{
	public function index(){
		echo "welcome";exit;
	}
  	//获取当前玩家在线数据
  	public function getCurrentInfo(){
  		//获取当前在线人数
  		$map['state']=1;
  		$info=D()->table("player_game_history")->field("count(*) num")->where($map)->find();
  		
  		//获取当前房卡总数  房卡消耗总数
  		$fc=D()->table("player")->field("sum(gold) gold_num,sum(gold_consume) gold_cost_num")->where()->find();
  		
  		//获取之前一次统计的数据
  		//$last_data=D()->table("player_info_statistics")->field()->where()->order("time desc")->find();
  		
  		//订单总额
  		
  		//
  		//把当前在线人数数据存储到记录表里面
  		$data['time']=time();
  		$data['online_num']=$info['num'];
  		$data['current_gold_num']=$fc['gold_num'];
  		$data['gold_cost']=$fc['gold_cost_num'];
  		
  		$r=D()->table("player_info_statistics")->add($data);
  		if($r){
  			echo "success";
  		}else{
  			echo "error";
  		}
  	}
}
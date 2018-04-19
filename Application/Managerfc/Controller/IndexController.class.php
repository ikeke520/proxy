<?php
namespace Managerfc\Controller;
use Think\Controller;

class IndexController extends Controller
{
    //统计数据  在线人数
	public function data_statistics(){
		//获取当前数据
		$map['statis']=1;
		$r=D()->table("player_game_history")->field("count(*) num")->where($map)->find();
		
		print_r($r);
		exit;
	}
   
}
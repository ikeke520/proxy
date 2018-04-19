<?php
// +----------------------------------------------------------------------
// | 欧阳长空
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Controller;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
/**
 *   数据中心 控制器
 */
class StatisticsController extends Controller
{
	//日活数据统计
	public function index(){
		if (UID) {
			if(IS_POST){
				$count_day=I('post.count_day', C('COUNT_DAY'),'intval',7);
				
				if(M('Config')->where(array('name'=>'COUNT_DAY'))->setField('value',$count_day)===false){
					$this->error(L('_ERROR_SETTING_').L('_PERIOD_'));
				}else{
					S('DB_CONFIG_DATA',null);
					$this->success(L('_SUCCESS_SETTING_').L('_PERIOD_'),'refresh');
				}
		
			}else{
				
				$this->meta_title = L('_Statistics_');
				
				$today = date('Y-m-d', time());
				$today = strtotime($today);
				$count_day = C('COUNT_DAY',null,7);
				$count['count_day']=$count_day;
				for ($i = $count_day; $i--; $i >= 0) {
					
					$day = $today - $i * 86400;
					$day_after = $today - ($i - 1) * 86400;
					$week_map=array('Mon'=>L('_MON_'),'Tue'=>L('_TUES_'),'Wed'=>L('_WEDNES_'),'Thu'=>L('_THURS_'),'Fri'=>L('_FRI_'),'Sat'=>'<strong>'.L('_SATUR_').'</strong>','Sun'=>'<strong>'.L('_SUN_').'</strong>');
					$week[] = date('m月d日 ', $day). $week_map[date('D',$day)];
					
					$day_date=date("Y-m-d H:i:s",$day);
					$day_after_date=date("Y-m-d H:i:s",$day_after);
					//$day_after_date=date("Y-m-d H:i:s",time());
					//活跃人数
					//$user = UCenterMember()->where('status=1 and reg_time >=' . $day . ' and reg_time < ' . $day_after)->count() * 1;
					$map='state=1 and time>="'.$day_date.'" and time < "' . $day_after_date.'"';
					
					$user=D()->table('player_game_history')->field("playerid,count(playerid)")->group("playerid")->where($map)->select();
					$user=count($user);
					$registeredMemeberCount[] = $user;
					if ($i == 0) {
						$count['rihuo_day'] = $user;
					}
				}
				
				$week = json_encode($week);
				$this->assign('week', $week);
				
				$start_time=date("Y-m-d H:i:s",time()-30*86400);
				$end_time=date("Y-m-d H:i:s",time());
				$map='state=1 and time>="'.$start_time.'" and time <"' . $end_time.'"';
				$info=D()->table('player_game_history')->field("playerid,count(playerid)")->group("playerid")->where($map)->select();
				$count['rihuo_month'] = count($info);
				
				$start_time=date("Y-m-d H:i:s",time()-7*86400);
				$map='state=1 and time>="'.$start_time.'" and time <"' . $end_time.'"';
				$info=D()->table('player_game_history')->field("playerid,count(playerid)")->group("playerid")->where($map)->select();
				$count['rihuo_week'] =count($info);
				$count['last_day']['days'] = $week;
				$count['last_day']['data'] = json_encode($registeredMemeberCount);
				//dump($count);exit;
				$this->assign('count', $count);
				$this->display();
			}
		} else {
			$this->redirect('Public/login');
		}
	}
	//登陆日志
	public function LoginLog($page=1,$r=20){
		$redis=D("Redis");
		$list=D()->table('player_game_history')->where($map)->page($page,$r)->select();
		$totalCount=D()->table("player_game_history")->where($map)->count();
		foreach($list as $k=>$v){
			$list[$k]['key']=$k+1;
			$list[$k]['player_name']=$redis->conn->hGet("player_id_to_nickName",$v['playerid']);
			$list[$k]['player_game']=$redis->getGameType($v['gametype']);
		}
		$builder=new AdminListBuilder();
		$builder->title("登录日志");
		$builder->keyText("key","序号");
		$builder->keyText("playerid","玩家ID");
		$builder->keyText("player_name","玩家昵称");
		$builder->keyText("player_game","游戏类型");
		$builder->keyStatusDiy("state","状态",array(1=>"上线",0=>"下线"));
		$builder->keyText("time","时间");
		$builder->pagination($totalCount,$r);
		$builder->data($list);
		$builder->display();
	}
	//单日  分时段
	public function hoursData(){
		//分时段获取
		$day=I('day');
		if(!$day){
			$day=date("Y-m-d 00:00:00",time());
		}
		$data=array();
		$start_time=$day;
		for($i=1;$i<=24;$i++){
			$end_time=date("Y-m-d H:i:s",strtotime($start_time)+3600);
			$map='state=1 and time>="'.$start_time.'" and time <"' . $end_time.'"';
			$info=D()->table('player_game_history')->field("playerid,count(playerid)")->group("playerid")->where($map)->select();
			$data[$i]['data']=count($info);
			$data[$i]['time']=$start_time."至".$end_time;
			$start_time=$end_time;
		}
		$builder=new AdminListBuilder();
		$builder->title("分时段活跃人数");
		$builder->search("日期","day","date");
		$builder->keyText("time","时段");
		$builder->keyText("data","活跃人数");
		$builder->data($data);
		$builder->display();
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
  	
  	/**
  	 * 在线玩家房间列表  add by 欧阳长空 2018/3/22
  	 */
  	public function roomLog($page=1,$r=15){
  		if(I('owner_user_id')){
  			$map['owner_user_id']=I("owner_user_id");
  		}
  		if(I('group_id')){
  			$map['group_id']=I("group_id");
  		}
  		
  		$end_time=I("end_time");
  		if($end_time){
  			$end_time=strtotime($end_time);
  		}else{
  			$end_time=time();
  		}
  		$start_time=I("start_time");
  		if($start_time){
  			$start_time=strtotime($start_time);
  			$map['time']=array("between",array($start_time,$end_time));
  		}
  		
  		$redis=D("Redis");
  		$redis->select(2);
  		$_list=D()->table("record a")->join("player b on a.owner_user_id=b.account_id","left","")->field("a.*,b.nickname")->where($map)->page($page,$r)->group("record_base")->order("time desc")->select();
  		
  		$totalCount=D()->table("record")->where($map)->field("count(distinct(record_base)) num")->find();
  		$totalCount=$totalCount['num'];
  		
  		foreach($_list  as $k=>$v){
  			$_list[$k]['key']=$k+1;
  			$json_roomData=json_decode(base64_decode($v['record_base']));
  			$_list[$k]['time']=$json_roomData->time;
  			$_list[$k]['room_id']=$json_roomData->roomid;
  			$_list[$k]['owner_name']=base64_decode($v['nickname']);
  		}
  		$builder=new AdminListBuilder();
  		$builder->search("房主ID","owner_user_id");
  		$builder->search("俱乐部ID","group_id");
  		$builder->search("开始时间","start_time","date");
  		$builder->search("结束时间","end_time","date");
  		$builder->title("玩家房间数据");
  		$builder->keyText("key","序号");
  		$builder->keyText("room_id","房间ID");
  		$builder->keyStatusDiy("game_type","游戏类型",array(1=>'新宁麻将',2=>'桃江麻将',3=>'长沙麻将',4=>'转转麻将',5=>'红中麻将',6=>'跑得快',7=>'掂坨'));
  		$builder->keyText("owner_user_id","房主ID");
  		$builder->keyText("owner_name","房主昵称");
  		$builder->keyStatusDiy("cost_type","开房渠道",array(2=>"俱乐部创建",0=>"自己创建"));
  		$builder->keyText("group_id","俱乐部ID");
  		$builder->keyTime("time","游戏时间");
  		$builder->keyText("cost_num","娱币消耗");
  		$builder->keyDoActionModalPopup("BigRoomDetail?id=###","大局详情");
  		$builder->keyDoActionModalPopup("LittleRoomDetail?id=###","小局详情");
  		$builder->data($_list);
  		$builder->pagination($totalCount,$r);
  		$builder->display();
  	}
  	
  	public function BigRoomDetail(){
  		$id=I('id');
  		if($id){
  			$map['id']=$id;
  		}else{
  			echo "没找到该局";exit;
  		}
  		$info=D()->table("record a")->field()->where($map)->find();
  		$json_roomData=json_decode(base64_decode($info['record_base']));
  		$roomData=objtoarr($json_roomData);
  		
  		$this->assign("roomData",$roomData);
  		$this->display();
  	}
  	public function LittleRoomDetail(){
  		$id=I('id');
  		if($id){
  			$map['id']=$id;
  		}else{
  			echo "没找到该局";exit;
  		}
  		$info=D()->table("record a")->field()->where($map)->find();
  		$json_roomData=json_decode(base64_decode($info['record_round']));
  		$roomData=objtoarr($json_roomData);
  		
  		$this->assign("roomData",$roomData);
  		$this->display();
  	}
}
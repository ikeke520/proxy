<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Daili\Model;
/**
 * 后台管理控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class DailiController extends AdminController
{
	public $daili_level_name=array(0=>"普通用户",1=>"银猫",2=>"金猫",3=>"招财猫");
	public $tixian_type_name=array(1=>"微信到银行卡",2=>"人工处理");
	
	/* 空操作，用于输出404页面 */
    public function _empty()
    {
        $this->redirect('Index/index');
    }
    //玩家列表
    public function playerList($page=1,$r=10){
  
    	//$uid=is_login();
    	$account_id=I('account_id');
    	$NickName=I('NickName');
    	$UserID=I('UserID');
    	if($UserID){
    		$map['b.UserID']=$UserID;
    	}
    	if($NickName){
    		$map['b.NickName']=$NickName;
    	}
    	if($account_id){
    		$map['a.account_id']=$account_id;
    	}
	
    	$field="a.account_id,a.username,a.gold,a.create_time,a.last_time,a.gold_consume,a.type,a.sex,b.UserID,b.NickName,b.upId,b.yb_coin,b.chengzi,b.agent";
    	$dailiuserlist=D()->table("player a")->join("__TP_ACCOUNTS_INFO__ b on a.username=b.Unionid","left","")->field($field)->where($map)->order("a.create_time desc")->page($page, $r)->select();
    	
    	//日支出
    	$dbconfig=array('DB_TYPE'   => 'mysql', // 数据库类型
    			'DB_HOST'   => 'rm-wz97q89c7nx24vq4lo.mysql.rds.aliyuncs.com', // 服务器地址
    			'DB_NAME'   => 'bkgame', // 数据库名
    			'DB_USER'   => 'tgame', // 用户名
    			'DB_PWD'    => 'Yujigame123',  // 密码
    			'DB_PORT'   => '3306', // 端口
    			'DB_PREFIX' => '' // 数据库表前缀)
    	);
    	$db_business=M("db_business","",$dbconfig);
    	
  		foreach($dailiuserlist as $k =>$v){
  		
  			$dailiuserlist[$k]['agent_name']=$this->daili_level_name[$v['agent']];
  			
  			//在线时长
  			$times=D()->table("player_game_history")->where("playerid='".$v['account_id']."' and state=1")->count();
  			$dailiuserlist[$k]['times']=$times;
  			
  			$mall=$db_business->table("mall_order")->where("user_id='".$v['UserID']."' and identification=1 and status=1")->field("sum(cost_money) amount")->find();
  			$dailiuserlist[$k]['id']=$v['account_id'];
  			//总充值额
  			$dailiuserlist[$k]['amount']=$mall['amount'];
  			
  		}
    	//总笔数
    	$totalCount = D()->table('player')->where($map)->count();
    	//$pager = new \Think\Page($totalCount, $r, $_REQUEST);
    	//$pager->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
    	//$paginationHtml = $pager->show();
    	
    	//$this->assign("_list",$dailiuserlist);
    	//$this->assign("_page",$paginationHtml);
		$builder=new AdminListBuilder();
		$builder->title("玩家列表");
		$builder->search("昵称","NickName");
		$builder->search("用户ID","account_id");
		$builder->search("邀请码","UserID");
	
		$builder->buttonNew(U('DataClub'),"玩家俱乐部数据");
		
		$builder->keyId();
		$builder->keyText("NickName","玩家昵称");
		$builder->keyStatusDiy("sex","玩家性别",array(1=>"男",2=>"女"));
		$builder->keyText("upId","上级邀请码");
		$builder->keyText("agent_name","用户类型");
		$builder->keyCreateTime("create_time","注册时间");
		$builder->keyTime("last_time","最后登录时间");
		$builder->keyText("times","登录次数");
		$builder->keyText("amount","总充值额");
		$builder->keyText("gold","娱币(游戏中)");
		$builder->keyText("chengzi","橙子");
		
		$builder->keyDoActionModalPopup("editPlayer?id=###","变更等级");
		$builder->keyDoActionModalPopup("clubEdit?id=###","开通俱乐部");
		$builder->keyDoActionModalPopup("pointGive?id=###","拨卡");
		//$builder->keyDoActionModalPopup("playerDelete?id=###","删除");
		$builder->pagination($totalCount,$r);
		$builder->data($dailiuserlist);
    	$builder->display();
    }
    //删除 add by 欧阳长空 2018.3.20
    public function editPlayer(){
    	$id=I("id");
    	if(!$id){
    		$this->error("未获取用户信息");
    	}
    	if(IS_POST){
    		$map['UserID']=$id;
    		$r=D()->table("tp_accounts_info")->where($map)->save(array('agent'=>I('agent')));
    		if($r){
    			$this->success("修改成功");
    		}else{
    			$this->error("修改失败");
    		}
    	}else{
    		$map['a.account_id']=$id;
    		$field="a.account_id,a.username,a.gold,a.create_time,a.last_time,a.gold_consume,a.type,a.sex,b.UserID,b.NickName,b.upId,b.yb_coin,b.chengzi,b.agent";
    		$dailiuserlist=D()->table("player a")->join("__TP_ACCOUNTS_INFO__ b on a.username=b.Unionid","left","")->field($field)->where($map)->find();
    		$arr=array();
    		foreach($this->daili_level_name as $k=>$v){
    			$z=array('value'=>$k,'name'=>$v);
    			$arr[]=$z;
    		}
    		$dailiuserlist['agent_name']=$this->daili_level_name[$dailiuserlist['agent']];
    		$this->assign("_daili_level_name",$arr);
    		$this->assign("_playerInfo",$dailiuserlist);
    		$this->display();
    	}
    	
    }
    //开通俱乐部  add by 欧阳长空 2018/3/20
    public function clubEdit(){
    	$id=I("id");
    	if(!$id){
    		$this->error("未获取用户信息");
    	}
    	if(IS_POST){
    		$map['account_id']=$id;
    		
    		$r=D()->table("player")->where($map)->save(array('type'=>1));
    		if($r){
    			$this->success("修改成功");
    		}else{
    			$this->error("修改失败");
    		}
    	}else{
    		$map['a.account_id']=$id;
    		$field="a.account_id,a.username,a.gold,a.create_time,a.last_time,a.gold_consume,a.type,a.sex,b.UserID,b.NickName,b.upId,b.yb_coin,b.chengzi,b.agent";
    		$dailiuserlist=D()->table("player a")->join("__TP_ACCOUNTS_INFO__ b on a.username=b.Unionid","left","")->field($field)->where($map)->find();
    		if($dailiuserlist['type']==1){
    			echo "<div>该用户已经有俱乐部权限了</div>";exit;
    		}
    		
    		$arr=array();
    		foreach($this->daili_level_name as $k=>$v){
    			$z=array('value'=>$k,'name'=>$v);
    			$arr[]=$z;
    		}
    		$dailiuserlist['agent_name']=$this->daili_level_name[$dailiuserlist['agent']];
    		$this->assign("_daili_level_name",$arr);
    		$this->assign("_playerInfo",$dailiuserlist);
    		$this->display();
    	}
    }
   	//add by 欧阳长空 2018/3/20 后台拨卡 
   	public function pointGive(){
   		$id=I("id");
   		if(!$id){
   			$this->error("未获取用户信息");
   		}
   		if(IS_POST){
   			$map['account_id']=$id;
   			$yb_coin_add=I("yb_coin_add");
   			if($yb_coin_add){
   				$r=D()->table("player")->where($map)->setInc("gold",$yb_coin_add);
   				if($r){
   					//日志记录
   					$param['userid']=$id;
   					$param['source']="0";//代表后台操作
   					$param['game']="1";
   					$param['addtime']=time();
   					$param['point']=$yb_coin_add;
   					$param['remark']="后台管理员给用户娱记游戏账号里面充值娱币";
   					$param['type']=1;
   					D()->table("tp_point_log")->add($param);
   				}
   			}
   			$chengzi_add=I("chengzi_add");
   			if($chengzi_add){
   				$map=array();
   				$map['UserID']=I('uid');
   				$r=D()->table("tp_accounts_info")->where($map)->setInc("chengzi",$chengzi_add);
   				if($r){
   					//日志记录
   					$param['userid']=I('uid');
   					$param['chengzi_num']=$chengzi_add;//代表后台操作
   					$param['create_time']=time();
   					$param['remark']="后台管理员给用户账号里面充值橙子";
   					D()->table("tp_chengzi_log")->add($param);
   				}
   			}
   			$Cash_add=I("Cash_add");
   			if($Cash_add){
   				$map=array();
   				$map['UserID']=I('uid');
   				$r=D()->table("tp_accounts_info")->where($map)->setInc("chengzi",$Cash_add);
   				if($r){
   					//日志记录
   					$param['user_id']=I('uid');
   					$param['money']=$Cash_add;//代表后台操作
   					$param['from_user_id']=0;
   					$param['date']=date("Y-m-d H:i:s",time());
   					$param['remark']="后台管理员给用户账号里面充值佣金";
   					D()->table("tp_fenxiao_log")->add($param);
   				}
   			}
   			if($r){
   				$this->success("修改成功");
   			}else{
   				$this->error("修改失败");
   			}
   		}else{
   			$map['a.account_id']=$id;
   			$field="a.account_id,a.username,a.gold,a.create_time,a.last_time,a.gold_consume,a.type,a.sex,b.UserID,b.NickName,b.upId,b.yb_coin,b.chengzi,b.agent";
   			$dailiuserlist=D()->table("player a")->join("__TP_ACCOUNTS_INFO__ b on a.username=b.Unionid","left","")->field($field)->where($map)->find();
   			
   			$arr=array();
   			foreach($this->daili_level_name as $k=>$v){
   				$z=array('value'=>$k,'name'=>$v);
   				$arr[]=$z;
   			}
   			$dailiuserlist['agent_name']=$this->daili_level_name[$dailiuserlist['agent']];
   			$this->assign("_daili_level_name",$arr);
   			$this->assign("_playerInfo",$dailiuserlist);
   			$this->display();
   		}
   	} 
   	//删除玩家 add by 欧阳长空 2018/3/20 
   	
    //add by 欧阳长空 2018/2/12  后台拨卡
    public function boka(){
    		$userid=I("UserID");
    		if(!$userid){
    			$this->error("未获得用户ID");
    		}
    		$map['UserID']=$userid;
    		$User=D("tp_accounts_info")->where($map)->find();
    		if(!$User){
    			$this->error("未找到该代理");
    		}
    	if(IS_POST){
    		$Cash_add=I("Cash_add");
    		$yb_coin_add=I("yb_coin_add");
    		$chengzi_add=I("chengzi_add");
    		$agent=I("agent");
    		
    		D("tp_accounts_info")->where($map)->save(array("agent"=>$agent));
    		D("tp_accounts_info")->where($map)->setInc("Cash",$Cash_add);
    		D("tp_accounts_info")->where($map)->setInc("yb_coin",$yb_coin_add);
    		D("tp_accounts_info")->where($map)->setInc("chengzi",$chengzi_add);
    		$this->success("修改成功",U("boka?UserID=".$userid));
    		
    	}else{
    		
    		$builder=new AdminConfigBuilder();
    		$builder->title("代理拨卡");
    		$builder->keyReadOnly("UserID","用户ID");
    		$builder->keyReadOnly("NickName","用户昵称");
    		$builder->keyReadOnly("Cash","用户佣金");
    		$builder->keyReadOnly("yb_coin","娱币");
    		$builder->keyReadOnly("chengzi","橙子");
    		
    		$builder->keySelect("agent","代理等级","",array(1=>L("_AGENT1_"),2=>L("_AGENT2_"),3=>L("_AGENT3_")));
    		$builder->keyText("Cash_add","佣金增加数","为负数就是扣除");
    		$builder->keyText("yb_coin_add","娱币增加数","为负数就是扣除");
    		$builder->keyText("chengzi_add","橙子增加数","为负数就是扣除");
    		$builder->data($User);
    		$builder->buttonSubmit("","保存");
    		$builder->display();
    	}
    }
    /**
     * 在线玩家房间列表  add by 欧阳长空 2018/3/22
     */
    public function roomData(){
    	
    	$redis=D("Redis");
    	$redis->select(2);
    	$r=$redis->conn->hGetAll('gourp_guid_2_player_owner_id');
		$roomlist=array();
		$i=1;
    	while (list($key, $val) = each($r))
 		 {
 		 	$a['key']=$i;
 		 	$a['room_id']=$key;
 		 	$a['own_id']=$val;
 		 	$nickname=$redis->conn->hGet('player_id_to_nickName', $val);
 			
 		 	$roomdata=$redis->conn->hGetAll("group_".$key."_info");
 		 		
 		 	$a['room_name']=$roomdata['name'];
 		 	$a['room_type']=$roomdata['type'];
 		 	$a['own_name']=$nickname;
  			$roomlist[]=$a;
  			$i++;
  		 }
    	$builder=new AdminListBuilder();
    	$builder->title("玩家房间数据");
    	$builder->keyText("key","序号");
    	$builder->keyText("room_id","房间ID");
    	$builder->keyText("room_name","房间名称");
    	$builder->keyStatusDiy("room_type","游戏类型",array(1=>'新宁麻将',2=>'桃江麻将',3=>'长沙麻将',4=>'转转麻将',5=>'红中麻将',6=>'跑得快',7=>'掂坨'));
    	$builder->keyText("own_id","房主ID");
    	$builder->keyText("own_name","房主昵称");
    	$builder->keyText("room_create_type","开房渠道");
    	$builder->keyText("room_create_id","渠道ID");
    	$builder->keyText("room_play_type","游戏玩法");
    	$builder->keyText("room_play_cost","娱币消耗");
    	$builder->keyDoActionModalPopup("BigRoomDetail","大局详情");
    	$builder->keyDoActionModalPopup("LittleRoomDetail","小局详情");
    	$builder->data($roomlist);
    	$builder->display();
    }
   
    /**
     * 玩家房卡流水  add by 欧阳长空 2018/3/22
     */
    public function playMoneyDetail($page=1,$r=15){
    	
    	$list=D()->table("player_consume a")->field("a.*,b.sex")->join("__PLAYER__ b on a.account_id=b.account_id","left","")->where($map)->order("a.create_time desc")->page($page,$r)->select();
    	$i=1;
    	$redis=D("Redis");
    	$redis->select(2);
    	foreach($list as $k=> $v){
    		$list[$k]['key']=$i;
    		$i++;
    		$nickname=$redis->conn->hGet('player_id_to_nickName', $v['account_id']);
    		$list[$k]['nickname']=$nickname;
    	}
    	
    	$totalCount=D()->table("player")->where($map)->count();
    	$builder=new AdminListBuilder();
    	$builder->title("玩家房卡流水");
    	$builder->keyText("key","序号");
    	$builder->keyTime("create_time","消耗时间");
    	$builder->keyText("consume","娱币消耗");
    	$builder->keyText("cost_type","娱币处理");
    	$builder->keyText("account_id","玩家ID");
    	$builder->keyText("nickname","玩家昵称");
    	$builder->keyStatusDiy("sex","性别",array(1=>"男",0=>"女"));
    	$builder->data($list);
    	$builder->pagination($totalCount,$r);
    	$builder->display();
    }
    /**
     * 玩家活动数据 add by 欧阳长空
     */
    public function playerActData(){
    	header("Content-type:text/html;charset=utf-8");
    	echo "该功能开发中，敬请期待";exit;
    }
    /**
     *  玩家充值 add by 欧阳长空
     */
    public function playerIncome($page=1,$r=15){
    	$dbconfig=C("MALL_DBCONFIG");
    	$db_business=M("db_business","",$dbconfig);
    	$map['indentification']=1;
    	$list=$db_business->table("mall_order")->where($map)->page($page,$r)->order("order_date desc")->select();
    	foreach($list as $k=>$v){
    		$list[$k]['key']=$k+1;
    	}
    	$totalCount=$db_business->table("mall_order")->where($map)->count();
    	$builder=new AdminListBuilder();
    	$builder->title("玩家充值");
    	$builder->keyText("key","序号");
    	$builder->keyText("user_id","用户ID");
    	$builder->keyText("username","用户名");
    	$builder->keyText("mobile","手机");
    	$builder->keyText("order_no","订单号");
    	$builder->keyText("cost_money","订单金额");
    	$builder->keyStatusDiy("status","支付状态",array(1=>"已支付",0=>"未支付"));
    	$builder->keyText("order_date","支付时间");
    	$builder->pagination($totalCount,$r);
    	$builder->data($list);
    	$builder->display();
    	   	
    }
    /**
     *  代理充值 add by 欧阳长空
     */
    public function proxyIncome($page=1,$r=15){
    	
    	$where['agent']=1;
    	$proxyList=D()->field("UserID")->table("tp_accounts_info")->where($where)->select();
    	$arr="";
    	foreach($proxyList as $k=>$v){
    		$arr.=$v['UserID'].",";
    	}
    	$arr=substr($arr,0,-1);
    	
    	$dbconfig=C("MALL_TEST");
    	$db_business=M("db_business","",$dbconfig);
    	$map['user_id']=array("IN",$arr);
    	$map['indentification']=1;
    	$list=$db_business->table("mall_order")->where($map)->page($page,$r)->order("order_date desc")->select();
    	foreach($list as $k=>$v){
    		$list[$k]['key']=$k+1;
    	}
    	$totalCount=$db_business->table("mall_order")->where($map)->count();
    	$builder=new AdminListBuilder();
    	$builder->title("玩家充值");
    	$builder->keyText("key","序号");
    	$builder->keyText("user_id","用户ID");
    	$builder->keyText("username","用户名");
    	$builder->keyText("mobile","手机");
    	$builder->keyText("order_no","订单号");
    	$builder->keyText("cost_money","订单金额");
    	$builder->keyStatusDiy("status","支付状态",array(1=>"已支付",0=>"未支付"));
    	$builder->keyText("order_date","支付时间");
    	$builder->pagination($totalCount,$r);
    	$builder->data($list);
    	$builder->display();
    		
    }
    /**
     *  代理充值 add by 欧阳长空
     */
	public function clubList($page=1,$r=15){
		$redis=D('Redis');
		$redis->select(2);
	
		$r=$redis->conn->hGetAll('gourp_guid_2_player_owner_id');
		
		$roomlist=array();
		$i=1;
    	while (list($key, $val) = each($r))
 		 {
 		 	$a['key']=$i;
 		 	$a['room_id']=$key;
 		 	$a['own_id']=$val;
 		 	$nickname=$redis->conn->hGet('player_id_to_nickName', $val);
 			
 		 	$roomdata=$redis->conn->hGetAll("group_".$key."_info");
 		 	$roomMember=$redis->conn->sMembers("group_".$key."_member_set");
 		 	$a['room_member']=implode(",",$roomMember);
 		 	$a['room_name']=$roomdata['name'];
 		 	$a['room_type']=$roomdata['type'];
 		 	$a['own_name']=$nickname;
  			$roomlist[]=$a;
  			$i++;
  		 }
  		 $builder=new AdminListBuilder();
  		 $builder->title("俱乐部列表");
  		 $builder->keyText("key","序号");
  		 $builder->keyText("room_id","俱乐部ID");
  		 $builder->keyText("room_name","俱乐部名称");
  		 $builder->keyText("own_id","会长ID");
  		 $builder->keyText("own_name","会长名称");
  		 $builder->keyText("cost_money","会长类型");
  		 $builder->keyText("order_date","俱乐部时间");
  		 $builder->keyText("room_member","俱乐部成员");
  		 $builder->keyText("order_date","俱乐部累计消耗");
  		 $builder->data($roomlist);
  		 $builder->display();
		
	}
	/**
	 * 俱乐部开房数据 add by 欧阳长空
	 */
	public function clubData($page=1,$r=15){
		$redis=D('Redis');
		
		$list=$redis->conn->hGetAll('fast_room_list');
		$roomlist=array();
		$i=1;
		while (list($key, $val) = each($list))
		{
			$a['key']=$i;
			$a['room_id']=$key;
			$redis->select(0);
			$roomdata=$redis->conn->hgetAll('room'.$key);
			
			$a['own_id']=$roomdata['owner_player'];
			$redis->select(2);
			$a['own_name']=$redis->conn->hGet("player_id_to_nickName",$a['own_id']);
			$a['max_player']=$roomdata['max_player'];
			$redis->select(1);
			$a['game_type']=$redis->conn->hGet("server_".$roomdata['gametype'],"name");
			if($roomdata['info']){
				$json=json_decode($roomdata['info']);
				$a['game_way']=$json->round."人";
			}
			$a['group_id']=$roomdata['group_id'];
			
			$roomlist[]=$a;
			$i++;
		}

		$builder=new AdminListBuilder();
		$builder->title("俱乐部列表");
		$builder->keyText("key","序号");
		$builder->keyText("room_id","房间ID");
		$builder->keyText("group_id","俱乐部ID");
		$builder->keyText("room_name","俱乐部名称");
		$builder->keyText("own_id","会长ID");
		$builder->keyText("own_name","会长名称");
		$builder->keyText("game_type","游戏类型");
		$builder->keyText("game_way","游戏玩法");
		$builder->keyText("order_date","开房时间");
		$builder->data($roomlist);
		$builder->display();
	}
	/**
	 * 玩家报名数据 add by 欧阳长空
	 */
	public function gameSigninList(){
		$this->error("当前功能还在开发");exit;
	}
	/**
	 * 赛事创建数据 add by 欧阳长空
	 */
	public function matchList(){
		$this->error("当前功能还在开发");exit;
	}
	/**
	 * 赛事创建数据 add by 欧阳长空
	 */
	public function matchData(){
		$this->error("当前功能还在开发");exit;
	}
	/**
	 * 赛事创建数据 add by 欧阳长空
	 */
	public function activityData(){
		$this->error("当前功能还在开发");exit;
	}
	
	/**
	 * 代理分佣日志  add by 欧阳长空 2018/3/25
	 */
	public function fenyongLog($page=1,$r=15){
		
		$_list=D()->table("tp_fenxiao_log")->page($page,$r)->order("date desc")->select();
		$totalCount=D()->table("tp_fenxiao_log")->count();
		foreach($_list as $k=>$v){
			$_list[$k]['key']=$k+1;
			$a=D()->table("tp_accounts_info")->where("UserID='".$v['user_id']."'")->find();
			
			$_list[$k]['user_name']=$a['NickName'];
			$_list[$k]['agent']=$a['agent'];
			$a=D()->table("tp_accounts_info")->where("UserID='".$v['from_user_id']."'")->find();
			$_list[$k]['from_user_name']=$a['NickName'];
			
		}
		$builder=new AdminListBuilder();
		$builder->title("代理分佣日志");
		$builder->keyText("key","序号");
		$builder->keyText("user_id","代理ID");
		$builder->keyText("user_name","代理昵称");
		$builder->keyStatusDiy("agent","代理等级",$this->daili_level_name);
		$builder->keyText("from_user_id","下级ID");
		$builder->keyText("from_user_name","下级昵称");
		$builder->keyText("money","分佣金额");
		
		$builder->keyText("remark","备注");
		$builder->keyText("date","分佣时间");
		$builder->data($_list);
		$builder->pagination($totalCount,$r);
		$builder->display();
	}
	/**
	 * 代理拨卡日志 add by 欧阳长空 2018/3/25
	 */
	public function proxyBokaLog($page=1,$r=15){
		$redis=D("Redis");
		$_list=D()->table("player_log")->page($page,$r)->order("create_time desc")->select();
		$totalCount=D()->table("player_log")->count();
		foreach($_list as $k=>$v){
			$_list[$k]['key']=$k+1;
			$redis->select(2);
			$_list[$k]['account_nickname']=$redis->conn->hGet("player_id_to_nickName",$v['account_id']);
			
			$a=D()->table("tp_accounts_info")->where("UserID='".$v['do_user']."' and agent>0")->find();
			$_list[$k]['do_user_name']=$a['NickName'];
			$_list[$k]['agent']=$a['agent'];
		}
		$builder=new AdminListBuilder();
		$builder->title("代理拨卡日志");
		$builder->keyText("key","序号");
		$builder->keyText("do_user","代理ID");
		$builder->keyText("do_user_name","代理昵称");
		$builder->keyStatusDiy("agent","代理等级",$this->daili_level_name);
		$builder->keyText("account_id","玩家");
		$builder->keyText("account_nickname","玩家昵称");
		$builder->keyText("gold_num","娱币数量");
		
		$builder->keyTime("create_time","拨卡时间");
		$builder->pagination($totalCount,$r);
		$builder->data($_list);
		$builder->display();
	}
	/**
	 * 代理等级变更日志  levelchangelog  add by 欧阳长空
	 */
	public function levelchangelog($page=1,$r=15){
		$this->error("该功能正在开发");
	}
    /**
     * 代理管理 add by 欧阳长空
     */
    public function proxyManager(){
    	
    }
    
     /**
     * 代理管理 edit by 欧阳长空 2018/3/23
     */
	public function manager($page=1,$r=15){
		//$uid=is_login();
		$UserID=I('UserID');
		if(!$UserID){
			$map1["agent"]=array("gt",0);
		}else{
			$map1['UserID']=$UserID;
		}
		$nickname=I("NickName");
		if($nickname){
			$map1['NickName']=$nickname;
		}
		
		$field="UserID,NickName,Cash,Addtime,Mobile,agent,upId,yb_coin";
		$dailiuserlist=D()->table("tp_accounts_info")->field($field)->where($map1)->order("Addtime desc,UserID desc")->page($page, $r)->select();
		
		$dbconfig=C("MALL_DBCONFIG");
		$db_business=M("db_business","",$dbconfig);
		$map_mall=array();
		$map_mall['identification']=1;
		$map_mall['status']=1;
		
		
		foreach($dailiuserlist as $k =>$v){
			$map=array();
			$dailiuserlist[$k]['key']=$k+1;
			$dailiuserlist[$k]['id']=$v['UserID'];
			$map['UserID']=$v['upId'];
			$p=D()->table('tp_accounts_info')->where($map)->find();
			$dailiuserlist[$k]['upidname']=$p['NickName'];
			$dailiuserlist[$k]['level_name']=$this->daili_level_name[$v['agent']];
			
			$where['upId']=$v['UserID'];
			$where['agent']=array("gt",0);
			$sonsProxyCount=D()->table("tp_accounts_info")->where($where)->count();
			//获取他下面的代理
			$dailiuserlist[$k]['sonsProxyCount']=$sonsProxyCount;
			//获取他下面的所有玩家
			$where="";
			$where['upId']=$v['UserID'];
			$where['agent']=0;
			$sonsProxyCount=D()->table("tp_accounts_info")->where($where)->count();
			//获取他下面的代理
			$dailiuserlist[$k]['sonsCount']=$sonsCount;
			//总充值
			$map_mall['user_id']=$v['UserID'];
			$total=$db_business->field("sum(cost_money) sum")->table("mall_order")->where($map)->find();
			$dailiuserlist[$k]['income']=$total['sum'];
		}
		
		//总笔数
		$totalCount = D()->table('tp_accounts_info')->where($map1)->count();
		
		$builder=new AdminListBuilder();
		$builder->title("代理管理");
		$builder->search("代理ID","UserID");
		$builder->search("昵称","NickName");
		$builder->search("上级ID","upId");
		$builder->keyText("key","序号");
		$builder->keyID("id","代理ID");
		$builder->keyText("NickName","昵称");
		$builder->keyText("upId","上级ID");
		$builder->keyText("level_name","代理等级");
		$builder->keyText("Addtime","时间");
		$builder->keyText("Cash","佣金");
		$builder->keyText("yb_coin","房卡");
		$builder->keyText("income","总充值");
		$builder->keyText("sonsProxyCount","旗下代理");
		$builder->keyText("sonsCount","旗下玩家");
		$builder->keyDoActionModalPopup("editPlayer?id=###","变更等级");
		$builder->data($dailiuserlist);
		$builder->pagination($totalCount,$r);
		$builder->display();
	}
	/**
	 * 实名注册功能  add by 欧阳长空 2017/3/23
	 */
	public function registerReal(){
		header("Content-type:text/html;charset=utf-8");
    	echo "该功能开发中，敬请期待";exit;
	}
	//代理俱乐部功能
	public function jlbedit(){
		$username=I('username');
		$status=I('status');
		$map['username']=$username;
		$user=D()->table('player')->where($map)->find();
		if(!$user){
			$this->error("该用户还没有注册游戏");
		}
		$r=D()->table('player')->where($map)->save(array('type'=>$status));
		if($r){
			$this->success("修改成功");
		}else{
			$this->error("修改失败");
		}
	}
	//代理申请提现列表
	public function tixian($page=1,$r=20){
		$tixianList=D()->table('tp_tixian_log')->where()->page($page,$r)->order("addtime desc")->select();
		$bank_list=C("BANK_CODE");
		foreach($tixianList as $k=>$v){
			 $map['UserID']=$v['userid'];
			 $uinfo=D()->table("tp_accounts_info")->where($map)->find();
			 $tixianList[$k]['nickname']=$uinfo['NickName'];
			 $tixianList[$k]['mobile']=$uinfo['Mobile'];
			$tixianList[$k]['addtime']=date("Y-m-d H:i:s",$v['addtime']);
			$tixianList[$k]['type']=$this->tixian_type_name[$v['type']];
			$tixianList[$k]['bank_name']=$bank_list[$v['bank_no']];
		}
		
		$totalCount = D('')->table('tp_tixian_log')->where()->count();
		
		$builder=new AdminListBuilder();
		$builder->title("提现记录");
		$builder->keyText("userid","用户ID")->keyText("nickname","用户昵称")->keyText("mobile","用户手机")->keyText("addtime","申请时间")->keyText("money","提现金额")->keyText("type","提现方式")
		->keyText("account_no","提现账户")->keyText("bank_name","银行名称")->keyText("user_name","拥卡名")->keyStatusDiy("status","状态",array(0=>'未处理',1=>'已处理',-1=>"退回"))->keyDoAction('tixianDo?id=###',"微信提现")
		->keyDoAction('tixianPdo?id=###',"人工处理")
		->keyDoAction('tixianReturn?id=###',"退回");
		$builder->pagination($totalCount,$r);
		$builder->data($tixianList);
		$builder->display();
	}
	//提现人工处理
	public function tixianPdo(){
		$id=I("id");
		if(!$id){
			$this->error("ID不存在");
			return false;
		}
		$map["a.id"]=$id;
		//获取客户信息
		$tixian_info=D()->table("tp_tixian_log a")->where($map)->find();
		$tixian_rate="1.5";
		
		if(!$tixian_info){
			$this->error("提现记录不存在");
			return false;
		}
		if($tixian_info['status']!=0){
			$this->error("提现已处理");
			return false;
		}
		
		//修改提现记录状态
		$param['status']=1;
		$param['amount']=$tixian_info['money']*(1-$tixian_rate*0.01);
		$param['type']=2;
		
		$map=array();
		$map['id']=$id;
		$r=D()->table("tp_tixian_log")->where($map)->save($param);
		if($r){
			//同时将冻结款项处理
			$map=array();
			$map['userid']=$tixian_info['userid'];
			$r=D()->table('tp_accounts_bank')->where($map)->setDec("freeze_cash",$tixian_info['money']);	
			$this->success("人工处理成功");
		}else{
			$this->error("人工处理失败");
		}
		//
	}
	//提现信息退回
	public function tixianReturn(){
		$id=I("id");
		if(!$id){
			$this->error("ID不存在");
			return false;
		}
		$map["a.id"]=$id;
		//获取客户信息
		$tixian_info=D()->table("tp_tixian_log a")->where($map)->find();
		if(!$tixian_info){
			$this->error("提现记录不存在");
			return false;
		}
		if($tixian_info['status']!=0){
			$this->error("提现已处理");
			return false;
		}
		//修改状态
		$param['status']=-1;
		$map=array();
		$map['id']=$id;
		$r=D()->table("tp_tixian_log")->where($map)->save($param);
		//退回冻结资金
		if($r){
			//同时将冻结款项处理
			$map=array();
			$map['userid']=$tixian_info['userid'];
			$where["UserID"]=$tixian_info['userid'];
			
			$r=D()->table('tp_accounts_bank')->where($map)->setDec("freeze_cash",$tixian_info['money']);
			D()->table("tp_accounts_info")->where($where)->setInc("Cash",$tixian_info['money']);
			
			$this->success("退回处理成功");
		}else{
			$this->error("退回处理失败");
		}
	}
	public function test_tixian(){
		$a=D("WxPayToBank");
		//$r=$a->getPublicKey();
		$r=$a->ToBank(10);
		print_r($r);exit;
	}
	public function tixianDo(){
		$id=I("id");
		$map['id']=$id;
		
		$a=D("WxPayToBank");
		$r=$a->ToBank($id);
		if($r){
			$this->success("处理成功");
		}else{
			$this->error("处理失败");
		}
	}
	//代理详细信息
	public function dailiInfo(){
		$uid=is_login();		
		$id=I('id');
		$map['UserID']=$id;
		$user=D()->table('tp_accounts_info')->where($map)->find();
		
		if(IS_POST){
			$data=I("post.");
			//-----修改密码
			if($data['UserID']){
				
				if($data['pwd']!=""){
					$pwd=D("Daili/daili")->encrypt($data['pwd']);
					$data['pwd']=$pwd;
				}else{
					unset($data['pwd']);
				}
				$map=array();
				$map['UserID']=$data['UserID'];
				$r=D()->table('tp_accounts_info')->where($map)->save($data);
			}else{
				
				if($data['pwd']!=""){
					$pwd=D("Daili/daili")->encrypt($data['pwd']);
					$data['pwd']=$pwd;
				}else{
					$this->error("请输入密码！");
				}
				
				if(!$data['Mobile']){
					$this->error("请输入电话");
				}else{
					$r=D()->table("tp_accounts_info")->where("Mobile='".$data['Mobile']."'")->find();
					if($r){
						$this->error("电话号码已存在！");
					}
				}
				$data['upId']="partner_".$uid;
				$data['Addtime']=date("Y-m-d H:i:s");
				//$data['agent']=1;
				
				$r=D()->table('tp_accounts_info')->add($data);
			}
			if($r){
				$this->success("修改成功",U("manager"));
			}
		}else{
			$builder=new AdminConfigBuilder();
			$builder->title("代理用户详情");
			$builder->keyId("UserID");
			$builder->keyImage('Headimgurl',"微信头像");
			$builder->keyText('NickName',"昵称");
			$builder->keyLabel('CashScore',"积分");
			$builder->keyLabel('Addtime',"注册时间");
			$builder->keyText('Mobile',"手机");
			$builder->keyText('pwd',"密码");
			$builder->keyText('upId',"上级代理");
			$builder->keySelect('agent',"代理等级","",$this->daili_level_name);
			$builder->keyText('yb_coin',"钻石（房卡）");
			$user['pwd']="";
			$builder->data($user);
			$builder->buttonSubmit(U('dailiInfo'),"保存");
			$builder->display();
		}
	}
	
    /* 用户登录检测 */
    protected function login()
    {
        /* 用户登录检测 */
        is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
    }

    protected function ensureApiSuccess($result)
    {
        if (!$result['success']) {
            $this->error($result['message'], $result['url']);
        }
    }
	
	public function advicesub1(){
		
		$data['userid']=I('userid');
		$data['phone']=I('phone');
		$data['advice']=I('advice');
		$data['weixin']=I('weixin');
		$data['session']=session_id();
		$data['createtime']=time();
		//print_r($data);exit;
		if(empty($data['advice'])){
			$this->error("未填写建议内容！");
			$this->index();exit;
		}
		$r=M('advice')->where('session="'.$data['session'].'"')->find();
		if($r){
			$this->error("请勿重复提交");
		}
		$r=M('advice')->add($data);
		if($r){
			$this->success("感谢您给我们提出的宝贵意见，我们会认真对待！");
		}
	}
	
	//常胜麻将代理
	public function csadvicesub(){
		$data['userid']=I('userid');
		$data['phone']=I('phone');
		$data['advice']=I('advice');
		$data['weixin']=I('weixin');
		$data['session']=session_id();
		$data['createtime']=time();
		//print_r($data);exit;
		if(empty($data['advice'])){
			$this->error("未填写建议内容！");
			$this->index();exit;
		}
		
		$r=M('csadvice')->where('session="'.$data['session'].'"')->find();
		if($r){
			$this->error("请勿重复提交");exit;
		}
		$r=M('csadvice')->add($data);
		if($r){
			$this->success("感谢您给我们提出的宝贵意见，我们会认真对待！");exit;
		}
	}
	//商城那边代理充值
	public function OrderList($page=1,$r=20){
		
		$dbconfig=C("MALL_DBCONFIG");
		$db_business=M("db_business","",$dbconfig);
		$map['identification']=1;
		$list=$db_business->table("mall_order")->order("order_date desc")->where($map)->page($page,$r)->select();
		$totalCount = $db_business->table('mall_order')->where($map)->count();
		
		$builder=new AdminListBuilder();
		$builder->title("代理充值订单");
		$builder->keyText("order_no","订单号");
		$builder->keyText("mobile","手机号");
		$builder->keyText("username","称呼");
		$builder->keyText("product_name","产品名");
		$builder->keyText("cost_money","金额");
		$builder->keyText("product_nums","数量");
		$builder->keyText("order_date","下单时间");
		$builder->keyStatusDiy("status","状态",array(0=>"未付款",1=>"已付款"));
		$builder->keyText("cost_accu_points","消耗橙子");
		$builder->pagination($totalCount,$r);
		$builder->Data($list);
		
		$builder->display();
	}
}

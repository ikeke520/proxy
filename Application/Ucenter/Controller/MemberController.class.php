<?php
/**
 * 放置用户登陆注册
 */
namespace Ucenter\Controller;


use Common\Model\FollowModel;
use Think\Controller;
use User\Api\UserApi;
use Ucenter\Model\wxModel;
use Common\Model;
use Think\Db;


require_once APP_PATH . 'User/Conf/config.php';

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class MemberController extends Controller
{
	public $fw=1;
	
	public function _initialize()
	{
		//parent::_initialize();
		//$uid = session('login_userid')? op_t(session('login_userid')) : D('Accounts')->is_login();
		//调用API获取基本信息
		//$this->userInfo($uid);	
	}
	private function userInfo($uid = null)
	{
		//获取用户封面id
		$user_info=D('Accounts')->userinfo($uid);
		$user_info['sex']=$user_info['Sex']==1?"男":"女";
		$a="_AGENT".$user_info['agent']."_";
		$user_info['agent_name']=L($a);
		if($user_info['chengzi']<0){
			$user_info['chengzi']=0;
		}
		if($user_info['yb_coin']<0){
			$user_info['yb_coin']=0;
		}
		if($user_info['Cash']<0){
			$user_info['Cash']=0;
		}
		$this->assign('user_info', $user_info);
		return $user_info;
	}

    public function register()
    {
    	session('login_userid', null);
        //获取参数
        $aUsername = $username = I('post.username', '', 'op_t');
        $aNickname = I('post.nickname', '', 'op_t');
        $aPassword = I('post.password', '', 'op_t');
        $aVerify = I('post.verify', '', 'op_t');
        $aRegVerify = I('post.reg_verify', '', 'op_t');
        //$aRegType = I('post.reg_type', '', 'op_t');
        $aRegType="mobile";
        $aStep = I('get.step', 'start', 'op_t');
        $aRole = I('post.role', 0, 'intval');
		//玩家ID
		$account_id=I('post.account_id');
		$user_id=I('user_id');
		
        if (!modC('REG_SWITCH', '', 'USERCONFIG')) {
            $this->error(L('_ERROR_REGISTER_CLOSED_'));
        }


        if (IS_POST) {
        	if(strlen($aPassword)<6){
        		$this->error("密码不小于6位数!");
        	}
        	$r=D("Accounts")->Register($username,$aPassword,$aNickname);
        	if($r){
        		redirect(U("Ucenter/index/Center"));
        	}else{
        		$this->error("注册失败");
        	}
        	exit;
            //注册用户
            $return = check_action_limit('reg', 'ucenter_member', 1, 1, true);
            if ($return && !$return['state']) {
                $this->error($return['info'], $return['url']);
            }
            /* 检测验证码 */
            /* if (check_verify_open('reg')) {
                if (!check_verify($aVerify)) {
                    $this->error(L('_ERROR_VERIFY_CODE_').L('_PERIOD_'));
                }
            } */
            if (!$aRole) {
                $this->error(L('_ERROR_ROLE_SELECT_').L('_PERIOD_'));
            }

            if (($aRegType == 'mobile' && modC('MOBILE_VERIFY_TYPE', 0, 'USERCONFIG') == 1) || (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 2 && $aRegType == 'email')) {
                if (!D('Verify')->checkVerify($aUsername, $aRegType, $aRegVerify, 0)) {
                    $str = $aRegType == 'mobile' ? L('_PHONE_') : L('_EMAIL_');
                    $this->error($str . L('_FAIL_VERIFY_'));
                }
            }
            $aUnType = 0;
            //获取注册类型
            check_username($aUsername, $email, $mobile, $aUnType);
            if ($aRegType == 'email' && $aUnType != 2) {
                $this->error(L('_ERROR_EMAIL_FORMAT_'));
            }
            if ($aRegType == 'mobile' && $aUnType != 3) {
                //$this->error(L('_ERROR_PHONE_FORMAT_'));
            }
            if ($aRegType == 'username' && $aUnType != 1) {
                $this->error(L('_ERROR_USERNAME_FORMAT_'));
            }
            if (!check_reg_type($aUnType)) {
                $this->error(L('_ERROR_REGISTER_NOT_OPENED_').L('_PERIOD_'));
            }

            $aCode = I('post.code', '', 'op_t');
            if (!$this->checkInviteCode($aCode)) {
                $this->error(L('_ERROR_INV_ILLEGAL_').L('_EXCLAMATION_'));
            }

            /* 注册用户 */
            $ucenterMemberModel=UCenterMember();
            $uid =$ucenterMemberModel ->register($aUsername, $aNickname, $aPassword, $email, $mobile, $aUnType);
			
			//增加玩家ID信息
			$data['account_id']=$account_id;
			D('member')->where('uid="'.$uid.'"')->save($data);
			
            if (0 < $uid) { //注册成功
                $this->initInviteUser($uid, $aCode, $aRole);
                $ucenterMemberModel->initRoleUser($aRole, $uid); //初始化角色用户
                if (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 1 && $aUnType == 2) {
                    set_user_status($uid, 3);
                    $verify = D('Verify')->addVerify($email, 'email', $uid);
                    $res = $this->sendActivateEmail($email, $verify, $uid); //发送激活邮件
                    // $this->success('注册成功，请登录邮箱进行激活');
                }

                $uid = $ucenterMemberModel->login($username, $aPassword, $aUnType); //通过账号密码取到uid
                D('Member')->login($uid, false, $aRole); //登陆

                //$this->success('', U('Ucenter/member/step', array('step' => get_next_step('start'))));
				$this->success('', U('Ucenter/index/information'));
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
            //显示注册表单
            if (D("Accounts")->is_login()) {
                redirect(U('Home/Index/index'));
            }
            $this->checkRegisterType();
            $aType = I('get.type', '', 'op_t');
            $regSwitch = modC('REG_SWITCH', '', 'USERCONFIG');
            $regSwitch = explode(',', $regSwitch);
            $regSwitch=array(0=>"mobile");
            $this->assign('regSwitch', $regSwitch);
            $this->assign('step', $aStep);
            $this->assign('type', $aType == '' ? 'username' : $aType);
            
            if($user_id){
            	$this->assign('user_id', $user_id);
            }
            
            $this->display();
        }
    }
    public function test(){
    	$param['auth']="1";
    	$param['sign']=getSignstr($param);
    	$url="http://127.0.0.1/Game/proxy/index.php?s=Ucenter/member/GameLogin/auth/1/sign/".$param['sign'];
		header("location:".$url);
    }
    /*游戏登录*/
    public function GameLogin(){
    	$param['auth']=I("auth");
    	//$sign=I("sign");
    	//$check=checkSign($param,$sign,"o5@Dpje@03VeaFyZGgc996kzTn%@MSct","MD5");
    	//if($check){
    		if($param['auth']){
    			//判断是否存在
    			$z=D()->table("tp_accounts_info")->where("Unionid='".$param['auth']."'")->find();
    			if($z){
    				$result=D('Accounts')->login($z['UserID']);
    				//$this->redirect("Ucenter/member/information");
    			}else{
    				$this->error("用户信息不存在");
    			}
    		//}
    	}else{
    		$this->error("用户信息错误");
    	}
    }
    /*微信登陆*/
    public function wxLogin(){
    	$param['auth']=I("auth");
    	$sign=I("sign");
    	$check=checkSign($param,$sign,"o5@Dpje@03VeaFyZGgc996kzTn%@MSct","MD5");
    	if($check){
    		if($param['auth']){
    			//判断是否存在
    			$z=M()->table("tp_accounts_info")->where("UserID=".$param['auth'])->find();
    			if($z){
    				$result=D('Accounts')->login($param['auth']);
    			}else{
    				$this->error("用户信息不存在");
    			}
    		}
    	}else{
    		$this->error("微信用户信息错误");
    	}
    }
    /* 登录页面 */
    public function login()
    {
    	session('login_userid', null);
    	//判断是否有传userid
  		//$user_id=I("userid");
    	//判断是否从微信登陆
    			//if(!$user_id){
    				
    				//跳转到 数据中心  获取用户数据
    				//$url=urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    				//$action="login";
    				//header("location:http://wx.91yuji.com/index/Gameservice/LoginReturn/action/".$action);
    				
    			//}
    			//判断是否有绑定微信用户
  			/* 	if($user_id){
					$map['user_id']=$user_id;
  					$member=D()->table("member")->where($map)->find();
  				} */
    		//有就微信登陆
    		/* if(int($member)>0){
    			D('Member')->login($member['uid'],true);
    		} */
    	
        $this->setTitle(L('_MEMBER_TITLE_LOGIN_'));
       	
        if (IS_POST) {
        	$username=I('username');$password=I('password');
        	$userid=D('Accounts')->checkLogin($username,$password);
        	
        	if($userid>=1){
        		//判断当前userid跟缓存是不是一样
        		if($userid!=session('login_userid')){
        			session('login_userid', null);
        		}
        		$result=D('Accounts')->login($userid);
        	}else{
        		$this->error("账号密码不正确！");exit;
        	}
        	
            if ($result['status']) {
            	$this->success(L('_SUCCESS_LOGIN_'), $result['url']);
            } else {
                $this->error($result['info']);
            }
        } else { //显示登录页面
        	
        	session('login_userid', null);
            $this->display();
        }
    }
    
    /* 快捷登录登录页面 */
    public function quickLogin()
    {
        if (IS_POST) {
            $result = A('Ucenter/Login', 'Widget')->doLogin();
            $this->ajaxReturn($result);
        } else { //显示登录弹出框
            $this->display();
        }
    }

    /* 退出登录 */
    public function logout()
    {
        if (D("Accounts")->is_login()) {
            D("Accounts")->logout();
        	//D('Member')->logout();
            $this->success(L('_SUCCESS_LOGOUT_').L('_EXCLAMATION_'), U('Home/index/index'));
        } else {
            $this->redirect('Home/index/index');
        }
    }
	
    /* 验证码，用于登录和注册 */
    public function verify($id = 1)
    {
        verify($id);
        //  $verify = new \Think\Verify();
        //  $verify->entry(1);
    }
    /* 商城免登 */
    public function mdLogin(){
    	
    	$userid=I('userid');
    	$sign=I('sign');
    	//验签
    	
    	//$check=D('Accounts')->checkSign($userid,$sign,"joker");
    	$check=md5($userid."joker")==$sign;
    	
    	if($check){
    		$r=D()->table("tp_accounts_info")->field("agent")->where("UserID='".$userid."'")->find();
    		if($r['agent']<=0){
    			//D("Accounts")->returnData(0,"You don't have the access to the place.");
    			redirect(U('Ucenter/member/login'));
    		}
    		$z=D("Accounts")->login($userid);
    		//redirect(U('Ucenter/index/center'));
    	}else{
    		D("Accounts")->returnData(0,"验证失败");
    	}
    }
    /* app免登 */
    public function appLogin(){
    	
    	$unionid=I('unionid');
    	if($unionid==""){
    		header("Content-type: text/html; charset=utf-8");
    		echo "<h1>参数错误<h1/>";exit;
    	}
    	//验签
    	$user=D()->table('tp_accounts_info')->where("Unionid='".$unionid."' and agent >0 ")->find();
    		
    	if($user){
    		D("Accounts")->login($user['UserID']);
    	}else{
    		header("Content-type: text/html; charset=utf-8");
    		//D("Accounts")->returnData(0,"You have no");
    		echo "<h1>您不是代理，没有权限访问<h1/>";
    	}
    }
	/* 下级代理列表 */
    public function DailiNextLevel($page=1,$r=20){
    	$uid=is_login();
    	if($uid){
    		$map['upId']=$uid;
    	}else{
    		redirect(U('ucenter/member/login'));
    	}
    	$map['agent']=array("egt","1");
    	$field="NickName,UserID,Headimgurl,agent,upId,yb_coin,chengzi,Cash,city_id,Addtime,Mobile";
    	$list=D()->table("tp_accounts_info")->field($field)->where($map)->order("Addtime desc")->page($page,$r)->select();
    	
    	$allCash=D()->table("tp_accounts_info")->field("sum(Cash) c")->where($map)->find();
    	$allCash=$allCash['c'];
    	
    	foreach ($list as $k=>$v){
    		$a="_AGENT".$v['agent']."_";
    		$list[$k]['agent_name']=L($a);
    		$z=D()->table("player")->field("type")->where("username=".$v['Unionid'])->find();
    		if($z['type']){
    			$list[$k]['type']="开通";
    		}
    	}
    	$totalCount=D()->table("tp_accounts_info")->where($map)->count();
    	
    	$pager = new \Think\Page($totalCount, $r, $_REQUEST);
        $pager->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $paginationHtml = $pager->show();
    	 //print_r($list);exit;
    	$this->assign("_list",$list);
    	$this->assign("totalCount",$totalCount);
    	$this->assign("allCash",$allCash);
    	
    	
    	$this->assign("_page",$paginationHtml);
    	$this->display();
    }
   
    /* 用户收益 管理中心（佣金收入详情）*/
    public function Income($page=1,$r=50){
    	
    	$uid=is_login();
    	
    	if(!$uid){
    		redirect(U("Ucenter/member/login"));
    	}
    	$this->display();exit;
    	//时间
    	
    	$map1['user_id']=$uid;
    	$map1['date']=array("egt",date("Y-m-d 00:00:00",time()));
    	$map1['date']=array("elt",date("Y-m-d H:i:s",time()));
    	//日统计
    	$day_fy=D()->table('tp_fenxiao_log')->where($map1)->field("sum(money) sum")->find();
    	
    	$map2['userid']=$uid;
    	$map2['addtime']=array("egt",strtotime(date("Y-m-d 00:00:00",time())));
    	$map2['addtime']=array("elt",time());
    	$day_yb=D()->table('tp_point_log')->where($map2)->field("sum(point) sum")->find();
    	
    	 	//日支出
     	 	$dbconfig=C("MALL_DB_CONFIG");
     	 	$db_business=M("db_business","",$dbconfig);
    	 
     	$map1['user_id']=$uid;
     	$map1['status']=1;
     	$map1['order_date']=array("egt",date("Y-m-d 00:00:00",time()));
     	$map1['order_date']=array("elt",date("Y-m-d 23:59:59",time()));
     	$day_zc=$db_business->table("mall_order")->where($map1)->field("sum(cost_money) sum")->find();
		
     	
    	//月统计
    	$map1=array();
    	$map1['user_id']=$uid;
    	$map1['date']=array("egt",date("Y-m-01 00:00:00",time()));
    	$map1['date']=array("elt",date("Y-m-d H:i:s",time()));
    	$month_fy=D()->table('tp_fenxiao_log')->where($map1)->field("sum(money) sum")->find(); 
    	$map2=array();
    	$map2['userid']=$uid;
    	$map2['addtime']=array("egt",strtotime(date("Y-m-0 00:00:00",time())));
    	$map2['addtime']=array("elt",time());
    	$month_yb=D()->table('tp_point_log')->where($map2)->field("sum(point) sum")->find();
    	
    	$map1=array();
    	$map1['user_id']=$uid;
    	$map1['status']=1;
    	$map1['order_date']=array("egt",date("Y-m-01 00:00:00",time()));
    	$map1['order_date']=array("elt",date("Y-m-d H:i:s",time()));
    	$month_zc=$db_business->table("mall_order")->where($map1)->field("sum(cost_money) sum")->find();
    	
    	//总统计
    	$map1=array();
    	$map1['user_id']=$uid;
    	$total_fy=D()->table('tp_fenxiao_log')->where($map1)->field("sum(money) sum")->find();
    	$map2=array();
    	$map1['status']=1;
    	$map2['userid']=$uid;
    	$total_yb=D()->table('tp_point_log')->where($map2)->field("sum(point) sum")->find();
    	$total_zc=$db_business->table("mall_order")->where($map1)->field("sum(cost_money) sum")->find();
    	
    	$this->assign("day_fy",$day_fy['sum']);
    	$this->assign("day_yb",$day_yb['sum']);
    	$this->assign("month_fy",$month_fy['sum']);
    	$this->assign("month_yb",$month_yb['sum']);
    	$this->assign("total_fy",$total_fy['sum']);
    	$this->assign("total_yb",$total_yb['sum']);
    	
    	$this->assign("day_zc",$day_zc['sum']);
    	$this->assign("month_zc",$month_zc['sum']);
    	$this->assign("total_zc",$total_zc['sum']);
    	
		$this->display();
    }
    //娱币充值
    public function ybLogList($page=1,$r=15){
    	$start_time=I("start_time");
    	$end_time=I("end_time");
    	$id=is_login();
    	
    	$dbconfig=C("MALL_DB_CONFIG");
    	$db_business=M("db_business","",$dbconfig);
    	//获取下级代理
    	$map=array();
    	$map['upId']=$id;
    	$map['agent']=array("egt",1);
    	$proxyList=D()->table("tp_accounts_info")->where($map)->select();
    	$str="";
    	foreach($proxyList as $k=>$v){
    		
    		//拼接代理字符串
    		$str.=$v['UserID'].",";
    	}
    	$str=substr($str,0,-1);
    	$where=array();
    	$where['user_id']=array("IN",$str);
    	if($start_time&&$end_time){
    		$where['order_date']=array("BETWEEN",array($start_time,$end_time));
    	}
    	if($start_time&&!$end_time){
    		$where['order_date']=array("egt",$start_time);
    	}
    	if(!$start_time&&$end_time){
    		$where['order_date']=array("elt",$end_time);
    	}
    	$where['identification']=1;
    	$where['status']=1;
    	
    	$order=$db_business->table("mall_order")->where($where)->page($page,$r)->select();
    	foreach($order as $k=>$v){
    		//
    		$r=D()->table("tp_accounts_info")->where("UserID='".$v['user_id']."'")->find();
    		$a="_AGENT".$r['agent']."_";
    		$order[$k]['agent_name']=L($a);
    	}
    	$this->assign("start_time",$start_time);
    	$this->assign("end_time",$end_time);
    	
    	$this->assign("orderList",$order);
    	$this->display();
    }
    //积分兑换
    public function jifenduihuan($page=1,$r=10){
    	
    	$start_time=I("start_time");
    	$end_time=I("end_time");
    	
    	$uid=is_login();
    	$dbconfig=C("MALL_DB_CONFIG");
    	$db_business=M("db_business","",$dbconfig);
    	
    	$map['identification']=2;
    	$map['status']=1;
    	$map['user_id']=$uid;
    	
    	if($start_time&&$end_time){
    		$map['order_date']=array("BETWEEN",array($start_time,$end_time));
    	}
    	if($start_time&&!$end_time){
    		$map['order_date']=array("egt",$start_time);
    	}
    	if(!$start_time&&$end_time){
    		$map['order_date']=array("elt",$end_time);
    	}
    	$list=$db_business->table("mall_order")->field("*,cost_accu_points*product_nums as total")->where($map)->page($page,$r)->select();
    	if(!$list){
    		$this->assign("msg","没有相关数据");$this->display();exit;
    	}
    	$this->assign("begin_date",$start_time);
    	$this->assign("end_date",$end_time);
    	//print_r($list);exit;
  		$this->assign("list",$list);
    	$this->display();
    }
    //佣金提现
    public function yongjintixian(){
    	$this->display();
    }
    //兑换积分
    public function duihuanjifen(){
    	if(IS_POST){
    		$score=I("Score");
    		if(!$score){
    			echo "未提交参数";
    		}
    		$uid=is_login();
    		//判断佣金是否足够
    		$user=D()->table("tp_accounts_info")->where("UserID='".$uid."'")->find();
    		if($user['Cash']*10<$score){
    			echo "佣金不足";exit;
    		}
    		$m=M();
    		$m->startTrans();
    		$r1=$m->table("tp_accounts_info")->where("UserID='".$uid."'")->setInc("chengzi",$score);
    		$r2=$m->table("tp_accounts_info")->where("UserID='".$uid."'")->setDec("Cash",$score/10);
    		if($r1&&$r2){
    			//记录
    			$param['userid']=$uid;
    			$param['time']=time();
    			$param['cash']=$score/10;
    			$param['score']=$score;
    			$user=D()->table("tp_accounts_info")->where("UserID='".$uid."'")->find();
    			$param['lastscore']=$user['chengzi'];
    			$m->table("tp_score_exchange_log")->add($param);
    			
    			$m->commit();
    			echo "兑换成功";
    		}else{
    			$m->rollback();echo "兑换失败";
    		}
    	}else{
    	$this->display();
    	}
    }
    //兑换娱币
    public function duihuanyubi(){
    	if(IS_POST){
    		$score=I("cash");
    		if(!$score){
    			echo "未提交参数";
    		}
    		$uid=is_login();
    		$user=D()->table("tp_accounts_info")->where("UserID='".$uid."'")->find();
    		if($user['Cash']*2<$score){
    			echo "佣金不足";exit;
    		}
    		$uid=is_login();
    		$m=M();
    		$m->startTrans();
    		$r1=$m->table("tp_accounts_info")->where("UserID='".$uid."'")->setDec("Cash",$score/2);
    		$r2=$m->table("tp_accounts_info")->where("UserID='".$uid."'")->setInc("yb_coin",$score);
    		if($r1&&$r2){
    			//记录
    			$param['userid']=$uid;
    			$param['time']=time();
    			$param['cash']=$score/2;
    			$param['yb_coin']=$score;
    			$user=D()->table("tp_accounts_info")->where("UserID='".$uid."'")->find();
    			$param['last_coin']=$user['yb_coin'];
    			$m->table("tp_cash_exchange_log")->add($param);
    			
    			$m->commit();
    			echo "兑换成功";
    		}else{
    			$m->rollback();echo "兑换失败";
    		}
    	}else{
    	$this->display();
    	}
    }
    //积分兑换记录
    public function scoreExchangeLog(){
    	$uid=is_login();
    	$list=D()->table("tp_score_exchange_log")->where("userid='".$uid."'")->order('time desc')->select();
    	
    	$this->assign("_list",$list);
    	$this->display();
    }
    //佣金兑换记录
    public function cashExchangeLog(){
    	$uid=is_login();
    	$start_time=I("begin_date");
    	$end_time=I("end_date");
    	//echo $start_time;exit;
    	$start_time=strtotime($start_time);
    	$end_time=strtotime($end_time);
    	$map['userid']=$uid;
   		 if($start_time&&$end_time){
    		$map['time']=array("BETWEEN",array($start_time,$end_time));
    	}
    	if($start_time&&!$end_time){
    		$map['time']=array("egt",$start_time);
    	}
    	if(!$start_time&&$end_time){
    		$map['time']=array("elt",$end_time);
    	}
    	$list=D()->table("tp_cash_exchange_log")->where($map)->order('time desc')->select();
    	if(IS_POST){
    		$html="";
    		foreach($list as $k=>$v){
    			$html.="<div class='agent_content'><div><p>".$v['userid']."</p></div><span>".$v['last_coin']."</span><span>".$v["yb_coin"]."</span><span>".date("Y-m-d",$v['time'])."</span></div>";
    		}
    		echo $html;exit;
    	}
    	$this->assign("_list",$list);
    	$this->display();
    }
    //获取用户记录
    public function getListLog(){
    	$fw=I('fw');
    	$nw=I('nw');    
    	if(!$fw){
    		$fw=1;
    	}
    	if(!$nw){
    		$nw=3;
    	}
    	$uid=is_login();
    	if($nw==1){
    		//支出类
    		$dbconfig=C("MALL_DB_CONFIG");
    		$db_business=M("db_business","",$dbconfig);
    		$map=array();
    		$map['status']=1;
    		$map['user_id']=$uid;
    		$map['order_date']=array("elt",date("Y-m-d H:i:s",time()));
    		if($fw==1){
    			//当天
    			$map['order_date']=array("egt",date("Y-m-d 00:00:00",time()));
    		}else if($fw==2){
    			$map['order_date']=array("egt",date("Y-m-01 00:00:00",time()));
    		}
    		$zhichu=$db_business->table("mall_order")->where($map)->order("order_date desc")->page(1,50)->select();
    		//print_r($zhichu);exit;
    		$html="";
    		$html.='';
    		foreach($zhichu as $k=>$v){
    			$html.='<div class="sq_sy_content"><div class="div_box" ><div class="all"><div>'.$v['order_date'].'</div>
		<div><p></p>商品名称:<span>'.$v['product_name'].'</span></div>
    	<div><p></p>商品金额:￥<span>'.$v['cost_money'].'</span></div>	
		</div></div></div>';
    		}
    		$html.="";
    		D("Accounts")->returnData(1,"",$html);exit;
    	}
    	if($nw==2){
    		//娱币
    		$map=array();
    		$map['userid']=$uid;
    		$map['order_date']=array("elt",time());
    		
    		if($fw==1){
    			//当天
    			$map['addtime']=array("egt",strtotime(date("Y-m-d 00:00:00",time())));
    		}else if($fw==2){
    			$map['addtime']=array("egt",strtotime(date("Y-m-01 00:00:00",time())));
    		}
    		$yb=D()->table("tp_point_log")->where($map)->page(1,50)->order("addtime desc")->select();
    		
    		$html="";
    		$html.='';
    		foreach($yb as $k=>$v){
    			$html.='<div class="sq_sy_content"><div class="div_box" ><div class="all"><div>'.date("Y-m-d H:i:s",$v['addtime']).'</div>
		<div><p></p><span>'.$v['remark'].'</span></div>
    	<div><p></p>赠送娱币:￥<span>'.$v['point'].'</span></div>
		</div></div></div>';
    		}
    		$html.="";
    		D("Accounts")->returnData(1,"",$html);exit;
    	}
    	if($nw==3){
    		$map=array();
    		
    		$map['user_id']=$uid;
    		$map['date']=array("elt",date("Y-m-d H:i:s",time()));
    		
    		if($fw==1){
    			//当天
    			$map['date']=array("egt",date("Y-m-d 00:00:00",time()));
    		}else if($fw==2){
    			$map['date']=array("egt",date("Y-m-01 00:00:00",time()));
    		}
    		$fx=D()->table("tp_fenxiao_log")->where($map)->order("date desc")->page(1,50)->select();
   
    		$html="";
    		$html.='';
    		foreach($fx as $k=>$v){
    			$html.='<div class="sq_sy_content"><div class="div_box" ><div class="all"><div>'.$v['date'].'</div>
		<div><p></p></div>
    	<div><p></p>分佣金额:￥<span>'.$v['money'].'</span></div>
		</div></div></div>';
    		}
    		$html.="";
    		D("Accounts")->returnData(1,"",$html);exit;
    	}
    	
    }
    /** 用户中心 **/
    public function dailiCenter(){
    	$this->display();
    }
    
    //佣金
    public function yongjin(){
    	$uid=is_login();
    	$map['UserID']=$uid;
    	$user=D()->table("tp_accounts_info")->where($map)->find();
    	$this->assign("user",$user);
    	$this->display();
    }
    //提现申请
    public function tixian(){
    	
    	if(IS_POST){
    	 //保存微信号
    	 //$weixin_no=I("weixin_no");
    	 
    	 //if($weixin_no){
    	$map=array();
    	$map['userid']=is_login();
    	//D()->table("tp_accounts_bank")->where($map)->save(array("weixin_no",$weixin_no));
    	 //
    	 $user_bank=D()->table("tp_accounts_bank")->where($map)->find();
    	 
    	 //$data['type']=I('type');
    	// if($data['type']==1){
    	 	$data['account_no']=$user_bank['bank_no'];
    	 //}else{
    	 	//$data['account_no']=$user_bank['weixin_no'];
    	// }
    	 if(!$data['account_no']){
    	 	D("Accounts")->returnData(0,"请填入你的卡号/微信号");
    	 }
    	 $data['status']=0;
    	 $data['addtime']=time();
    	 $data['userid']=is_login();
    	 $data['money']=I("money");
    	 $data['remark']="提现";
    	 $data['bank_no']=$user_bank['bank_code'];
    	 $data['user_name']=$user_bank['user_name'];
    	 
    	 $map=array();
    	 $map['UserID']=$data['userid'];
    	 $user=D()->table("tp_accounts_info")->field("Cash")->where($map)->find();
    	 
    	 if($data['money']<100){
    	 	D("Accounts")->returnData(0,"提现金额必须满100元");
    	 }
    	 if($data['money']>$user['Cash']){
    	 	D("Accounts")->returnData(0,"您的佣金不足");
    	 }
    	 //判断是否银行卡号是否有填
    	 //$map['userid']=$data['userid'];
    	 //$user_bank=D()->table("tp_accounts_bank")->where($map)->find();
    	
    	 if(!$user_bank['bank_code']||!$user_bank['bank_no']||!$user_bank['user_name']){
    	 	D("Accounts")->returnData(0,"您的银行卡信息不正确，请完善信息后提交");
    	 }
    	 
    	 $r=D()->table("tp_tixian_log")->add($data);
    	 
    	 //冻结金额
    	 D()->table("tp_accounts_info")->where($map)->setDec("Cash",$data['money']);
    	 $map=array();
    	 $map['userid']=$data['userid'];
    	 D()->table("tp_accounts_bank")->where($map)->setInc("freeze",$data['money']);
    	 
    	 if($r){
    	 	D("Accounts")->returnData(1,"申请提现成功");
    	 }else{
    	 	D("Accounts")->returnData(0,"申请提现失败");
    	 }
    	}else{
    	
    	//获取当前代理的额外信息
    	$userid=is_login();
    	$map['userid']=$userid;
    	$where['UserID']=$userid;
    	$info=D()->table("tp_accounts_info")->where($where)->field("Cash")->find();
    	$ext_info=D()->table("tp_accounts_bank")->where($map)->find();
    	$bank_code_list=C('BANK_CODE');
    	if(!$bank_code_list){
				$bank_code_list=array(
    		'1002'=>"工商银行",
    		'1005'=>"农业银行",
    		'1026'=>"中国银行",
    		'1003'=>"建设银行",
    		'1001'=>"招商银行",
    		'1066'=>"邮储银行",
    		'1020'=>"交通银行",
    		'1004'=>"浦发银行",
    		'1006'=>"民生银行",
    		'1009'=>"兴业银行",
    		'1010'=>"平安银行",
    		'1021'=>"中信银行",
    		'1025'=>"华夏银行",
    		'1027'=>"广发银行",
    		'1022'=>"光大银行",
    		'1032'=>"北京银行",
    		'1056'=>"宁波银行",
			);
			}
    	$ext_info['bank_name']=$bank_code_list[$ext_info['bank_code']];
    	
    	$this->assign('info',$info);
    	$this->assign('ext_info',$ext_info);
    	$this->display();
    	}
    }
   //提现记录
   public function tixianLog(){
   		$uid=is_login();
   		$map['userid']=$uid;
   		$loglist=D()->table("tp_tixian_log")->where($map)->order("addtime desc")->select();
   		$this->assign("list",$loglist);
  		$this->display();
   }
    
   //修改银行
   public function bankedit(){
   		$uid=is_login();
   		$map['userid']=$uid;
   		$info=D()->table("tp_accounts_bank")->where($map)->find();
   		if(IS_POST){
   			$data['user_name']=I('user_name');
   			$data['bank_code']=I('bank_code');
   			$data['bank_no']=I('bank_no');
   			
   			if($info){
   				//修改
   				D()->table("tp_accounts_bank")->where($map)->save($data);
   				redirect(U("Ucenter/member/tixian"));
   			}else{
   				$data['userid']=$uid;
   				$data['addtime']=time();
   				D()->table("tp_accounts_bank")->where($map)->add($data);
   				redirect(U("Ucenter/member/tixian"));
   			}
   		}else{
   			
   			//获取银行信息
   			$bank_code_list=C("BANK_CODE");
			if(!$bank_code_list){
				$bank_code_list=array(
    		'1002'=>"工商银行",
    		'1005'=>"农业银行",
    		'1026'=>"中国银行",
    		'1003'=>"建设银行",
    		'1001'=>"招商银行",
    		'1066'=>"邮储银行",
    		'1020'=>"交通银行",
    		'1004'=>"浦发银行",
    		'1006'=>"民生银行",
    		'1009'=>"兴业银行",
    		'1010'=>"平安银行",
    		'1021'=>"中信银行",
    		'1025'=>"华夏银行",
    		'1027'=>"广发银行",
    		'1022'=>"光大银行",
    		'1032'=>"北京银行",
    		'1056'=>"宁波银行",
			);
			}
			
   			$arr=array();
   			$i=0;
   			foreach($bank_code_list as $k=>$v){
   				$arr[$i]['code']=$k;
   				$arr[$i]['name']=$v;
   				$i++;
   			}
   			$this->assign("bank_code_list",$arr);
   			$this->assign("_info",$info);
   			$this->display();
   		}
   }
    /** -------  基础功能  ---------  **/
    
    /* 用户密码找回首页 */
    public function mi( $email = '', $verify = '')
    {

        $email = strval($email);

        if (IS_POST) { //登录验证
            //检测验证码

            if (!check_verify($verify)) {
                $this->error(L('_ERROR_VERIFY_CODE_'));
            }

            //根据用户名获取用户UID
            $user = UCenterMember()->where(array( 'email' => $email, 'status' => 1))->find();
            $uid = $user['id'];
            if (!$uid) {
                $this->error(L('_ERROR_USERNAME_EMAIL_'));
            }

            //生成找回密码的验证码
            $verify = $this->getResetPasswordVerifyCode($uid);

            //发送验证邮箱
            $url = 'http://' . $_SERVER['HTTP_HOST'] . U('Ucenter/member/reset?uid=' . $uid . '&verify=' . $verify);
            $content = C('USER_RESPASS') . "<br/>" . $url . "<br/>" . modC('WEB_SITE_NAME', L('_OPENSNS_'), 'Config') . L('_SEND_MAIL_AUTO_')."<br/>" . date('Y-m-d H:i:s', TIME()) . "</p>";
            send_mail($email, modC('WEB_SITE_NAME', L('_OPENSNS_'), 'Config') . L('_SEND_MAIL_PASSWORD_FOUND_'), $content);
            $this->success(L('_SUCCESS_SEND_MAIL_'), U('Member/login'));
        } else {
            if (is_login()) {
                redirect(U('Home/Index/index'));
            }
            if(!check_reg_type('email')){
                redirect(U('Ucenter/Member/miMobile'));
            }

            $this->display();
        }
    }

    public function miMobile( $email = '', $verify = '')
    {
        if(!check_reg_type('mobile')){
         $this->error('请开启手机注册');
        }
        $email = strval($email);

        if (IS_POST) { //登录验证
            //检测验证码
            $aMobile=$_POST['mobile'];
            $aMobVerify=$_POST['verify'];

            $isVerify=D('Common/Verify')->checkVerify($aMobile,$type='mobile',$aMobVerify,0);


            if($isVerify){
                $user=UCenterMember()->where(array('mobile'=>$aMobile,'status'=>1))->find();
                if (empty($user)) {
                    $this->ajaxReturn(array('status'=>0,'info'=>'该用户不存在！'));
                }
                /*重置密码操作*/
                $ucModel = UCenterMember();
                $res = $ucModel->where(array('id'=>$user['id'],'status'=>1))->save(array('password' =>think_ucenter_md5('123456', UC_AUTH_KEY)));
                if ($res) {
                    $this->success('密码重置成功！新密码是“123456”');
                } else {
                    $this->error('密码重置失败！可能密码重置前就是“123456”。');
                }
            }else{
                $this->error('验证码或手机号码错误！');
            }
        } else {
            if (is_login()) {
                redirect(U('Home/Index/index'));
            }

            $this->display();
        }
    }


    /**
     * 重置密码
     */
    public function reset($uid, $verify)
    {
        //检查参数
        $uid = intval($uid);
        $verify = strval($verify);
        if (!$uid || !$verify) {
            $this->error(L('_ERROR_PARAM_'));
        }

        //确认邮箱验证码正确
        $expectVerify = $this->getResetPasswordVerifyCode($uid);
        if ($expectVerify != $verify) {
            $this->error(L('_ERROR_PARAM_'));
        }

        //将邮箱验证码储存在SESSION
        session('reset_password_uid', $uid);
        session('reset_password_verify', $verify);

        //显示新密码页面
        $this->display();
    }

    public function doReset($password, $repassword)
    {
        //确认两次输入的密码正确
        if ($password != $repassword) {
            $this->error(L('_PW_NOT_SAME_'));
        }

        //读取SESSION中的验证信息
        $uid = session('reset_password_uid');
        $verify = session('reset_password_verify');

        //确认验证信息正确
        $expectVerify = $this->getResetPasswordVerifyCode($uid);
        if ($expectVerify != $verify) {
            $this->error(L('_ERROR_VERIFY_INFO_INVALID_'));
        }

        //将新的密码写入数据库
        $data = array('id' => $uid, 'password' => $password);
        $model = UCenterMember();
        $data = $model->create($data);
        if (!$data) {
            $this->error(L('_ERROR_PASSWORD_FORMAT_'));
        }
        $result = $model->where(array('id' => $uid))->save($data);
        if ($result === false) {
            $this->error(L('_ERROR_DB_WRITE_'));
        }

        //显示成功消息
        $this->success(L('_ERROR_PASSWORD_RESET_'), U('Ucenter/Member/login'));
    }

    private function getResetPasswordVerifyCode($uid)
    {
        $user = UCenterMember()->where(array('id' => $uid))->find();
        $clear = implode('|', array($user['uid'], $user['username'], $user['last_login_time'], $user['password']));
        $verify = thinkox_hash($clear, UC_AUTH_KEY);
        return $verify;
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    public function showRegError($code = 0)
    {
        switch ($code) {
            case -1:
                $error = L('').modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('USERNAME_MAX_LENGTH',32,'USERCONFIG').L('_ERROR_LENGTH_2_').L('_EXCLAMATION_');
                break;
            case -2:
                $error = L('_ERROR_USERNAME_FORBIDDEN_').L('_EXCLAMATION_');
                break;
            case -3:
                $error = L('_ERROR_USERNAME_USED_').L('_EXCLAMATION_');
                break;
            case -4:
                $error = L('_ERROR_LENGTH_PASSWORD_').L('_EXCLAMATION_');
                break;
            case -5:
                $error = L('_ERROR_EMAIL_FORMAT_2_').L('_EXCLAMATION_');
                break;
            case -6:
                $error = L('_ERROR_EMAIL_LENGTH_').L('_EXCLAMATION_');
                break;
            case -7:
                $error = L('_ERROR_EMAIL_FORBIDDEN_').L('_EXCLAMATION_');
                break;
            case -8:
                $error = L('_ERROR_EMAIL_USED_2_').L('_EXCLAMATION_');
                break;
            case -9:
                $error = L('_ERROR_PHONE_FORMAT_2_').L('_EXCLAMATION_');
                break;
            case -10:
                $error = L('_ERROR_FORBIDDEN_').L('_EXCLAMATION_');
                break;
            case -11:
                $error = L('_ERROR_PHONE_USED_').L('_EXCLAMATION_');
                break;
            case -20:
                $error = L('_ERROR_USERNAME_FORM_').L('_EXCLAMATION_');
                break;
            case -30:
                $error = L('_ERROR_NICKNAME_USED_').L('_EXCLAMATION_');
                break;
            case -31:
                $error = L('_ERROR_NICKNAME_FORBIDDEN_2_').L('_EXCLAMATION_');
                break;
            case -32:
                $error =L('_ERROR_NICKNAME_FORM_').L('_EXCLAMATION_');
                break;
            case -33:
                $error = L('_ERROR_LENGTH_NICKNAME_1_').modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG').L('_ERROR_LENGTH_2_').L('_EXCLAMATION_');;
                break;
            default:
                $error = L('_ERROR_UNKNOWN_');
        }
        return $error;
    }


    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function profile()
    {
        if (!is_login()) {
            $this->error(L('_ERROR_NOT_LOGIN_'), U('User/login'));
        }
        if (IS_POST) {
            //获取参数
            $uid = is_login();
            $password = I('post.old');
            $repassword = I('post.repassword');
            $data['password'] = I('post.password');
            empty($password) && $this->error(L('_ERROR_INPUT_ORIGIN_PASSWORD_'));
            empty($data['password']) && $this->error(L('_ERROR_INPUT_NEW_PASSWORD_'));
            empty($repassword) && $this->error(L('_ERROR_CONFIRM_PASSWORD_'));

            if ($data['password'] !== $repassword) {
                $this->error(L('_ERROR_NOT_SAME_PASSWORD_'));
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $password, $data);
            if ($res['status']) {
                $this->success(L('_SUCCESS_CHANGE_PASSWORD_').L('_EXCLAMAITON_'));
            } else {
                $this->error($res['info']);
            }
        } else {
            $this->display();
        }
    }

    /**
     * doSendVerify  发送验证码
     * @param $account
     * @param $verify
     * @param $type
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doSendVerify($account, $verify, $type)
    {
        switch ($type) {
            case 'mobile':
                $content = modC('SMS_CONTENT', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = sendSMS($account, $content);
                return $res;
                break;
            case 'email':
                //发送验证邮箱
                $content = modC('REG_EMAIL_VERIFY', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = send_mail($account, modC('WEB_SITE_NAME', L('_OPENSNS_'), 'Config') . L('_EMAIL_VERIFY_2_'), $content);
                return $res;
                break;
        }

    }

    /**
     * activate  提示激活页面
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function activate()
    {

        // $aUid = I('get.uid',0,'intval');
        $aUid = session('temp_login_uid');
        $status = UCenterMember()->where(array('id' => $aUid))->getField('status');
        if ($status != 3) {
            redirect(U('ucenter/member/login'));
        }
        $info = query_user(array('uid', 'nickname', 'email'), $aUid);
        $this->assign($info);
        $this->display();
    }

    /**
     * reSend  重发邮件
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function reSend()
    {
        $res = $this->activateVerify();
        if ($res === true) {
            $this->success(L('_SUCCESS_SEND_'), 'refresh');
        } else {
            $this->error(L('_ERROR_SEND_') . $res, 'refresh');
        }

    }

    /**
     * changeEmail  更改邮箱
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function changeEmail()
    {
        $aEmail = I('post.email', '', 'op_t');
        $aUid = session('temp_login_uid');
        $ucenterMemberModel = UCenterMember();
        //$ucenterMemberModel->where(array('id' => $aUid))->getField('status');
        if ($ucenterMemberModel->where(array('id' => $aUid))->getField('status') != 3) {
            $this->error(L('_ERROR_AUTHORITY_LACK_').L('_EXCLAMATION_'));
        }
        $ucenterMemberModel->where(array('id' => $aUid))->setField('email', $aEmail);
        clean_query_user_cache($aUid, 'email');
        $res = $this->activateVerify();
        $this->success(L('_SUCCESS_CHANGE_'), 'refresh');
    }

    /**
     * activateVerify 添加激活验证
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    private function activateVerify()
    {
        $aUid = session('temp_login_uid');
        $email = UCenterMember()->where(array('id' => $aUid))->getField('email');
        $verify = D('Verify')->addVerify($email, 'email', $aUid);
        $res = $this->sendActivateEmail($email, $verify, $aUid); //发送激活邮件
        return $res;
    }

    /**
     * sendActivateEmail   发送激活邮件
     * @param $account
     * @param $verify
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    private function sendActivateEmail($account, $verify, $uid)
    {

        $url = 'http://' . $_SERVER['HTTP_HOST'] . U('ucenter/member/doActivate?account=' . $account . '&verify=' . $verify . '&type=email&uid=' . $uid);
        $content = modC('REG_EMAIL_ACTIVATE', '{$url}', 'USERCONFIG');
        $content = str_replace('{$url}', $url, $content);
        $content = str_replace('{$title}', modC('WEB_SITE_NAME', L('_OPENSNS_'), 'Config'), $content);
        $res = send_mail($account, modC('WEB_SITE_NAME', L('_OPENSNS_'), 'Config') . L('_VERIFY_LETTER_'), $content);


        return $res;
    }

    /**
     * saveAvatar  保存头像
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function saveAvatar()
    {

        $redirect_url = session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('change_avatar'))) : 'refresh';
        $aCrop = I('post.crop', '', 'op_t');
        $aUid = session('temp_login_uid') ? session('temp_login_uid') : is_login();
        $aPath = I('post.path', '', 'op_t');

        if (empty($aCrop)) {
            $this->success(L('_SUCCESS_SAVE_').L('_EXCLAMATION_'),$redirect_url );
        }

        $returnPath = A('Ucenter/UploadAvatar', 'Widget')->cropPicture($aCrop,$aPath);
        $driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
        $data = array('uid' => $aUid, 'status' => 1, 'is_temp' => 0, 'path' => $returnPath,'driver'=> $driver, 'create_time' => time());
        $res = M('avatar')->where(array('uid' => $aUid))->save($data);
        if (!$res) {
            M('avatar')->add($data);
        }
        clean_query_user_cache($aUid, 'avatars');
        $this->success(L('_SUCCESS_AVATAR_CHANGE_').L('_EXCLAMATION_'), $redirect_url);

    }

    /**
     * doActivate  激活步骤
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doActivate()
    {

        $aAccount = I('get.account', '', 'op_t');
        $aVerify = I('get.verify', '', 'op_t');
        $aType = I('get.type', '', 'op_t');
        $aUid = I('get.uid', 0, 'intval');
        $check = D('Common/Verify')->checkVerify($aAccount, $aType, $aVerify, $aUid);
        if ($check) {
            set_user_status($aUid, 1);
            $this->success(L('_SUCCESS_ACTIVE_'), U('Ucenter/member/step', array('step' => get_next_step('start'))));
        } else {
            $this->error(L('_FAIL_ACTIVE_').L('_EXCLAMATION_'));
        }

    }



    /**
     * checkAccount  ajax验证用户帐号是否符合要求
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkAccount()
    {
        $aAccount = I('post.account', '', 'op_t');
        $aType = I('post.type', '', 'op_t');
        if (empty($aAccount)) {
            $this->error(L('_EMPTY_CANNOT_').L('_EXCLAMATION_'));
        }
        check_username($aAccount, $email, $mobile, $aUnType);
        $mUcenter = UCenterMember();
        switch ($aType) {
            case 'username':
                empty($aAccount) && $this->error(L('_ERROR_USERNAME_FORMAT_').L('_EXCLAMATION_'));
                $length = mb_strlen($aAccount, 'utf-8'); // 当前数据长度
                if ($length < modC('USERNAME_MIN_LENGTH',2,'USERCONFIG') || $length > modC('USERNAME_MAX_LENGTH',32,'USERCONFIG')) {
                    $this->error(L('_ERROR_USERNAME_LENGTH_1_').modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('USERNAME_MAX_LENGTH',32,'USERCONFIG').L('_ERROR_USERNAME_LENGTH_2_'));
                }


                $id = $mUcenter->where(array('username' => $aAccount))->getField('id');
                if ($id) {
                    $this->error(L('_ERROR_USERNAME_EXIST_2_'));
                }
                preg_match("/^[a-zA-Z0-9_]{".modC('USERNAME_MIN_LENGTH',2,'USERCONFIG').",".modC('USERNAME_MAX_LENGTH',32,'USERCONFIG')."}$/", $aAccount, $result);
                if (!$result) {
                    $this->error(L('_ERROR_USERNAME_ONLY_PERMISSION_'));
                }
                break;
            case 'email':
                empty($email) && $this->error(L('_ERROR_EMAIL_FORMAT_').L('_EXCLAMATION_'));
                $length = mb_strlen($email, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 32) {
                    $this->error(L('_ERROR_EMAIL_EXIST_'));
                }

                $id = $mUcenter->where(array('email' => $email))->getField('id');
                if ($id) {
//                    $this->error(L('_ERROR_EMAIL_LENGTH_LIMIT_'));
                    $this->error(L('_ERROR_EMAIL_EXIST_'));
                }
                break;
            case 'mobile':
                empty($mobile) && $this->error(L('_ERROR_PHONE_FORMAT_'));
                $id = $mUcenter->where(array('mobile' => $mobile))->getField('id');
                if ($id) {
                    $this->error(L('_ERROR_PHONE_EXIST_'));
                }
                break;
        }
        $this->success(L('_SUCCESS_VERIFY_'));
    }

    /**
     * checkNickname  ajax验证昵称是否符合要求
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkNickname()
    {
        $aNickname = I('post.nickname', '', 'op_t');

        if (empty($aNickname)) {
            $this->error(L('_EMPTY_CANNOT_').L('_EXCLAMATION_'));
        }

        $length = mb_strlen($aNickname, 'utf-8'); // 当前数据长度
        if ($length < modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG') || $length > modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG')) {
            $this->error(L('_ERROR_NICKNAME_LENGTH_11_').modC('NICKNAME_MIN_LENGTH',2,'USERCONFIG').'-'.modC('NICKNAME_MAX_LENGTH',32,'USERCONFIG').L('_ERROR_USERNAME_LENGTH_2_'));
        }

        $memberModel = D('member');
        $uid = $memberModel->where(array('nickname' => $aNickname))->getField('uid');
        if ($uid) {
            $this->error(L('_ERROR_NICKNAME_EXIST_'));
        }
        preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $aNickname, $result);
        if (!$result) {
            $this->error(L('_ERROR_NICKNAME_ONLY_PERMISSION_'));
        }

        $this->success(L('_SUCCESS_VERIFY_'));
    }

    /**
     * 切换登录身份
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function changeLoginRole()
    {
        $aRoleId = I('post.role_id', 0, 'intval');
        $uid = is_login();
        $data['status'] = 0;
        if ($uid && $aRoleId != get_login_role()) {
            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
            if ($roleUser) {
                $memberModel = D('Common/Member');
                $memberModel->logout();
                clean_query_user_cache($uid, array('avatars', 'rank_link'));
                $result = $memberModel->login($uid, false, $aRoleId);
                if ($result) {
                    $data['info'] = L('_INFO_ROLE_CHANGE_');
                    $data['status'] = 1;
                }
            }
        }
        $data['info'] = L('_ERROR_ILLEGAL_OPERATE_');
        $this->ajaxReturn($data);
    }

    /**
     * 持有新身份
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function registerRole()
    {
        $aRoleId = I('post.role_id', 0, 'intval');
        $uid = is_login();
        $data['status'] = 0;
        if ($uid > 0 && $aRoleId != get_login_role()) {
            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
            if ($roleUser) {
                $data['info'] = L('_INFO_INV_ROLE_POSSESS_');
                $this->ajaxReturn($data);
            } else {
                $memberModel = D('Common/Member');
                $memberModel->logout();
                UCenterMember()->initRoleUser($aRoleId, $uid);
                clean_query_user_cache($uid, array('avatars', 'rank_link'));
                $memberModel->login($uid, false, $aRoleId); //登陆
            }
        } else {
            $data['info'] = L('_ERROR_ILLEGAL_OPERATE_');
            $this->ajaxReturn($data);
        }
    }


    /**修改用户扩展信息
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function edit_expandinfo()
    {
        $result = A('Ucenter/RegStep', 'Widget')->edit_expandinfo();
        if ($result['status']) {
            $this->success(L('_SUCCESS_SAVE_'), session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('expand_info'))) : 'refresh');
        } else {
            !isset($result['info']) && $result['info'] = L('_ERROR_INFO_SAVE_NONE_');
            $this->error($result['info']);
        }
    }

    /**
     * 设置用户标签
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function set_tag()
    {
        $result = A('Ucenter/RegStep', 'Widget')->do_set_tag();
        if ($result['status']) {
            $result['url'] = U('Ucenter/member/step', array('step' => get_next_step('set_tag')));
        } else {
            !isset($result['info']) && $result['info'] = L('_ERROR_INFO_SAVE_NONE_');
        }
        $this->ajaxReturn($result);
    }

    /**
     * 判断注册类型
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function checkRegisterType()
    {
        $aCode = I('get.code', '', 'op_t');
        $register_type = modC('REGISTER_TYPE', 'normal', 'Invite');
        $register_type = explode(',', $register_type);

        if (!in_array('invite', $register_type) && !in_array('normal', $register_type)) {
            $this->error(L('_ERROR_WEBSITE_REG_CLOSED_'));
        }

        if (in_array('invite', $register_type) && $aCode != '') { //邀请注册开启且有邀请码
            $invite = D('Ucenter/Invite')->getByCode($aCode);
            if ($invite) {
                if ($invite['end_time'] <= time()) {
                    $this->error(L('_ERROR_EXPIRED_').L('_EXCLAMATION_'));
                } else { //获取注册角色
                    $map['id'] = $invite['invite_type'];
                    $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
                    if ($invite_type) {
                        if (count($invite_type['roles'])) {
                            //角色
                            $map_role['status'] = 1;
                            $map_role['id'] = array('in', $invite_type['roles']);
                            $roleList = D('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                            if (!count($roleList)) {
                                $this->error(L('_ERROR_ROLE_').L('_EXCLAMATION_'));
                            }
                            //角色end
                        } else {
                            //角色
                            $map_role['status'] = 1;
                            $map_role['invite'] = 0;
                            $roleList = D('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                            //角色end
                        }
                        $this->assign('code', $aCode);
                        $this->assign('invite_user', $invite['user']);
                    } else {
                        $this->error(L('_ERROR_FORBIDDEN_2_').L('_EXCLAMATION_'));
                    }
                }
            } else {
                $this->error(L('_ERROR_NOT_EXIST_').L('_EXCLAMATION_'));
            }
        } else {
            //（开启邀请注册且无邀请码）或（只开启了普通注册）
            if (in_array('invite', $register_type)) {
                $this->assign('open_invite_register', 1);
            }

            if (in_array('normal', $register_type)) {
                //角色
                $map_role['status'] = 1;
                $map_role['invite'] = 0;
                $roleList = D('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                //角色end
            } else {
                //（只开启了邀请注册）
                $this->error(L('_ERROR_NOT_INVITED_').L('_EXCLAMATION_'));
            }
        }
        $this->assign('role_list', $roleList);
        return true;
    }

    /**
     * 判断邀请码是否可用
     * @param string $code
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function checkInviteCode($code = '')
    {
        if ($code == '') {
            return true;
        }
        $invite = D('Ucenter/Invite')->getByCode($code);
        if ($invite['end_time'] >= time()) {
            $map['id'] = $invite['invite_type'];
            $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
            if ($invite_type) {
                return true;
            }
        }
        return false;
    }

    private function initInviteUser($uid = 0, $code = '', $role = 0)
    {
        if ($code != '') {
            $inviteModel = D('Ucenter/Invite');
            $invite = $inviteModel->getByCode($code);
            $data['inviter_id'] = abs($invite['uid']);
            $data['uid'] = $uid;
            $data['invite_id'] = $invite['id'];
            $result = D('Ucenter/InviteLog')->addData($data, $role);
            if ($result) {
                D('Ucenter/InviteUserInfo')->addSuccessNum($invite['invite_type'], abs($invite['uid']));

                $invite_info['already_num'] = $invite['already_num'] + 1;
                if ($invite_info['already_num'] == $invite['can_num']) {
                    $invite_info['status'] = 0;
                }
                $inviteModel->where(array('id' => $invite['id']))->save($invite_info);

                $map['id'] = $invite['invite_type'];
                $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
                if ($invite_type['is_follow']) {
                    $followModel = D('Common/Follow');
                    $followModel->addFollow($uid, abs($invite['uid']),1);
                    $followModel->addFollow(abs($invite['uid']), $uid,1);
                }
                if ($invite['uid'] > 0) {
                    D('Ucenter/Score')->setUserScore(array($invite['uid']), $invite_type['income_score'], $invite_type['income_score_type'], 'inc', '', 0, L('_ERROR_BONUS_'));
                }
            }
        }
        return true;
    }

}
<?php


namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;

class ManagerfcController extends AdminController
{
   //房卡管理
   public function index($page = 1, $r = 15)
    {
		$search=I('search');
		if(!empty($search)){
			$map="account_id ='".$search."'";
		}
		$gameid=I('gameid');
		
		if($gameid==0){
			//$this->error("没有选择游戏");
		}
        //$map['status'] = array('egt', 0);
        //$profileList = D('player')->where($map)->page($page, $r)->select();
		if(!empty($search)&&$gameid==1){
			$_REQUEST['r']=$r;//显示条数
			$profileList=$this->lists('player', $map,$order = '', $base = array('status' => array('egt', 0)), $field = true);
		}
		if($gameid==2){
			$param['id']=$search;
			$r=request_post("http://wx.91yuji.com/index/Gameservice/sgFindPlayer",$param);
			$arr=json_decode($r);
			
			if(!$arr->data){
				$this->error("玩家不存在");
			}else{
				$player['account_id']=$search;
				$player['username']=$arr->data;
				$player['gold']="";
				$profileList[]=$player;	
			}
		}
        $totalCount = D('player')->where($map)->count();
        /* $builder = new AdminListBuilder();
		$builder->title("玩家列表");
        $builder->meta_title = '玩家列表';
		$builder->search('查询ID','search','text');
        //$builder->buttonNew(U('editProfile', array('id' => '0')))->buttonDelete(U('del'));
        $builder->keyText('account_id', "玩家ID")->keyText('username', '微信OPENID')->keyText('gold','房卡数量');
        $builder->keyDoAction('addfc?id=###', '拨房卡');
        $builder->data($profileList);
        $builder->pagination($totalCount, $r);
        $builder->display(); */
		
		$this->assign('_list', $profileList);
        $this->meta_title ="玩家列表";
		$this->assign('search',$search);
		$this->assign('gameid',$gameid);
		$this->display();
		
    }
	
	//拨房卡
   public function addfc($page = 1, $page = 15){
	   $id=I('id');
	   $gameid=I('gameid');
	   
	    //我当前的房卡数  member{point}
		$my=D('member')->field('nickname,point')->where('uid="'.UID.'"')->find();
		$this->assign('my',$my);
		  
	   if(IS_POST){
	   	 
	   	
	   	if($gameid==1){
	  /**
	   *   娱记麻将拨房卡
	   */
		  //验证是否是正整数
		  $gold_add=intval(I('gold_add'));
		  //if(is_numeric($gold_add)){
			  //$this->error("请填写整数");
		  //}
		  $map="account_id='".$id."'";
		  $ret=D('player')->where($map)->setInc('gold',$gold_add);
		  
		  //如果是代理 需要扣房卡
		  if(UID!=1){
		  	
			$member=D('member')->field('account_id')->where('uid="'.UID.'"')->find();
			$account_id=$member['account_id'];
			
			//如果拨房卡大于自己拥有的房卡数，提醒错误
			if($gold_add>$my['point']){
				$this->error("拨房卡数量请不要超过你拥有的房卡数量！");
			}
			
			//$data['account_id']=UID;
			//$data['create_time']=time();
			//$data['gold_num']=0-$gold_add;
			//$data['do_user']='0';
			//$ret=D('player_log')->add($data);
			
		  }
		  $data['account_id']=$id;
		  $data['create_time']=time();
		  $data['gold_num']=$gold_add;
		  $data['do_user']=UID;
		  $data['game']=1;
		  $ret=D('player_log')->add($data);
		  
		   if($ret){
			   $this->success("拨房卡成功，请刷新");
		   }else{
			   $this->error("拨房卡失败，请重新操作或者联系管理员");
		   }
	   	}else if($gameid==2){
		   /**
		    * 三公麻将拨房卡
		    */
	   		$gold_add=intval(I('gold_add'));
	   		$param['id']=$id;
	   		$param['num']=$gold_add;
	   		$r=request_post("http://wx.91yuji.com/index/Gameservice/sgCard",$param);
	   		$json=json_decode($r);
	   		if($json->status){
	   			$data['account_id']=$id;
	   			$data['create_time']=time();
	   			$data['gold_num']=$gold_add;
	   			$data['do_user']=UID;
	   			$data['game']=2;
	   			$ret=D('player_log')->add($data);
	   			
	   			if($ret){
	   				$this->success("拨房卡成功，请刷新");
	   			}else{
	   				$this->error("拨房卡失败，请重新操作或者联系管理员");
	   			}	
	   		}
	   	}
	   	//代理扣除房卡
	   	$map=" uid='".$id."'";
	   	$ret=D('member')->where($map)->setDec('point',$gold_add);
	   	
	   }else{
	   	
	   	$map="account_id='".$id."'";
	   	
	   	if($gameid==1){
		  $data=D('player')->where($map)->find();
	   	}elseif($gameid==2){
	   		$param['id']=$id;
			$r=request_post("http://wx.91yuji.com/index/Gameservice/sgFindPlayer",$param);
			$arr=json_decode($r);
			$data['account_id']=$id;
			$data['username']=$arr->data;
	   	}
		  //拨房卡记录
		  $_REQUEST['r']=$page;//显示条数
		  $profileList=$this->lists('player_log', $map);
		  
		  $this->meta_title ="拨房卡";
		  $this->assign('data',$data);
		  $this->assign('_list', $profileList);
		  $this->assign('gameid',$gameid);
		  $this->display();
		  
	   }
	   exit;
   }
   
   //给代理拨房卡
   public function givePoint(){
   		$uid=I('uid');
   		$point=I('point');
   		$map['uid']=$uid;
   		$r=M('member')->where($map)->setInc('point',$point);
   			if($r){
   				echo "拨卡成功";
   			}else{
   				echo "拨卡失败";
   			}
   	}
   
}
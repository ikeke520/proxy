<?php
namespace Ucenter\Controller;

use Think\Controller;

class IndexController extends BaseController
{
    public function _initialize()
    {
        //parent::_initialize();
       $uid = session('login_userid')? op_t(session('login_userid')) : D('Accounts')->is_login();
       if(!$uid){
       		redirect("index.php?s=Ucenter/member/login");
       }
      
        //调用API获取基本信息
       	$this->userInfo($uid);
        $this->_fans_and_following($uid);

        $this->_tab_menu();

    }
    public function index($uid = null,$page=1)
    {
        $show_tab= get_kanban_config('UCENTER_KANBAN', 'enable','', 'USERCONFIG');
        $menu=$this->_tab_menu();
        foreach($show_tab as $v1) {
            foreach($menu as $v2) {
                if (array_search($v1,$v2)) {
                    $arr3[$v1] = $v2;
                }
            }
        }
        unset($v1);unset($v2);
        $appArr =$arr3;
        $current_action=current($appArr);
        $url_link=array(
            'info'=>'Ucenter/Index/Center',
            'rank_title'=>'Ucenter/Index/rank',
            'follow'=>'Ucenter/Index/following',
        );
       
        if(!$current_action){
            $this->redirect('Ucenter/Index/Center', array('uid' => $uid));
        }
        if (in_array($current_action['data-id'],array('info','rank_title','follow'))) {
            $this->redirect($url_link[$current_action['data-id']], array('uid' => $uid));
        }
        $type=key($appArr);
        if (!isset ($appArr [$type]))
        {
            $this->error(L('_ERROR_PARAM_').L('_EXCLAMATION_').L('_EXCLAMATION_'));
        }
		
        $this->assign('type', $type);
        $this->assign('module',$appArr[$type]['data-id']);
        $this->assign('page',$page);
		
        //四处一词 seo
        $str = '{$user_info.nickname|text}';
        $str_app = '{$appArr.'.$type.'.title|text}';
        $this->setTitle($str . L('_INDEX_TITLE_'));
        $this->setKeywords($str . L('_PAGE_PERSON_') . $str_app);
        $this->setDescription($str . L('_DE_PERSON_') . $str_app . L('_PAGE_'));
        //四处一词 seo end
        $this->display();
    }
	
    private function userInfo($uid = null)
    {
        //获取用户封面id
       	$user_info=D('Accounts')->userinfo($uid);
        $this->assign('user_info', $user_info);
        return $user_info;
    }
	//个人中心
	public function Center(){
		
		$this->display("myCenter");
	}
   
	public function BokaLog(){
		$uid=is_login();
		if(!$uid){
			redirect(U("Ucenter/member/login"));
		}
		$this->display();
	}
	
	public function dailiboka($uid = null)
	{
		$uid=is_login();
		if(!$uid){
			redirect(U("Ucenter/member/login"));
		}
		//显示页面
		$user=D()->table("tp_accounts_info")->where("UserID=".$uid)->find();
		//获取当前玩家信息
		$player=D()->table("player")->where("username='".$user['Unionid']."'")->find();
		$this->assign('player', $player);
		//四处一词 seo end
		$this->display();
	}
    public function information($uid = null)
    {
	   $uid=is_login();
	   if(!$uid){
	   	redirect(U("Ucenter/member/login"));
	   }
        //显示页面
		$user=D()->table("tp_accounts_info")->where("UserID=".$uid)->find();
        //获取当前玩家信息
        $player=D()->table("player")->where("username='".$user['Unionid']."'")->find();
        $this->assign('player', $player);
        //四处一词 seo end
        $this->display();
    }
    /**
     * 通过id获取代理信息--娱记游戏
     */
    public function getDLPlayerinfo(){
    	$id=I('id');
    	$player=D('tp_accounts_info')->where('UserID="'.$id.'"')->find();
    	 
    	if($player){
    		$html.='<img src="'.$player['headimgurl'].'"/>
		<span>
		<button  type="button" class="btn" data-toggle="modal" data-target="#myModal" >拨卡</button></span><span>
		';
    		$html.='
		</span>
		<span>'.$player['yb_coin'].'</span><span>'.$player['UserID'].'</span>';
    	}else{
    		$html="未查到该玩家";
    	}
    	echo $html;exit;
    }
    /**
     * 通过游戏id获取玩家信息--娱记游戏
     */
    public function getYJPlayerinfo(){
    	$id=I('id');
    	$player=D('player')->where('account_id="'.$id.'"')->find();
    	
    	if($player){
    		$map['Unionid']=$player['username'];
    		$playerext=D()->table('tp_accounts_info')->where($map)->find();
    		$player['headimgurl']=$playerext['Headimgurl'];
    	}
    	if($player){
    		$html.='<img src="'.$player['headimgurl'].'"/>
		<span>
		<button  type="button" class="btn" data-toggle="modal" data-target="#myModal" >拨卡</button></span><span>
		';
		if($player['type']==1){
			$player['type_name']="开通";
		}else{
			$player['type_name']='<button type="button" class="btn" onclick="jlb('.$player['account_id'].');";>授权</button>';	
		}
		
		$html.=$player['type_name'].'
		</span>
		<span>'.$player['gold'].'</span><span>'.$player['account_id'].'</span>';
    	}else{
    		$html="未查到该玩家";
    	}
    	echo $html;exit;
    }
	/**
		获取查询用户表
	*/
	public function getPlayerInfor(){
		$id=I('id');
		//$gameid=I('gameid');
		if($gameid==1){
			$player=D('player')->where('account_id="'.$id.'"')->find();
			
			if($player){
				$map['Unionid']=$player['username'];
				$playerext=D()->table('tp_accounts_info')->where($map)->find();
				$player['headimgurl']=$playerext['Headimgurl'];
			}
			
		}else if($gameid==2){
			$param['id']=$id;
			$arr=request_post("http://wx.91yuji.com/index/Gameservice/sgFindPlayer",$param);
			$json=json_decode($arr);
			
			if($json->data){
				$player['account_id']=$id;
				$player['headimgurl']=$json->data->headimgurl;
				$player['username']=$json->data->nickname;
				$player['gold']=$json->data->card;
			}
		}
		//获取玩家的信息
		
	if($player){
		$qx=$player['type']==1?'有':'无';
		
		$html='<table class="table datatable">
  <thead>
    <tr>
      <th>玩家头像</th>

      <!-- 以下三列中间可滚动 -->
      <th class="flex-col">玩家ID</th> 
      <th class="flex-col">房卡数量</th>
	  <th class="flex-col">俱乐部权限</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody>
	 <tr>
		<td><img src='.$player['headimgurl'].' style="width:4rem;height:4rem"/></td>
		<td>'.$player['account_id'].'</td>
		<td>'.$player['gold'].'</td>
		<td>'.$qx.'</td>	
		<td><button type="button" class="btn" data-toggle="modal" data-target="#myModal">拨房卡</button>
		';
		if($player['type']!=1){
			$html.='<button type="button" class="btn" onclick="jlb('.$player['account_id'].');";>授权</button>';
		}
		$html.='		
		</td>
	 </tr>
  </tbody>
</table>';
	}else{
		$html='<table class="table datatable">
  <thead>
    <tr>
      <th>玩家ID</th>
      <!-- 以下三列中间可滚动 -->
      <th class="flex-col">玩家微信OPENID</th> 
      <th class="flex-col">房卡数量</th>
      <th>操作</th>
    </tr>
  </thead>
  <tbody>
	 <tr>
		<td cols="3">玩家不存在</td>
	 </tr>
  </tbody>
</table>';
	}
		echo $html;
	}
	/**
	 * 俱乐部授权
	 */
	public function jlbsq(){
		$id=I('id');
		
		if($id){
			$ret=D()->table("player")->where("account_id='".$id."'")->save(array('type'=>1));
		}
		if($ret){
			echo 1;
		}else{
			echo 0;
		}
	}
	/**代理拨房卡
	 */
	public function Dlboka(){
		$id=I('id');
		$num=I('num');
		$gameid=1;
	
		$uid=session('login_userid');
		if(!$uid){
			return false;
		}
		//验证是否是正整数
		$gold_add=intval($num);
		//$this->error($gold_add);
		if($gold_add<=0){
			echo "请填写正整数";exit;
		}
	
		//如果是代理 需要扣房卡
		 
		$my=D()->table("tp_accounts_info")->field('UserID,yb_coin')->where('UserID="'.$uid.'"')->find();
			
		//如果拨房卡大于自己拥有的房卡数，提醒错误
		if($gold_add>$my['yb_coin']){
			$this->error("拨房卡数量请不要超过你拥有的房卡数量！");
		}
			
		$map="UserID='".$uid."'";
		$ret=D('tp_accounts_info')->where($map)->setDec('yb_coin',$gold_add);
			
	
		if($gameid==1){
			//增加客户房卡
			$map="UserID='".$id."'";
			$ret=D('tp_accounts_info')->where($map)->setInc('yb_coin',$gold_add);
	
			//房卡记录
			$data['account_id']=$id;
			$data['create_time']=time();
			$data['gold_num']=$gold_add;
			$data['do_user']=$uid;
			$data['game']=1;
			$ret=D('player_log')->add($data);
	
		}
		if($ret){
			$this->success("拨房卡成功");
		}else{
			$this->error("拨房卡失败");
		}
			
	}
	/**增加房卡
	*/
	public function addGoldNum(){
		$id=I('id');
		$num=I('num');
		$gameid=1;
		
		$uid=session('login_userid');
		 if(!$uid){
		 	return false;	
		 }
		 //验证是否是正整数
		  $gold_add=intval($num);
		  //$this->error($gold_add);
		  if($gold_add<=0){
			  echo "请填写正整数";exit;
		  }
		  
		  //如果是代理 需要扣房卡
		  	
			$my=D()->table("tp_accounts_info")->field('UserID,yb_coin')->where('UserID="'.$uid.'"')->find();
			
			//如果拨房卡大于自己拥有的房卡数，提醒错误
			if($gold_add>$my['yb_coin']){
				$this->error("拨房卡数量请不要超过你拥有的房卡数量！");
			}
			
			$map="UserID='".$uid."'";
			$ret=D('tp_accounts_info')->where($map)->setDec('yb_coin',$gold_add);
			
		  
		  if($gameid==1){
		  //增加客户房卡
		  $map="account_id='".$id."'";
		  $ret=D('player')->where($map)->setInc('gold',$gold_add);
		  
		  //房卡记录
		  $data['account_id']=$id;
		  $data['create_time']=time();
		  $data['gold_num']=$gold_add;
		  $data['do_user']=$uid;
		  $data['game']=1;
		  $ret=D('player_log')->add($data);
		  
		  }else if($gameid==2){
		  		
		  	$param['id']=$id;
		  	$param['num']=$gold_add;
		  	$r=request_post("http://wx.91yuji.com/index/Gameservice/sgCard",$param);
		  	$json=json_decode($r);
		  	if($json->status){
		  		$data['account_id']=$id;
		  		$data['create_time']=time();
		  		$data['gold_num']=$gold_add;
		  		$data['do_user']=$uid;
		  		$data['game']=2;
		  		$ret=D('player_log')->add($data);
		  	}
		  }
			if($ret){
			  $this->success("拨房卡成功");
		   }else{
			  $this->error("拨房卡失败");
		   }
		 
	}
	/**
		获取房卡记录
	**/
	public function getGoldLog(){
		$uid=session("login_userid");
		$page=I('page');
		
		$goldlognum=D('player_log')->where(' do_user ="'.$uid.'"')->count();
		$goldlog=D('player_log')->where(' do_user ="'.$uid.'"')->page($page,10)->order('create_time desc')->select();
		$html="";
		foreach($goldlog as $k=>$v){
			if($v['game']==1){
				$v['gamename']="娱记麻将";
			}else{
				$v['gamename']="三公游戏";
			}
			$html.="<tr><td>".$v['account_id']."</td><td>".date("Y-m-d H:i:s",$v['create_time'])."</td><td>".$v['gold_num']."</td><td>".$v['gamename']."</td></tr>";
		};
		$this->success($html);
	}
	/**
		获取自己的房卡
	*/
	public function getFc(){
		$uid=is_login();
		$member=D('member')->field('account_id')->where('uid="'.$uid.'"')->find();
		$account_id=$member['account_id'];
		$my=D('player')->where('account_id="'.$account_id.'"')->find();
		$this->success($my['gold']);
	}
	
    /**获取用户扩展信息
     * @param null $uid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function getExpandInfo($uid = null, $profile_group_id = null)
    {
        $profile_group_list = $this->_profile_group_list($uid);
        foreach ($profile_group_list as &$val) {
            $val['info_list'] = $this->_info_list($val['id'], $uid);
        }
        $this->assign('profile_group_list', $profile_group_list);
    }

    /**扩展信息分组列表获取
     * @param null $uid
     * @return mixed
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function _profile_group_list($uid = null)
    {
        $profile_group_list=array();
        $fields_list=$this->getRoleFieldIds($uid);
        if($fields_list){
            $fields_group_ids=D('FieldSetting')->where(array('id'=>array('in',$fields_list),'status' => '1'))->field('profile_group_id')->select();
            if($fields_group_ids){
                $fields_group_ids=array_unique(array_column($fields_group_ids,'profile_group_id'));
                $map['id']=array('in',$fields_group_ids);

                if (isset($uid) && $uid != is_login()) {
                    $map['visiable'] = 1;
                }
                $map['status'] = 1;
                $profile_group_list = D('field_group')->where($map)->order('sort asc')->select();
            }
        }
        return $profile_group_list;
    }

    private function getRoleFieldIds($uid=null){
        $role_id=get_role_id($uid);
        $fields_list=S('Role_Expend_Info_'.$role_id);
        if(!$fields_list){
            $map_role_config=getRoleConfigMap('expend_field',$role_id);
            $fields_list=D('RoleConfig')->where($map_role_config)->getField('value');
            if($fields_list){
                $fields_list=explode(',',$fields_list);
                S('Role_Expend_Info_'.$role_id,$fields_list,600);
            }
        }
        return $fields_list;
    }

    /**分组下的字段信息及相应内容
     * @param null $id
     * @param null $uid
     * @return null
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function _info_list($id = null, $uid = null)
    {
        $fields_list=$this->getRoleFieldIds($uid);
        $info_list = null;

        if (isset($uid) && $uid != is_login()) {
            //查看别人的扩展信息
            $field_setting_list = D('field_setting')->where(array('profile_group_id' => $id, 'status' => '1', 'visiable' => '1','id'=>array('in',$fields_list)))->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = $uid;
        } else if (is_login()) {
            $field_setting_list = D('field_setting')->where(array('profile_group_id' => $id, 'status' => '1','id'=>array('in',$fields_list)))->order('sort asc')->select();

            if (!$field_setting_list) {
                return null;
            }
            $map['uid'] = is_login();

        } else {
            $this->error(L('_ERROR_PLEASE_LOGIN_').L('_EXCLAMATION_'));
        }
        foreach ($field_setting_list as &$val) {
            $map['field_id'] = $val['id'];
            $field = D('field')->where($map)->find();
            $val['field_content'] = $field;
            unset($map['field_id']);
            $info_list[$val['id']] = $this->_get_field_data($val);
            //当用户扩展资料为数组方式的处理@MingYangliu
            $vlaa = explode('|', $val['form_default_value']);
            $needle =':';//判断是否包含a这个字符
            $tmparray = explode($needle,$vlaa[0]);
            if(count($tmparray)>1){
                foreach ($vlaa as $kye=>$vlaas){
                    if(count($tmparray)>1){
                        $vlab[] = explode(':', $vlaas);
                        foreach ($vlab as $key=>$vlass){
                            $items[$vlass[0]] = $vlass[1];
                        }
                    }
                    continue;
                }
                $info_list[$val['id']]['field_data'] = $items[$info_list[$val['id']]['field_data']];
            }
            //当扩展资料为join时，读取数据并进行处理再显示到前端@MingYang
            if($val['child_form_type'] == "join"){
                $j = explode('|',$val['form_default_value']);
                $a = explode(' ',$info_list[$val['id']]['field_data']);
                $info_list[$val['id']]['field_data'] = get_userdata_join($a,$j[0],$j[1]);
            }
        }
        return $info_list;
    }

    public function _get_field_data($data = null)
    {
        $result = null;
        $result['field_name'] = $data['field_name'];
        $result['field_data'] = L('');
        switch ($data['form_type']) {
            case 'input':
            case 'radio':
            case 'textarea':
            case 'select':
                $result['field_data'] = isset($data['field_content']['field_data']) ? $data['field_content']['field_data'] : "还未设置";
                break;
            case 'checkbox':
                $result['field_data'] = isset($data['field_content']['field_data']) ? implode(' ', explode('|', $data['field_content']['field_data'])) : "还未设置";
                break;
            case 'time':
                $result['field_data'] = isset($data['field_content']['field_data']) ? date("Y-m-d", $data['field_content']['field_data']) : "还未设置";
                break;
        }
        $result['field_data'] = op_t($result['field_data']);
        return $result;
    }

    public function appList($uid = null, $page = 1, $tab = null)
    {
        $show_tab= get_kanban_config('UCENTER_KANBAN', 'enable','', 'USERCONFIG');
        $menu=$this->_tab_menu();
        foreach($show_tab as $v1) {
            foreach($menu as $v2) {
                if (array_search($v1,$v2)) {
                    $arr3[$v1] = $v2;
                }
            }
        }
        unset($v1);unset($v2);
        $appArr =$arr3;

        if (!$appArr) {
            $this->redirect('Usercenter/Index/information', array('uid' => $uid));
        }

        $type = op_t($_GET['type']);
        if (!isset ($appArr [$type])) {
            $this->error(L('_ERROR_PARAM_').L('_EXCLAMATION_').L('_EXCLAMATION_'));
        }
        $this->assign('type', $type);
        $this->assign('module',$appArr[$type]['data-id']);
        $this->assign('page',$page);
        $this->assign('tab',$tab);

        //四处一词 seo
        $str = '{$user_info.nickname|op_t}';
        $str_app = '{$appArr.'.$type.'.title|op_t}';
        $this->setTitle($str . L('_DE_PERSON_') . $str_app . L('_PAGE_'));
        $this->setKeywords($str . L('_PAGE_PERSON_') . $str_app);
        $this->setDescription($str . L('_DE_PERSON_') . $str_app . L('_PAGE_'));
        //四处一词 seo end

        $this->display('index');
    }

    /**
     * 个人主页标签导航
     * @return void
     */
    public function _tab_menu()
    {
        $modules = D('Common/Module')->getAll();
        $apps = array();
        foreach ($modules as $m) {
            if ($m['is_setup'] == 1 && $m['entry'] != '') {
                if (file_exists(APP_PATH . $m['name'] . '/Widget/UcenterBlockWidget.class.php')) {
                    $apps[] = array('data-id' => $m['name'], 'title' => $m['alias'],'sort'=>$m['sort'],'key'=>strtolower($m['name']));
                }
            }
        }

        $show_tab= get_kanban_config('UCENTER_KANBAN', 'enable','', 'USERCONFIG');
        $apps[] = array('data-id' => 'info', 'sort'=>'0', 'title' =>'资料','key'=>'info');
        $apps[] = array('data-id' => 'rank_title', 'sort'=>'0', 'title' => L('_RANK_TITLE_'),'key'=>'rank_title');
        $apps[] = array('data-id' => 'follow', 'sort'=>'0','title' =>L('_FOLLOWERS_NO_SPACE_').'/粉丝','key'=>'follow');

        $apps = $this->sortApps($apps);
        $apps=array_combine(array_column($apps,'key'),$apps);
        foreach($show_tab as $v1) {
            foreach($apps as $v2) {
                if (array_search($v1,$v2)) {
                    $arr3[$v1] = $v2;
                }
            }
        }
        unset($v1);unset($v2);
        $this->assign('appArr', $arr3);
        return $apps;
    }


    public function _fans_and_following($uid = null)
    {
        $uid = isset($uid) ? $uid : is_login();
        //我的粉丝展示
        $map['follow_who'] = $uid;
        $fans_default = D('Follow')->where($map)->field('who_follow')->order('create_time desc')->limit(8)->select();
        $fans_totalCount = D('Follow')->where($map)->count();
        foreach ($fans_default as &$user) {
            $user['user'] = query_user(array('avatar64', 'uid', 'nickname', 'fans', 'following', 'weibocount', 'space_url', 'title'), $user['who_follow']);
        }
        unset($user);
        $this->assign('fans_totalCount', $fans_totalCount);
        $this->assign('fans_default', $fans_default);

        //我关注的展示
        $map_follow['who_follow'] = $uid;
        $follow_default = D('Follow')->where($map_follow)->field('follow_who')->order('create_time desc')->limit(8)->select();
        $follow_totalCount = D('Follow')->where($map_follow)->count();
        foreach ($follow_default as &$user) {
            $user['user'] = query_user(array('avatar64', 'uid', 'nickname', 'fans', 'following', 'weibocount', 'space_url', 'title'), $user['follow_who']);
        }
        unset($user);
        $this->assign('follow_totalCount', $follow_totalCount);
        $this->assign('follow_default', $follow_default);
    }

    public function fans($uid = null, $page = 1)
    {
        $uid = isset($uid) ? $uid : is_login();

        $this->assign('tab', 'fans');
        $fans = D('Follow')->getFans($uid, $page, array('avatar128', 'uid', 'nickname', 'fans', 'following', 'weibocount', 'space_url', 'title'), $totalCount);
        $this->assign('fans', $fans);
        $this->assign('totalCount', $totalCount);

        //四处一词 seo
        $str = '{$user_info.nickname|op_t}';
        $this->setTitle($str . L('_FANS_TITLE_'));
        $this->setKeywords($str . L('_FANS_KEYWORDS_'));
        $this->setDescription($str . L('_FANS_TITLE_'));
        //四处一词 seo end

        $this->display();
    }

    public function following($uid = null, $page = 1)
    {
        $uid = isset($uid) ? $uid : is_login();

        $following = D('Follow')->getFollowing($uid, $page, array('avatar128', 'uid', 'nickname', 'fans', 'following', 'weibocount', 'space_url', 'title'), $totalCount);
       // dump($following);exit;
        $this->assign('following', $following);
        $this->assign('totalCount', $totalCount);
        $this->assign('tab', 'following');

        //四处一词 seo
        $str = '{$user_info.nickname|op_t}';
        $this->setTitle($str . L('_FOLLOWING_TITLE_'));
        $this->setKeywords($str . L('_FOLLOWING_KEYWORDS_'));
        $this->setDescription($str . L('_FOLLOWING_DESC_'));
        //四处一词 seo end

        $this->display();
    }

    public function rank($uid = null)
    {
        $uid = isset($uid) ? $uid : is_login();

        $rankList = D('rank_user')->where(array('uid' => $uid, 'status' => 1))->field('rank_id,reason,create_time')->select();
        foreach ($rankList as &$val) {
            $rank = D('rank')->where('id=' . $val['rank_id'])->find();
            $val['title'] = $rank['title'];
            $val['logo_url'] = get_pic_src(M('picture')->where('id=' . $rank['logo'])->field('path')->getField('path'));
            $val['label_content']=$rank['label_content'];
            $val['label_bg']=$rank['label_bg'];
            $val['label_color']=$rank['label_color'];
        }
        unset($val);
        $this->assign('rankList', $rankList);
        $this->assign('tab', 'rank');

        //四处一词 seo
        $str = '{$user_info.nickname|op_t}';
        $this->setTitle($str . L('_RANK__TITLE_'));
        $this->setKeywords($str . L('_RANK__KEYWORDS_'));
        $this->setDescription($str . L('_RANK__DESC_'));
        //四处一词 seo end

        $this->display('rank');
    }

    public function rankVerifyFailure()
    {
        $uid = isset($uid) ? $uid : is_login();

        $rankList = D('rank_user')->where(array('uid' => $uid, 'status' => -1))->field('id,rank_id,reason,create_time')->select();
        foreach ($rankList as &$val) {
            $rank = D('rank')->where('id=' . $val['rank_id'])->find();
            $val['title'] = $rank['title'];
            $val['logo_url'] = get_pic_src(M('picture')->where('id=' . $rank['logo'])->field('path')->getField('path'));
            $val['label_content']=$rank['label_content'];
            $val['label_bg']=$rank['label_bg'];
            $val['label_color']=$rank['label_color'];
        }
        unset($val);
        $this->assign('rankList', $rankList);
        $this->assign('tab', 'rankVerifyFailure');

        //四处一词 seo
        $str = '{$user_info.nickname|op_t}';
        $this->setTitle($str . L('_RANK_TITLE_'));
        $this->setKeywords($str . L('_RANK__KEYWORDS_'));
        $this->setDescription($str . L('_RANK_TITLE_'));
        //四处一词 seo end

        $this->display('rank');
    }

    public function rankVerifyWait()
    {
        $uid = isset($uid) ? $uid : is_login();

        $rankList = D('rank_user')->where(array('uid' => $uid, 'status' => 0))->field('rank_id,reason,create_time')->select();
        foreach ($rankList as &$val) {
            $rank = D('rank')->where('id=' . $val['rank_id'])->find();
            $val['title'] = $rank['title'];
            $val['logo_url'] = get_pic_src(M('picture')->where('id=' . $rank['logo'])->field('path')->getField('path'));
            $val['label_content']=$rank['label_content'];
            $val['label_bg']=$rank['label_bg'];
            $val['label_color']=$rank['label_color'];
        }
        unset($val);
        $this->assign('rankList', $rankList);
        $this->assign('tab', 'rankVerifyWait');

        //四处一词 seo
        $str = '{$user_info.nickname|op_t}';
        $this->setTitle($str . L('_RANK_TITLE_'));
        $this->setKeywords($str . L('_RANK__KEYWORDS_'));
        $this->setDescription($str . L('_RANK_TITLE_'));
        //四处一词 seo end

        $this->display('rank');
    }

    public function rankVerifyCancel($rank_id = null)
    {
        $rank_id = intval($rank_id);
        if (is_login() && $rank_id) {
            $map['rank_id'] = $rank_id;
            $map['uid'] = is_login();
            $map['status'] = 0;
            $result = D('rank_user')->where($map)->delete();
            if ($result) {
                D('Message')->sendMessageWithoutCheckSelf(is_login(),L('_MESSAGE_RANK_CANCEL_1_'),  L('_MESSAGE_RANK_CANCEL_2_'), 'Ucenter/Message/message', array('tab' => 'system'));
                $this->success(L('_SUCCESS_CANCEL_'), U('Ucenter/Index/rankVerifyWait'));
            } else {
                $this->error(L('_FAIL_CANCEL_'));
            }
        }
    }

    public function rankVerify($rank_user_id = null)
    {
        $uid = isset($uid) ? $uid : is_login();

        $rank_user_id = intval($rank_user_id);
        $map_already['uid'] = $uid;
        //重新申请头衔
        if ($rank_user_id) {
            $model = D('rank_user')->where(array('id' => $rank_user_id));
            $old_rank_user = $model->field('id,rank_id,reason')->find();
            if (!$old_rank_user) {
                $this->error(L('_ERROR_RANK_RE_SELECT_'));
            }
            $this->assign('old_rank_user', $old_rank_user);
            $map_already['id'] = array('neq', $rank_user_id);
            D('Message')->sendMessageWithoutCheckSelf(is_login(), L(''),L(''),  'Ucenter/Message/message', array('tab' => 'system'));
        }
        $alreadyRank = D('rank_user')->where($map_already)->field('rank_id')->select();
        $alreadyRank = array_column($alreadyRank, 'rank_id');
        if ($alreadyRank) {
            $map['id'] = array('not in', $alreadyRank);
        }
        $map['types'] = 1;
        $rankList = D('rank')->where($map)->select();
        foreach($rankList as &$rank){
            $rank['logo_url'] = get_pic_src(M('picture')->where('id=' . $rank['logo'])->field('path')->getField('path'));
        }
        unset($rank);
        $this->assign('rankList', $rankList);
        $this->assign('tab', 'rankVerify');

        //四处一词 seo
        $str = '{$user_info.nickname|op_t}';
        $this->setTitle($str . L('_RANK_APPLY_TITLE_'));
        $this->setKeywords($str . L('_RANK_APPLY_KEYWORDS_'));
        $this->setDescription($str . L('_RANK_APPLY_TITLE_'));
        //四处一词 seo end

        $this->display('rank_verify');
    }

    public function verify($rank_id = null, $reason = null, $rank_user_id = 0)
    {
        $rank_id = intval($rank_id);
        $reason = op_t($reason);
        $rank_user_id = intval($rank_user_id);
        if (!$rank_id) {
            $this->error(L('_ERROR_RANK_SELECT_'));
        }
        if ($reason == null || $reason == '') {
            $this->error(L('_ERROR_RANK_REASON_'));
        }
        $data['rank_id'] = $rank_id;
        $data['reason'] = $reason;
        $data['uid'] = is_login();
        $data['is_show'] = 1;
        $data['create_time'] = time();
        $data['status'] = 0;
        if ($rank_user_id) {
            $model = D('rank_user')->where(array('id' => $rank_user_id));
            if (!$model->select()) {
                $this->error(L('_ERROR_RANK_RE_SELECT_'));
            }
            $result = D('rank_user')->where(array('id' => $rank_user_id))->save($data);
        } else {
            $result = D('rank_user')->add($data);
        }
        if ($result) {
            D('Message')->sendMessageWithoutCheckSelf(is_login(),L('_MESSAGE_RANK_APPLY_1_'),L('_MESSAGE_RANK_APPLY_2_'),  'Ucenter/Message/message', array('tab' => 'system'));
            $this->success(L('_SUCCESS_RANK_APPLY_'), U('Ucenter/Index/rankVerify'));
        } else {
            $this->error(L('_FAIL_RANK_APPLY_'));
        }
    }

    /**
     * @param $apps
     * @param $vals
     * @return mixed
     * @auth 陈一枭
     */
    private function sortApps($apps)
    {
        return $this->multi_array_sort($apps, 'sort', SORT_DESC);
    }

    function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
    {
        if (is_array($multi_array)) {
            foreach ($multi_array as $row_array) {
                if (is_array($row_array)) {
                    $key_array[] = $row_array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_array, $sort, $multi_array);
        return $multi_array;
    }

}
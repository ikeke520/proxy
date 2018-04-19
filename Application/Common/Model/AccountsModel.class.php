<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Model;

use Think\Model;


/**
 * 文档基础模型
 */
class AccountsModel extends Model
{
    /* 用户模型自动完成 */
    protected $_auto = array(
       
    );

//     protected $_validate = array(
//         array('signature', '0,100', -1, self::EXISTS_VALIDATE, 'length'),
//         /* 验证昵称 */
//         array('nickname', 'checkNickname', -33, self::EXISTS_VALIDATE, 'callback'), //昵称长度不合法
//         array('nickname', 'checkDenyNickname', -31, self::EXISTS_VALIDATE, 'callback'), //昵称禁止注册
//         array('nickname', 'checkNickname', -32, self::EXISTS_VALIDATE, 'callback'),
//         array('nickname', '', -30, self::EXISTS_VALIDATE, 'unique'), //昵称被占用

//     );

//     protected $insertField = 'nickname,sex,birthday,qq,signature'; //新增数据时允许操作的字段
//     protected $updateField = 'nickname,sex,birthday,qq,signature,last_login_ip,login,update_time,last_login_role,show_role,status,tox_money,score,pos_province,pos_city,pos_district,pos_community'; //编辑数据时允许操作的字段

    //登陆状态
    public function Login($userid){
    	
    	session('login_userid', $userid);
    	header('Content-Type:application/json; charset=utf-8');
    	$data['status'] = 1;
    	$data['url'] = U('Ucenter/index/Center');
    	
    	if (IS_AJAX) {
    		exit(json_encode($data));
    	} else {
    		redirect($data['url']);
    	}
    }
    public function Register($mobile,$pwd,$nickname){
    	$data["Mobile"]=$mobile;
    	$data['pwd']=$this->encrypt($pwd);
    	$data['NickName']=$nickname;
    	$user=D()->table("tp_accounts_info")->where("Mobile='".$mobile."'")->find();
    	if($user){
    		$this->Login($user['UserID']);
    		return 1;
    	}else{
    		$r=D()->table("tp_accounts_info")->add($data);
    		
    		if($r){
    				$this->Login($r);return 1;
    			}else{
    				return 0;
    		}
    	}
    }
    //登陆验证
    public function checkLogin($username,$pwd){
    	
    	$map['Mobile']=$username;
    	$user=D()->table("tp_accounts_info")->where($map)->find();
    	if($user){
    		if($user['pwd']==$this->encrypt($pwd)){
    			return $user['UserID'];
    		}else{
    			return "-1";
    		}
    	}else{
    		return 0;
    	}
    }
    public function is_login(){
    	if(session('login_userid')){
    		return session('login_userid');
    	}else{
    		
    		return 0;
    		//redirect(U("ucenter/member/login"));
    	}
    }
   
    /**
     * 注销当前用户
     * @return void
     */
    public function logout()
    {
    	session('login_userid', null);
    }
    public function userinfo($uid){
    	$field="*";
    	$userinfo=D()->table("tp_accounts_info")->field($field)->where("UserID='".$uid."'")->find();
    	return $userinfo;
    }
	protected function encrypt($str,$key="joker"){
		return strtoupper(Md5($str.$key));	
	}
	
	//参数返回
	public function returnData($status,$msg="",$data=""){
		$ret['status']=$status;
		$ret['msg']=$msg;
		$ret['data']=$data;
		echo json_encode($ret);exit;
	}
	
	function toString($param){
	
		ksort($param);
		$tmpStr = '';
		foreach($param as $k=>$v){
			$tmpStr = $tmpStr.$k.'='.$v.'&';
		}
		$stringA = substr($tmpStr,0,-1);
	
		return $stringA;
	}
	
	function checkSign($param,$sign,$key="secret"){
		$str=$this->toString($param);
		//echo SHA1($str.$key);exit;
		if(SHA1($str.$key)==$sign){
			return true;
		}else{
			return false;
		}
	}
	
	public function getSignstr($param,$key='o5@Dpje@03VeaFyZGgc996kzTn%@MSct'){
		ksort($param);
		$tmpStr = '';
		foreach($param as $k=>$v){
			$tmpStr = $tmpStr.$k.'='.$v.'&';
		}
		$stringA = substr($tmpStr,0,-1);
		$sign =  $stringA.$key ;
		return $sign;
	}
	
	function request_post($url = '', $param = '') {
		if (empty($url) || empty($param)) {
			return false;
		}
	
		$postUrl = $url;
		$curlPost = $param;
		$ch = curl_init();//初始化curl
		curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		$data = curl_exec($ch);//运行curl
		curl_close($ch);
	
		return $data;
	}
}

<?php 
namespace Daili\Model;
use Think\Model;

class DailiModel extends Model{
	//密码加密
	public function encrypt($str,$key="joker"){
		return strtoupper(Md5($str.$key));	
	}
}
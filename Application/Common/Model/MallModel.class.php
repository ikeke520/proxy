<?php
namespace Common\Model;

use Think\Model;

class MallModel extends Model{

	private $dbconfig=array('DB_TYPE'   => 'mysql', // 数据库类型
			'DB_HOST'   => 'rm-wz97q89c7nx24vq4lo.mysql.rds.aliyuncs.com', // 服务器地址
			'DB_NAME'   => 'game', // 数据库名
			'DB_USER'   => 'mall', // 用户名
			'DB_PWD'    => 'Yuji1518GameCenter',  // 密码
			'DB_PORT'   => '3306', // 端口
			'DB_PREFIX' => '' // 数据库表前缀)
	);
	public $db_mall;
	
	public function __construct(){
		$this->db_mall=M("db_mall","",$this->dbconfig);
	}
}
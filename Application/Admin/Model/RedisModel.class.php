<?php 

namespace Admin\Model;

use Think\Model;

class RedisModel extends Model{
	
	private $server;
	private $port;
	private $auth;
	public $conn;
	public $db=2;
	public function __construct(){
		$this->server="120.78.53.104";
		$this->port="6379";
		$this->auth="yjredis!@#";
		$this->conn= new \Redis();
		$conn=$this->conn->connect($this->server, $this->port);
		if($conn){
			$this->conn->auth($this->auth);
			//$this->conn->select($this->db);
			return $this->conn;
		}else{
			echo "链接redis服务器有误";	
		}
	}
	public function getGameType($id,$field="name"){
		$this->conn->select(1);
		return $this->conn->hGet("server_".$id,$field);
	}
	public function select($res){
		$this->db=$res;
		return $this->conn->select($this->db);
	}
}
?>
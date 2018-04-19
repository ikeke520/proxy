<?php 

namespace Admin\Model;

use Think\Model;
use Admin\Model\RSAModel;

class WxPayToBankModel extends Model{
	
	public $url="https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank";
	public $rsa_url="https://fraud.mch.weixin.qq.com/risk/getpublickey";
	
	public $apiclient_cert="";
	private $apiclient_key="";
	private $mch_id="1423081102";
	private $key="yujiweixingzh8888888888888888888";
	
	public $error=array();
	
	public function __construct(){
		$this->apiclient_cert=MODULE_PATH."Conf/apiclient_cert.pem";
		$this->apiclient_key=MODULE_PATH."Conf/apiclient_key.pem";
	}
	//提现
	public function ToBank($id){
		if(!$id){
			$this->error=array(3003,"ID不存在");
			return false;
		}
		$map["a.id"]=$id;
		//获取客户信息
		$tixian_info=D()->table("tp_tixian_log a")->where($map)->find();
		//获取费率
		//$tixian_rate=D()->table("system_config")->where("name='tixian_rate'")->find();
		$tixian_rate="1.5";
		
		if(!$tixian_info){
			$this->error=array(3002,"提现记录不存在");
			return false;
		}
		if($tixian_info['status']!=0){
			$this->error=array(3004,"提现已执行");
			return false;
		}
		//生成订单号并插入提现记录中
		if(!$tixian_info['partner_trade_no']){
			$param['partner_trade_no']="tixian".time().$tixan_info['money'];
			D()->table("tp_tixian_log a")->where($map)->save($param);
		}else{
			$param['partner_trade_no']=$tixian_info['partner_trade_no'];
		}
		if(!$tixian_info['account_no']){
			$this->error=array(3006,"提现记录无银行卡号");
			return false;
		}
		if(!$tixian_info['bank_no']){
			$this->error=array(3007,"提现记录无银行编码");
			return false;
		}
		if(!$tixian_info['user_name']){
			$this->error=array(3008,"提现记录无用户姓名");
			return false;
		}
		if($tixian_rate['value']==""){
			$tixian_rate['value']=0;
		}
		$param['mch_id']=$this->mch_id;
		$param['nonce_str']=strtoupper(MD5(time()));
		$param['enc_bank_no']=$this->RSAencrypt($tixian_info['account_no']);
		$param['enc_true_name']=$this->RSAencrypt($tixian_info['user_name']);
		$param['bank_code']=$tixian_info['bank_no'];
		$param['amount']=$tixian_info['money']*(1-$tixian_rate['value']*0.01)*100;
		$param['desc']="娱记游戏提现";
		$param['sign']=$this->getSign($param);
		$xml=$this->toXml($param);
		$returnXML=$this->curl_post_ssl($this->url,$xml);
		$r=$this->ParseReturnInfo($returnXML);
		return $r;
	}
	//返回数据验证
	public function ParseReturnInfo($returnXML){
		$return_data=$this->parseXML($returnXML);
		if($return_data['return_code']!="SUCCESS"){
			$this->error=array(0,$return_data['return_msg'],array(),"3001");
			return false;
		}else{
			if($return_data['result_code']!="SUCCESS"){
				$this->error=array(0,$return_data['err_code_des'],array(),$return_data['err_code']);
				return false;
			}else{
				//修改提现记录状态
				$param['status']=1;
				//$param['partner_trade_no']=$return_data['partner_trade_no'];
				$param['amount']=$return_data['amount'];
				$param['payment_no']=$return_data['payment_no'];
				$param['cmms_amt']=$return_data['cmms_amt'];
				$param['type']=1;
				$map['partner_trade_no']=$return_data['partner_trade_no'];
				$r=D()->table("tp_tixian_log")->where($map)->save($param);
				if($r){
					//同时将冻结款项处理
					$map=array();
					$map['userid']=$tixian_log['userid'];
					$r=D()->table('tp_accounts_bank')->where($map)->setDec("freeze_cash",$tixian_log['money']);
					
					return true;
				}else{
					$this->error=array(3001,"提现记录修改错误");
					return false;
				}
			}
		}
	}
	//获取参数
	public function test(){
		$xml="<xml>
<return_code><![CDATA[SUCCESS]]></return_code>
<return_msg><![CDATA[支付成功]]></return_msg>
<result_code><![CDATA[SUCCESS]]></result_code>
<err_code><![CDATA[SUCCESS]]></err_code>
<err_code_des><![CDATA[微信侧受理成功]]></err_code_des>
<nonce_str><![CDATA[50780e0cca98c8c8e814883e5caa672e]]></nonce_str>
<mch_id><![CDATA[2302758702]]></mch_id>
<partner_trade_no><![CDATA[1212121221278]]></partner_trade_no>
<amount>500</amount>
<payment_no><![CDATA[10000600500852017030900000020006012]]></payment_no>
<cmms_amt>0</cmms_amt>
</xml> ";
		$r=$this->ParseReturnInfo($xml);
		print_r($this->error);
		print_r($r);exit;
	}
	//解析xml
	public static function parseXML($xmlSrc){
		if(empty($xmlSrc)){
			return false;
		}
		$array = array();
		$xml = simplexml_load_string($xmlSrc);
		
		if($xml && $xml->children()) {
			foreach ($xml->children() as $node){
				//有子节点
				if($node->children()) {
					$k = $node->getName();
					$nodeXml = $node->asXML();
					$v = substr($nodeXml, strlen($k)+2, strlen($nodeXml)-2*strlen($k)-5);
						
				} else {
					$k = $node->getName();
					$v = (string)$node;
				}
	
				if($encode!="" && $encode != "UTF-8") {
					$k = iconv("UTF-8", $encode, $k);
					$v = iconv("UTF-8", $encode, $v);
				}
				$array[$k] = $v;
			}
		}
		return $array;
	}
	//签名
	public function getSign($param,$key=''){
		if(!$key){
			$key=$this->key;
		}
		ksort($param);
        $tmpStr = '';
        foreach($param as $k=>$v){
            $tmpStr = $tmpStr.$k.'='.$v.'&';
        }
		$sign =  $tmpStr."key=".$key ;
		return strtoupper(MD5($sign));
	}
	//换成xml
 	public function toXml($array){
        $xml = '<xml>';
        forEach($array as $k=>$v){
            $xml.='<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
        }
        $xml.='</xml>';
        return $xml;
    }
	//rsa加密
	public function RSAencrypt($rst){
		$rsa = new RSAModel(getcwd()."\Application\Admin\Conf\publickey.pem", "");
		//$rst=json_encode($rst);
		$ret_e = $rsa->encrypt($rst);
		return $ret_e;
	}
	//获取公钥
	public function getPublicKey(){
		$param['mch_id']=$this->mch_id;
		$param['nonce_str']=strtoupper(MD5(time()));
		$param['sign']=$this->getSign($param);
		$xml=$this->toXml($param);
		//echo $xml;exit;
		$r=$this->curl_post_ssl($this->rsa_url,$xml);
		echo $r;
	}
	
	//换成xml
	/*
	 请确保您的libcurl版本是否支持双向认证，版本高于7.20.1
	 */
	public function curl_post_ssl($url, $vars, $second=60,$aHeader=array())
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
	
		//以下两种方式需选择一种
	
		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'\Application\Admin\Conf\apiclient_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'\Application\Admin\Conf\apiclient_key.pem');
		
		//第二种方式，两个文件合成一个.pem文件
		//curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'\Admin\Conf\all.pem');
		
		if( count($aHeader) >= 1 ){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}
	
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
		$data = curl_exec($ch);
		
		if($data){
			curl_close($ch);
			return $data;
		}else {
			$error = curl_errno($ch);
			echo "call faild, errorCode:$error\n";
			curl_close($ch);
			return false;
		}
	}
}
?>
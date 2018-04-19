<?php

namespace Admin\Model;
use Think\Model;
  /*
    Author:yf
    使用说明:微信公众号无限群发接口，使用实例:   
    $test = new SendAllMsg("你的appId","你的appSecret");
    $test->sendMsgToAll(); //调用群发方法
    注：1.使用条件：认证号或测试号
      2.群发消息内容可为图文、文本、音乐等,$data具体内容参照微信开发文档/客服接口
      3.若用户量过万,需修改getUserInfo()，具体参照信开发文档/获取关注者列表
        
    新手上路，大神们多多指点，谢谢
  */
/*   interface iSendAllMsg{
    function getData($url); //curl 发送get请求
    function postData($url,$data); //curl 发送post请求
    function getAccessToken();  //在构造方法中已调用该方法来获取access_token,注意它在wx服务器的保存时间7200s
    function sendMsgToAll($data); //群发消息方法,发送的消息$data 可自行修改
  } */
  class SendAllMsgModel extends Model{
    private $appId; 
    private $appSecret;
    private $access_token;
    //
    public function __construct() {
	  $config = M('Config')->getField('name,value');	
      //$this->appId = "wx1040c62d14e0868f";
      //$this->appSecret ="51710e0a7abd047ba80296bb1abc6136";
	  $this->appId=$config['WECHAT_APPID'];
	  $this->appSecret=$config['WECHAT_SECRETKEY'];
	  $touser=$config['WECHAT_MESSAGETO'];
      $this->access_token = $this->getAccessToken();
    }
    //
    function getData($url){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
      curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
    }
    //
    function postData($url,$data){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $tmpInfo = curl_exec($ch);
      if (curl_errno($ch)) {
        return curl_error($ch);
      }
      curl_close($ch);
      return $tmpInfo;
    }
    //
    function getAccessToken(){
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=".$this->appSecret;
      $res = $this->getData($url);
      $jres = json_decode($res,true);
      $access_token = $jres['access_token'];
      return $access_token;
    }
    //
    public function getUserInfo(){
      $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->access_token;
      $res = $this->getData($url);
      $jres = json_decode($res,true);
      //print_r($jres);
      $userInfoList = $jres['data']['openid'];
      return $userInfoList;
    }
    function sendMsgToAll($content){
      $userInfoList = $this->getUserInfo();
      //print_r($userInfoList);exit;
	  	  /* $content="你的早餐，我的用心！
周三套餐：
碱水粽+银耳羹
咸蛋黄红烧肉棕+银耳羹
蒸饺+银耳羹

健康美食，由此开始！"; */
      $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$this->access_token;
	  $i=0;
       foreach($userInfoList as $val){
        $data = '{
              "touser":"'.$val.'",
              "msgtype":"text",
              "text":
              {
                "content":"'.$content.'"
              }
            }';
        $r=$this->postData($url,$data);
		$i++;
      }
	 /*  $data['touser']=$userInfoList;
	  $data['msgtype']='text';
	  
	  $content=urlencode($content);
	  $content="你的早餐，我的用心！
周三套餐：
碱水粽+银耳羹
咸蛋黄红烧肉棕+银耳羹
蒸饺+银耳羹

健康美食，由此开始！";
	  $data["text"]=array('content'=>$content);
	  $data=json_encode($data);
	  $data=urldecode($data);
	  print_r($data);exit;
	  $r=$this->postData($url,$data); */
	  return $i;
    }
	public function preview($content,$touser=""){
		
		$url="https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=".$this->access_token;
		$data['touser']=$touser;
		$data['msgtype']='text';
		
		$content=urlencode($content);
		$data["text"]=array('content'=>$content);
		
		$data=json_encode($data);
		 $data=urldecode($data);
		$r=$this->postData($url,$data);
		return $r;
	}
	/* function sendTWMstToAll(){
		$url="https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=".$this->access_token;
		$data['thumb_media_id']="";
		
		
	} */
	/* function getHangye(){
		$url="https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=".$this->access_token;
		$data="{
           'template_id_short':'TM00015'
		}";
		$r=$this->postData($url,$data);
		return $r;
	} */
	function modelpreview($touser,$data,$modelid=""){
		$url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->access_token;
		//$modelid="IrAgMP_-2g11og1giU1yvMjK5aLHBa-oLpwc7B0HXlk";
		
		$info='{
           "touser":"'.$touser.'",
           "template_id":"'.$modelid.'",
           "url":"'.$data['url'].'",            
           "data":{
                   "first": {
                       "value":"'.$data['first'].'",
                       "color":"#173177"
                   },
                   "keyword1":{
                       "value":"'.$data['keyword1'].'",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"'.$data['keyword2'].'",
                       "color":"#173177"
                   },
				   "keyword3": {
                       "value":"'.$data['keyword3'].'",
                       "color":"#173177"
                   },
				   "keyword4": {
                       "value":"'.$data['keyword4'].'",
                       "color":"#173177"
                   },
				   "keyword5": {
                       "value":"'.$data['keyword5'].'",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"'.$data['remark'].'",
                       "color":"#173177"
                   }
           }
       }';
	   $r=$this->postData($url,$info);
	   return $r;
	}
	function sendModel($info){
		
		
		$url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->access_token;
		$modelid="hQtlpReHQ47o3AxRnvvkKlnO-xv23TGI1r8qwIKjb5Q";
		
		$userInfoList = $this->getUserInfo();
		//$userInfoList=array('otFBHwy2-6ag9qe9S4Jl6wiFpLhk','otFBHwy2-6ag9qe9S4Jl6wiFpLhk');
		$i=0;
		foreach($userInfoList as $k =>$v){
		$data='{
           "touser":"'.$v.'",
           "template_id":"'.$modelid.'",
           "url":"'.$info['url'].'",            
           "data":{
                   "first": {
                       "value":"'.$info['first'].'",
                       "color":"#173177"
                   },
                   "keyword1":{
                       "value":"'.$info['keyword1'].'",
                       "color":"#173177"
                   },
                   "keyword2": {
                       "value":"'.$info['keyword2'].'",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"'.$info['remark'].'",
                       "color":"#173177"
                   }
           }
       }';
	   $r=$this->postData($url,$data);
	   $i++;
		}
	   return $i;
	}
  }
  
//$wxsend = new SendAllMsg();
/* $userInfoList=$wxsend->getUserInfo();
print_r($userInfoList);
		$name=time().".txt";
		$file=fopen("1.txt",'w');
		fwrite($file,$userInfoList);
		fclose($file); */
 //$wxsend->sendMsgToall('fdfdsafd');
  //$r=$wxsend->preview();
 // $r=$wxsend->modelpreview();
  //print_r($r);exit;
?>

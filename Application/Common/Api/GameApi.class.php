<?php

namespace Common\Api;
/*
 * ���֧���ӽ�����
 * author:wangbingxuan
 * date:2017-04-12
 * function���������֧��
 * */
class GameApi {
    private static $_instanceObj; //����ʵ������
    private $secret = 'secret';
    private  $publicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCBVO7dy3skYxTzFPMxpeZl6pSs
RmgTWTCG+V6WbvVaDdusghsS2CK1lp9hzybJVa4EOgpWJKoV32ci22RV7M8grJGi
e0ZjgP+PxbapWAMHHswfaiboquD410npYpJfxqYVrF7CM9CM/A+CQK3CCrjvHszc
4vS6bjdFOfg3hzCjKwIDAQAB
-----END PUBLIC KEY-----';
    private  $privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgQCBVO7dy3skYxTzFPMxpeZl6pSsRmgTWTCG+V6WbvVaDdusghsS
2CK1lp9hzybJVa4EOgpWJKoV32ci22RV7M8grJGie0ZjgP+PxbapWAMHHswfaibo
quD410npYpJfxqYVrF7CM9CM/A+CQK3CCrjvHszc4vS6bjdFOfg3hzCjKwIDAQAB
AoGAWecliM5rEpdBt9xnVariZxRTNxJWRKTqQ9lTNDV7npcljlx+33GZZyuGLaKn
bqt9pPiHuOwRw9ShSCzEKarNLAU/VdicMAZ/p2wTrO4v+ZMBPBIpmmzYELqaFpzx
5ci+C/aOwUffLoMoIy5CTzwBJ44SNIP/wxEDccZUlX1NY1kCQQDPiJtA7s/1KxzI
k9BvUl7lPoAgu5EnknRrD+PBbakygaeB2/EaX+7PZKURIXVDpnG3SsyOTS9IjQf+
C7vrJ0uPAkEAn4kH0AecEo+SzSbuuVYi+UIDfoBZZ3RcOsK7yo4WphpHIXoI4QTq
Ycn58umlQJUf/50RJ9PVszlxiNP0mZwQpQJADodVtxoA3P0DqtcORHzTv+C8P45h
/w81rzkRjL0Ml0iUXXb0ThBhO4ukMqrQ9sd0Noy5/UxR/xZAdPyFT1UU6QJAV39o
cYg66VMlSK9Zrvy3Ic467F6oqjz/eJrlNCrJ8T/oU0wDIqb3hbqpA7lxkQI5EpCq
oBVm121h/5GATd9yoQJAEVGA/YDkbnlJ42PDzqBnB2mAhjXfo1YxXmEmNzjaCDmO
4k42TCZDl7CGoZdGcr5eJW7E953R9MiyhesPl5KX3A==
-----END RSA PRIVATE KEY-----';
    //private  $appId = 3010380243;
    //private  $waresId = 1;//Ӧ�ô����͵���Ʒ ����1
    private  $orderUrl = 'pay.gagooo.com/index.php?s=gamepay/paynow';//�µ��ӿ�
    private function __construct(){

    }

    //˽�У���ֹ�ⲿ��¡
    private function __clone(){

    }

    //ʵ��������Ĺ�����̬����
    public static function get_instance(){
        if (!(self::$_instanceObj instanceof self)) {
            self::$_instanceObj = new self;
        }

        return self::$_instanceObj;
    }
    /*
    * author:wangbingxuan
    * date:2017-04-14
    * function�������ַ�������
    * */
//    public function key_sort( $data )
//    {
//        function k_sort( $a , $b ){
//            if ( $a == $b ) return 0 ;
//            return ( $a > $b ) ? -1 : 1 ;
//        }
//        if( is_array($data) ){
//            uksort( $data , "k_sort" );
//            $str='';
//            foreach( $data as $k => $v ){
//                $str = $str .'&'. $k . '=' . $v ;
//            }
//            $str = substr_replace( $str ,'',0,1 ) ;
//            framework_static_function::write_log('�������' . $str , 'key_sort', 'log' , 'lib_yuji' );
//            return $str;
//        }
//        //framework_static_function::write_log('�������' . $data , 'key_sort', 'log' , 'lib_yuji' );
//        return false;
//    }


//����
    public function encrypt($param){
        ksort($param);
        $tmpStr = '';
        foreach($param as $k=>$v){
            $tmpStr = $tmpStr.$k.'='.$v.'&';
        }
        $stringA = substr($tmpStr,0,-1);
        framework_static_function::write_log('δ���ܲ���ƴ��:'.$stringA,'yuji','log','lib_yuji');
        $maxlength = 117;
        $strSign = '';
        while($stringA){
            $input = substr($stringA,0,$maxlength);
            $stringA = substr($stringA,$maxlength);
            openssl_private_encrypt($input,$result,$private_key);
            $strSign.=$result;
        }
        //����base64
        $strSign = base64_encode($strSign);
        framework_static_function::write_log('base64���ܲ���:'.$stringA,'yuji','log','lib_yuji');

        $sign =  $strSign . 'secret' ;
        $sign = hash( 'sha256' , $sign );
        return array(
            'result' => $strSign,
            'sign'   => $sign
        );
    }
    //����
    public function decrypt($data)
    {
        $data = base64_decode($data);
        $maxlength = 128;
        $string = '';
        while($data){
            $input = substr($data,0,$maxlength);
            $data = substr($data,$maxlength);
            openssl_public_decrypt($input,$decryptData,$private_key);
            $string .= $decryptData;
        }
        framework_static_function::write_log('���ܲ������ַ���:'.$stringA,'yuji','log','lib_yuji');
        return $string;
    }

    public function decryptData($data){
        $decryptData = $this->decrypt($data);
        $array = explode('&',$decryptData);
        $res = array();
        foreach($array as $val){
            $value = explode('=',$val);
            $res[$value[0]] = $value[1];
        }
        framework_static_function::write_log('��ν��ܺ���������:'.json_encode($res),'log','lib_yuji');
        return $res;
    }
    /*
     * �Ե�����������ǩ��
     * �������ĺ�ƴ��key����sha256����ǩ
     */
    public function verifySign($para){
        $result = $para['result'];
        $sign = $para['sign'];
        $decryptData = $this->decrypt($result);
        $mySignStr = hash('sha256',$decryptData.'secret');
        framework_static_function::write_log('��ν��ܺ���������:'.json_encode($res),'fanliwang');

        if(($mySignStr == $sign) && !empty($mySignStr)){
            return true;
        }
        return false;

    }

    /**
     * curl��ʽ����post����
     * $remoteServer �����ַ
     * $postData post��������
     * $userAgent�û�����
     * return ���ر���
     */
    function request_by_curl($remoteServer, $postData, $userAgent) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remoteServer);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        $data = urldecode(curl_exec($ch));
        curl_close($ch);
        return $data;
    }

    function createOrder($cpData) {
        if(empty($cpData['orderId']) || empty($cpData['pay_amount']) || empty($cpData['user_id']) || empty($cpData['userAgent']) ){
            return '';
        }

        $orderReq['cporderid'] = $cpData['orderId']; //�ҷ�������
        $orderReq['price'] = $cpData['pay_amount'];//��λԪ
        $orderReq['currency'] = 'RMB';
        $orderReq['notifyurl'] = SITE_URL_HTTP.'yujipay_notify.php'; //�첽�ص���ַ����ȷ�ϣ�

        //��װ������  ������ǩ��
        $reqData = $this->encrypt($orderReq);
        return $repDate;
        //��ǩ���ݲ��ҽ������ر���
//        if(!$this->parseResp($respData,$respJson)) {
//            return '';
//        }else{
//
//            error_log('transid: '.$respJson->transid."\r\n",3,'/tmp/sczh.log');
//            return $respJson->transid;
//        }
//


    }
    /**
     * Request ����ӿ�
     *
     * �����ļ���Ҫʹ�� $method = 'POST' �� ͬʱָ�� $multi = array('{fieldname}'=>'/path/to/file)
     *
     * @param string $url
     * @param array $params
     * @param string $method ֧�� GET / POST / DELETE
     * @param false|array $multi false:��ͨpost array: array ( '{fieldname}'=>'/path/to/file' ) �ļ��ϴ�
     * @return string
     */
    private function request( $url , $params , $method='GET' , $multi=false ,$extheaders=array())
    {
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ci, CURLOPT_TIMEOUT, 3);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ci, CURLOPT_HEADER, false);

        $headers = (array)$extheaders;
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params)) {
                    if ($multi) {
                        foreach($multi as $key => $file) {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    } else {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params)) {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if ($headers) {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }
        $response = curl_exec($ci);
        curl_close ($ci);
        return $response;
    }
}





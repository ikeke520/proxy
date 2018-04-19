<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminSortBuilder;
use Admin\Model\SendAllMsg;
/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class WechatController extends AdminController
{
    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index()
    {
    	 
	  	 $this->display();
    }
	public function model(){
		 //获取模板信息
		$modellist=M('wechat_mmg')->select();
		$this->assign('modellist',$modellist);
		$this->display();
	}
    public function wxTxtPreview(){
    	//require_once 'wxmessage.class.php';
    	$content=I('content');
    	
    	if(!$content){
    		echo "别逗我，不要发送空白信息好么！！";
    	}
		$config = M('Config')->getField('name,value');
		$touser=$config['WECHAT_MESSAGETO'];
		
    	$r=D('SendAllMsg')->preview($content,$touser);
		//print_r($r);exit;
    	if($r){
    		echo "发送成功";
    	}else{
    		echo "抱歉！".$r;
    	}
    }
	
    public function wxTxtSend(){
    	//require_once 'wxmessage.class.php';
    	$content=I('content');
    	
    	if(!$content){
    		$this->error("别逗我，不要发送空白信息好么！！");exit;
    	}
    	$r=D('SendAllMsg')->sendMsgToAll($content);
    	if($r){
    		$this->success("发送成功".$r."个对象");exit;
    	}else{
    		$this->error("很抱歉，我们没有连上对方WIFI！");exit;
    	}
    }
	
	public function wxModelPreview(){
		
		//require_once 'wxmessage.class.php';
		$modelid=I('modelid');
		if(!$modelid){
		   $this->error("请选择好模板ID");exit;
    	}
		
    	$data['first']=I('first');
		$data['keyword1']=I('keyword1');
		$data['keyword2']=I('keyword2');
		$data['keyword3']=I('keyword3');
		$data['keyword4']=I('keyword4');
		$data['keyword5']=I('keyword5');
		$data['remark']=I('remark');
		$data['url']=I('url');
		
		$config = M('Config')->getField('name,value');
		$touser=$config['WECHAT_MESSAGETO'];
		
		if(!$touser){
			echo "请去系统配置里设置【测试人】这个参数";exit;
		}
		
		if(!$data['keyword1']||!$data['keyword2']||!$data['first']||!$data['remark']||!$data['url']){
    		echo "请不要发送空白信息好么！！";exit;
    	}
    	$r=D('SendAllMsg')->modelpreview($touser,$data,$modelid);
		//print_r($r);exit;
    	if($r){
    		echo "发送成功";exit;
    	}else{
    		echo "很抱歉，发送失败";exit;
    	}
	}
	public function wxModelSend(){

		//require_once 'wxmessage.class.php';
		$modelid=I('modelid');
    	$data['first']=I('first');
		$data['keyword1']=I('keyword1');
		$data['keyword2']=I('keyword2');
		$data['keyword3']=I('keyword3');
		$data['keyword4']=I('keyword4');
		$data['keyword5']=I('keyword5');
		$data['remark']=I('remark');
		$data['url']=I('url');
    	
    	if(!$data['keyword1']||!$data['keyword2']||!$data['first']||!$data['remark']||!$data['url']){
    		$this->error("请不要发送空白信息好么！！");exit;
    	}
    	$r=D('SendAllMsg')->sendModel($data,$modelid);
    	if($r){
    		$this->success("发送成功".$r."个对象");exit;
    	}else{
			$this->error("很抱歉，发送失败！");exit;
    	}
	}
	function config($page = 1, $r = 20){
		
		$list=M('wechat_mmg')->where($map)->page($page, $r)->select();
		
		$totalCount = M('wechat_mmg')->where($map)->count();
		$builder = new AdminListBuilder();
        $builder->title("微信模板列表");
        $builder->meta_title = '微信模板列表';
        //$builder->setSearchPostUrl(U('Admin/User/expandinfo_select'),'','')->search('搜索', 'nickname', 'text', '请输入用户昵称或者ID');
        $builder->buttonNew(U('newModel'));
		$builder->keyId()->keyText('name', "模板名称");
        $builder->keyText('modelid','模板ID', '');
		$builder->keyText('keywordnum','模板字段数')->keyDoAction('newModel?id=###', '编辑')->keyDoAction('delete?id=###', '删除');
        $builder->data($list);
        $builder->pagination($totalCount, $r);
        $builder->display();  
		
	}
	function newModel(){
		if (IS_POST) {
			$id=I('id');
			$param['name']=I('name');
			$param['modelid']=I('modelid');
			$param['keywordnum']=I('keywordnum');
			if(!$id){
			 $res=M('wechat_mmg')->add($param);
			}else{
			 $res = M('wechat_mmg')->where('id=' . $id)->save($param);	
			}
			if ($res) {
                $this->success($id == '' ? "添加分组成功" : "编辑分组成功", U('config'));
            } else {
                $this->error($id == '' ? "添加分组失败" : "编辑分组失败");
            }
		}else{
			
			$id=I('id');
			$builder = new AdminConfigBuilder();
			$builder->title("微信模板添加");
			$builder->meta_title = '微信模板添加';
		
			$builder->keyReadOnly("id", "标识")->keyText('name', '模板名称')->keyText('modelid', '模板ID','微信公众号模板ID')->keyText('keywordnum', '模板字段数');
			
			if($id){
				$map['id']=$id;
				$data=M('wechat_mmg')->where($map)->find();
				$builder->data($data);
			}
			$builder->buttonSubmit(U('newModel'), $id == 0 ? "添加" : "修改")->buttonBack();
		
			$builder->display();
		}
	}
	
	function delete(){
		$id=I('id');
		$map['id']=$id;
		$res=M('wechat_mmg')->where($map)->delete();
			if ($res) {
                $this->success($id == '' ? "删除成功" : "删除成功", U('config'));
            } else {
                $this->error($id == '' ? "删除失败" : "删除失败");
            }
	}
	
	
}

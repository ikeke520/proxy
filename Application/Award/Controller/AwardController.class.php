<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Common\Model\MallModel;

/**
 *   转盘控制器
 */
class AwardController extends AdminController
{
    //奖品池
    public function index($page=1,$r=20)
    {
    	//奖品池
    	//$map[]="";
    	$list=D()->table("zhuanpan_award")->where()->page($page,$r)->order("create_time desc")->select();
    	$mall=D('Common/Mall');
    	foreach($list as $k =>$v){
    		$map['id']=$v['product_id'];
			$product=$mall->db_mall->table("mall_product")->where($map)->find();
			$list[$k]['pname']=$product['pname'];
			$list[$k]['sale_price']=$product['sale_price'];
    	}
    	$builder=new AdminListBuilder();
    	$builder->title("奖品池");
    	$builder->buttonNew(U('awardDo'),"新增");
    	$builder->keyId()->keyText("product_id","商品ID")->keyText("pname","商品名称")->keyText("sale_price","商品售价")->keyText("source","平台")->keyText("rate","中奖利率%")
    	->keyText("repertory","库存")->keyText("threshold","阈值(每天超过就不再中了)")->keyText("people_day_award_num","每人每天最多中奖次数")->keyDoAction('awardDo?id=###', "修改")->keyDoAction('awardDelete?id=###', "删除");
    	$builder->data($list);
     	$builder->display();
    }
    //新增 修改  奖品
    public function awardDo(){
    	$id=I("id");
    	$map['id']=$id;
    	$list=D()->table("zhuanpan_award")->where($map)->find();
    	$data=I('post.');
    	if(IS_POST){
    		$mall=D('Common/Mall');
    		$map1['id']=$data['product_id'];
    		$product=$mall->db_mall->table("mall_product")->where($map1)->find();
    		if(!$product){
    			$this->error("商城无该商品");
    		}
    		if($id){
				//修改
				unset($data['id']);
    			$r=D()->table("zhuanpan_award")->where($map)->save($data);
    			
    		}else{
    			//新增
    			$r=D()->table("zhuanpan_award")->where($map)->add($data);
    		}
    		if($r){
    			$this->success("保存成功");
    		}else{
    			$this->error("保存失败");
    		}
    	}else{
    		$builder=new AdminConfigBuilder();
    		$builder->title("新增奖品");
    		$builder->keyId()->keyText("product_id","商品ID")->keyText("source","平台")->keyText("rate","中奖利率%")
    		->keyText("repertory","库存")->keyTime("dead_time","有限期限","不填无限制","date")->keyText("threshold","阈值(每天超过就不再中了)")
    		->keyText("people_day_award_num","每人每天最多中奖次数");
    		$builder->data($list);
    		$builder->buttonSubmit(U("awardDo"),"保存");
    		$builder->display();
    	}
    }
    //删除奖品
    public function awardDelete(){
    	$map['id']=I('id');
    	$r=D()->table("zhuanpan_award")->where($map)->delete();
    	if($r){
    		$this->success("操作成功");
    	}else{
    		$this->error("操作失败");
    	}
    }
    //用户奖品列表
    public function userAward($page=1,$r=20){
    	
    	$list=D()->table("accounts_award a")->join(" __ZHUANPAN_AWARD__ b on a.awardid=b.id","left","")->field("a.*,b.source")->where($map)->page($page,$r)->order("start_time desc")->select();
    	$count=D()->table("accounts_award a")->join(" __ZHUANPAN_AWARD__ b on a.awardid=b.id","left","")->field("a.*,b.source")->where($map)->count();
    	$mall=D('Common/Mall');
    	foreach($list as $k=>$v){	
    		$map1['id']=$v['goodsid'];
    		$product=$mall->db_mall->table("mall_product")->where($map1)->find();
    		$list[$k]['pname']=$product['pname'];
    		$list[$k]['sale_price']=$product['sale_price'];
    		
    	}
    	$builder=new AdminListBuilder();
    	$builder->title("中奖记录");
    	//$builder->buttonNew(U('awardDo'),"新增");
    	$builder->keyId()->keyText("goodsid","商品ID")->keyText("pname","商品名称")->keyText("sale_price","商品售价")->keyText("source","平台")->keyText("unionid","中奖者id")
    	->keyTime("start_time","中奖时间")->
    	keyStatusDiy("status","状态",array(-1=>"未激活",0=>"待兑换",1=>"已兑换",-2=>"失效"))->keyDoAction('userawardStatus?id=###', "承兑")->keyDoAction('userawardDel?id=###', "删除");
    	$builder->data($list);
    	$builder->pagination($count,$r);
    	$builder->display();
    }
    //承兑
    public function userawardStatus(){
		$map['id']=I('id');
		$r=D()->table("accounts_award")->where($map)->save(array('status'=>1));
		if($r){
			$this->success("成功");
		}
		$this->error("失败");
    }
    //删除奖项
    public function userawardDel(){
    	$map['id']=I('id');
    	$r=D()->table("accounts_award")->where($map)->delete();
    	if($r){
    		$this->success("删除成功");
    	}
    	$this->error("删除失败");
    }
    
}
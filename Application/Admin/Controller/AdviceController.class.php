<?
namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminSortBuilder;

class AdviceController extends AdminController
{
    public function index($page = 1, $r = 20)
    {
        //$map['status'] = array('egt', 0);
        $profileList = D('advice')->where($map)->order("createtime desc")->page($page, $r)->select();
        $totalCount = D('advice')->where($map)->count();
        $builder = new AdminListBuilder();
		$builder->title("意见反馈列表");
        $builder->meta_title = '意见反馈列表';
        $builder->buttonNew(U('editProfile', array('id' => '0')))->buttonDelete(U('changeProfileStatus', array('status' => '-1')));
        $builder->keyId()->keyText('userid', "游戏ID")->keyText('phone', '电话号码')->keyText('weixin','微信号')->keyHtml('advice','建议')->
		keyTime("createtime", "创建时间");
        $builder->keyStatus()->keyDoAction('Proxy/sub?id=###', '解决');
        $builder->data($profileList);
        $builder->pagination($totalCount, $r);
        $builder->display();
		
    }
	
	 public function changsheng($page = 1, $r = 20)
    {
        //$map['status'] = array('egt', 0);
        $profileList = D('csadvice')->order("createtime desc")->page($page, $r)->select();
		//print_r($profileList);exit;
        $totalCount = D('csadvice')->where($map)->count();
        $builder = new AdminListBuilder();
		$builder->title("意见反馈列表");
        $builder->meta_title = '意见反馈列表';
        //$builder->buttonNew(U('editProfile', array('id' => '0')))->buttonDelete(U('changeProfileStatus', array('status' => '-1')));
        $builder->keyId()->keyText('userid', "游戏ID")->keyText('phone', '电话号码')->keyText('weixin','微信号')->keyHtml('advice','建议')->
		keyTime("createtime", "创建时间");
        //$builder->keyStatus()->keyDoAction('Proxy/sub?id=###', '解决');
        $builder->data($profileList);
        $builder->pagination($totalCount, $r);
        $builder->display();
		
    }
	
}
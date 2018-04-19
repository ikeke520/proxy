<?

//常胜代理申请管理


namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminSortBuilder;

class ProxyCsController extends AdminController
{
    public function index($page = 1, $r = 20)
    {
        //$map['status'] = array('egt', 0);
        $profileList = D('dailics')->where($map)->order("time desc")->page($page, $r)->select();
        $totalCount = D('dailics')->where($map)->count();
        $builder = new AdminListBuilder();
		$builder->title("代理申请人列表");
        $builder->meta_title = '代理申请人列表';
        $builder->buttonNew(U('editProfile', array('id' => '0')))->buttonDelete(U('del'));
        $builder->keyId()->keyText('name', "代理人名称")->keyText('phone', '电话号码')->keyText('weixin','微信号')->keyText('areaid','地区')->
		keyText('type','游戏类')->keyTime("time", "创建时间");
        $builder->keyStatus()->keyDoAction('sub?id=###', '审核确认')->keyDoAction('del?id=###', '删除');
        $builder->data($profileList);
        $builder->pagination($totalCount, $r);
        $builder->display();
		
    }
	
	public function del(){
			$id=(int)I('id');
			$r=D('dailics')->delete($id);
			if($r){
				$this->success("删除成功");
			}else{
				$this->error("删除失败");
			}
	}
	public function sub(){
		$id=(int)I('id');
		//$data['id']=$id;
		$data['status']=1;
		$r=D('dailics')->where("id='".$id."'")->save($data);
			if($r){
				$this->success("审核通过");
			}else{
				$this->error("审核失败");
			}
	}
}
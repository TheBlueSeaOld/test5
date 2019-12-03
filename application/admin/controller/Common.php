<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;

class Common extends Controller{
	//保存nav数据信息
	protected $nav=array();
	public function _initialize(){
		//验证登陆状态
		if(!session('name') || !session('id')){
			exit(alert_href('请先登录',url('admin/log/index')));
		}
		//判断登陆角色并获取角色权限
		$id=session('id');
		if(session('name')=='admin'){
			$nav_data=Db::query("select * from role_admin ra left join pri_role pr on pr.role_id=ra.role_id");
		}else{
			$nav_data=Db::query("select * from role_admin ra left join pri_role pr on pr.role_id=ra.role_id where ra.admin_id=".$id);
		}
		$pri_data=array();
		foreach($nav_data as $v){
			$pri_data[]=$v['pri_id'];
		}
		//制作nav
		$this->nav($pri_data);
	}
	public function nav($pri_data){
		$result=db('privilege')->where('id','in',$pri_data)->select();
		$data=nav_leval($result);
//		var_dump($data);die;
		session('nav',$data);
	}


}



?>
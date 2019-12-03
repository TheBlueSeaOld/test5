<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\admin\controller\Page;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Privilege;
use app\admin\model\Role;
use think\Db;

/* 
* 管理员操作类
 */

class Admin extends Common{
	/* 管理员列表 */
	public function admin_list(){
		//获取管理员与角色组装数据总条数
		$userMsg=Db::query("select * from admin a left join role_admin ra on a.id=ra.admin_id left join role r on r.id=ra.role_id");
		$page=new Page(count($userMsg),5);
		$limit=$page->limit();
		
		//获取管理员与角色分页数据,这里使用了group by与group_concat组合
		$data=Db::query("select a.*,group_concat(r.role_name) from admin a left join role_admin ra on a.id=ra.admin_id left join role r on r.id=ra.role_id group by a.id limit ".$limit);			
//		var_dump($data[1]['group_concat(r.role_name)']);die;
		$count=db('admin')->count();
		$this->assign('count',5);				//每页显示条数
		$this->assign('page',$page->show(3));	//分页显示  在Page类中实装
		$this->assign('userMsg',$data);
		return $this->fetch();
	}
	
	/* 添加管理员 */
	public function admin_add(){
		//角色信息
		$role_data=db('role')->select();
		$this->assign('role_data',$role_data);
		return $this->fetch();
	}
	
	/* 管理员编辑 */
	public function admin_update(){
		if(!@request()->param('id')){
			return $this->error("未知操作");
		}
		//获取角色信息
		$role_data=db('role')->select();
		$this->assign('role_data',$role_data);
		//获取管理员数据
		$userMsg=db('admin')->where('id','=',request()->param('id'))->find();
		$this->assign('u',$userMsg);
		return $this->fetch();
	}

	/* 修改管理员开关按钮 */
	public function statusChange(){
		if(@request()->param('id') && @request()->param('status')!==''){
			if(request()->param('id')==1 || session('name')!=='admin'){
				$this->error("power failed : Only supperMan can update this users");
			}
			db('admin')->where('id','=',request()->param('id'))->update(array('status'=>request()->param('status')));
			$this->redirect("admin/admin_list");
		}else{
			return $this->error("installed status failed");
		}
		
	}
	
	/* 管理员删除 */
	public function admin_del(){
		if(!@request()->param('id')){
			return $this->error("installed status failed");
		}
		if(request()->param('id')==1){
			$this->error("power failed : The supperman cat't delete");
		}
		db('admin')->where('id','=',request()->param('id'))->delete();
		db('role_admin')->where('admin_id','=',request()->param('id'))->delete();
		return $this->redirect("admin/admin_list");
	}
	
	/* 管理员添加与提交地址 */
	public function admin_log(){
		$admin=new AdminModel();
		$data=array();
		$data['name']=request()->param('name');
		$data['password']=request()->param('password');
		$data['logTime']=time();
		$data['email']=request()->param('email');
		if(@request()->param(id)){
			//这里是修改操作
			$data['id']=request()->param('id');
			$result=$admin->validate(true)->update($data);
			if(!$result){
				alert($admin->getError());
			}else{
				alert("添加成功");
			}
		}else{
			//这是里新增操作
			$result=$admin->validate(true)->save($data);
			if(!$result){
				alert($admin->getError());
			}else{
				//alert("添加成功");
			}
		}
		//获取用户ID
		$data=db('admin')->where('name','=',request()->param('name'))->find();
		$id=$data['id'];
		//删除原用户角色
		$role_del=db('role_admin')->where('admin_id','=',$id)->delete();
		//重组角色与用户数组
		
		foreach($_POST['role'] as $v){
			$role_arr=array();
			$role_arr['admin_id']=$id;
			$role_arr['role_id']=$v;
			$end=db('role_admin')->insert($role_arr);
			if(!$end){
				$this->error("false");
			}
		}
		return $this->success('admin/admin_list');
	}

	/** 权限列表 
	 * private 
	**/
	public function privilege_list(){
		$msg=db('privilege')->select();
		$msg=leval_list($msg);
		$this->assign('msg',$msg);
		return $this->fetch();
	}
	
	/* 权限添加 */
	public function privilege_add(){
		if(@request()->param('id') && !@request()->param('leval')){
			$id=request()->param('id');			
			$data=db('privilege')->where('id','=',request()->param('id'))->select();
			$msg=array(
				'id'=>$data[0]['id'],
				'pri_name'=>$data[0]['pri_name'],
				'node'=>$data[0]['node'],
				'descs'=>$data[0]['descs'],
				'parent_id'=>$data[0]['id'],
				'leval'=>$data[0]['leval']
			);
			$this->assign('msg',$msg);
		}elseif(@request()->param('id') && @request()->param('leval')){
			$data=db('privilege')->where('id','=',request()->param('id'))->select();
			$msg=array(
				'parent_id'=>$data[0]['id'],
				'leval'=>$data[0]['leval']+1
			);
			if($msg['leval']>3){
				$this->error("无法继续添加子分类");
			}
			$this->assign('msg',$msg);
		}else{
			$msg['leval']=1;
			$msg['parent_id']=0;
			$this->assign('msg',$msg);
		}
		return $this->fetch();
	}
	
	/* 权限提交 */
	public function privilege_log(){
		$data=array();
		$data['pri_name']=request()->param('pri_name');
		$data['node']=request()->param('node');
		$data['parent_id']=request()->param('parent_id');
		$data['leval']=request()->param('leval');
		$data['descs']=request()->param('descs');
		$privilege=new Privilege();
		if(@request()->param('id')){
			//修改操作
			$data['id']=request()->param('id');
			$result=$privilege->validate(true)->update($data);
			if($result){
				echo "正确";
				return $this->success("添加成功，跳转权限列表",'admin/privilege_list');
			}else{
				echo "失败";
				alert_back("失败：".$privilege->getError());
			}
		}else{
			//添加操作
			$result=$privilege->validate(true)->save($data);
			if($result){
				echo "正确";
				return $this->success("添加成功，跳转权限列表",'admin/privilege_list');
			}else{
				echo "失败";
				alert_back("失败：".$privilege->getError());
			}
		}
	}
	
	/* 权限删除操作 */
	public function privilege_del(){
		if(!@request()->param('id')){
			$this->error("参数错误");
		}
		$id=request()->param('id');
		$del=db('privilege')->delete($id);
		if($del){
			$this->success("删除成功",'admin/privilege_list');
		}else{
			$this->error("删除失败",'admin/privilege_add');
		}
	}	
	
	/* 角色列表 */
	public function role_list(){
		$data=db('role')->paginate(5);
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	/* 角色添加 */
	public function role_add(){
		$data=db('privilege')->select();
		$data=pri_leval($data);
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	/* 角色添加，修改提交 */
	public function role_log(){
		//收集添加信息
		$data=array(
			'role_name'=>request()->param('role_name'),
			'remark'=>request()->param('remark'),
		);
		
		$role=new Role();
		Db::startTrans();
		try{
			if(@request()->param('id')){
				$data['id']=request()->param('id');
				$result=$role->validate(true)->update($data);
			}else{
				$result=$role->validate(true)->save($data);
			}
			
			$role_id=db('role')->where('role_name','=',$data['role_name'])->find();
			$role_pri_del=db('pri_role')->where('role_id','=',$role_id['id'])->delete();
			for($i=0;$i<count($_POST['privilege']);$i++){
			 	$msg=array(
					'pri_id'=>$_POST['privilege'][$i],
					'role_id'=>$role_id['id']
				);
				print_r($msg);
				db('pri_role')->insert($msg);
				
			}
			// 更新成功 提交事务
			Db::commit();
		} catch (\Exception $e) {
			// 更新失败 回滚事务
			Db::rollback();
			return $this->error('失败');
		}
		return $this->success('成功','admin/role_list');
	}
	
	/* 角色删除 */
	public function role_del(){
		if(!request()->param('id')){
			return $this->error("shibai");
		}else{
			$id=request()->param('id');
		}
		//删除角色
		db('role')->where('id','=',$id)->delete();
		//删除 role_admin 数据 
		db('role_admin')->where('role_id','=',$id)->delete();
		//删除 pri_role 数据
		db('pri_role')->where('role_id','=',$id)->delete();
		return $this->success('删除成功','admin/role_list');
	}
	
	/* 角色修改 */
	public function role_update(){
		if(!@request()->param('id')){
			return $this->error("获取ID错误");
		}
		//获取当前角色信息
		$data=db('role')->where('id','=',request()->param('id'))->find();
		//获取当前角色的权限
		$rp_msg=db('pri_role')->field(array('pri_id'))->where('role_id','=',request()->param('id'))->select();
		$rp_data=array();
		foreach($rp_msg as $v){
			$rp_data[]=$v['pri_id'];
		}
		//获取所有权限信息，排序，并通过当前角色信息判断是否勾选
		$pri_msg=pri_leval(db('privilege')->select(),$rp_data);		
		$this->assign('pri_msg',$pri_msg);
		$this->assign('data',$data);
		$this->assign('rp_msg',$rp_msg);
		return $this->fetch();
	}
	
	public function lists(){
		$str='';
		$data=db('privilege')->select();
		foreach($data as $v){
			if($v['leval']==1){
				$str.="<dl>";
				foreach($data as $t){
					if($t['leval']==2 && $t['parent_id']==$v['id']){
						$str.="<dt>".$t['pri_name']."</dt>";
					}
					foreach($data as $m){
						if($m['leval']==3 && $m['parent_id']==$t['id']){
							$url=url($v['node'].'/'.$t['node'].'/'.$m['node']);
							$str.="<dd><a href='".$url."'>".$m['pri_name']."</a></dd>";
						}
					}
				}
				$str.='</dl>';
			}
		}
		echo $str;
	}
	
	//测试
	public function nav_try(){
		$this->assign('nav',session('nav'));
		return $this->fetch();
	}
	
	
	
	
}



?>
<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Db;
use app\admin\model\Type as TypeModels;

/* 商品类别处理类 */
class Type extends Common{
	/* 商品类别列表 */
	public function type_list(){
		$data=db('type')->select();
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	/* 商品类别添加与修改 */
	public function type_add(){
		if(@request()->param('id')){
			$data=db('type')->where('id','=',request()->param('id'))->find();
			$this->assign('type_name',$data['type_name']);
			$this->assign('id',$data['id']);
			$this->assign('remark',$data['remark']);
			return $this->fetch('type/type_update');
			exit;
		}
		return $this->fetch();
	}
	
	/* 商品类别提交 */
	public function type_log(){
		$data=array(
			'type_name'=>request()->param('type_name'),
			'remark'=>request()->param('remark')
		);
		$type=new TypeModels();
		if(!@request()->param('id')){
			$result=$type->validate(true)->save($data);
		}else{
			$data['id']=request()->param('id');
			$result=$type->validate(true)->update($data);
		}
		if($result){
			return $this->success("成功",'type/type_list');
		}else{
			return $this->error("失败");
		}
	}
	
	/* 商品类别删除 */
	public function type_del(){
		if(!@request()->param('id')){
			return $this->error("参数错误");
		}
		$id=request()->param('id');
		//检测此商品类别是否被商品占用
		$count=db('goods')->where('type_id','=',$id)->count();
		if($count>0){
			return $this->error('此商品类别已被商品占用，无法删除');
		}else{
			$type_del=db('type')->where('id','=',$id)->delete();
			if($type_del){
				return $this->success('删除成功');
			}else{
				return $this->error('失败');
			}
		}
	}
	
}








?>
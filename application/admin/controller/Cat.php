<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Db;
use app\admin\model\Cat as CatModel;

/* 商品分类处理类 */
class Cat extends Common{
	/* 商品分类列表 */
	public function cat_list(){
		//检测添加or修改or几级分类
		if(@request()->param('id')){
			$cat=db('cat')->where('id','=',request()->param('id'))->find();
		}elseif(@!request()->param('cat_leval') && @!request()->param('parent_id')){
			$cat['cat_leval']=1;
			$cat['parent_id']=0;
			$cat['id']=-1;
		}else{
			$cat['cat_leval']=request()->param('cat_leval')+1;
			$cat['parent_id']=request()->param('parent_id');
			$cat['id']=-1;
		}
		//商品分类的无限级分类
		$this->assign('cat',$cat);
		$data_result=db('cat')->select();
		$data=cat_leval($data_result,url('cat/cat_list'));
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	/* 商品分类添加与修改 */
	public function cat_add(){
		if(@request()->param('id')!=-1){
			//修改操作
			$data=db('cat')->where('id','=',request()->param('id'))->select();
			$leval=$data['cat_leval'];
			$this->assign('data',$data);
		}
		//否则为添加操作
		return $this->fetch();
	}
	
	/* 商品分类提交 */
	public function cat_log(){
		$data=array(
			'cat_name'=>request()->param('cat_name'),
			'cat_leval'=>request()->param('cat_leval'),
			'parent_id'=>request()->param('parent_id'),
		);
		$cat=new CatModel();
		if(request()->param('id')==-1){
			//添加操作
			$result=$cat->validate(true)->save($data);
		}else{
			$data['id']=request()->param('id');
			$result=$cat->validate(true)->update($data);
		}
		if($result){
			return $this->success('do yes');
		}else{
			return $this->error('do no');
		}
	}
	
	/* 商品分类删除 */
	public function cat_del(){
		if(!@request()->param('id')){
			return $this->error("参数错误");
		}
		$id=request()->param('id');
		//检测此商品分类是否被商品占用
		$count=db('goods')->where('cat_id','=',$id)->count();
		if($count>0){
			return $this->error('此商品分类已被商品占用，无法删除');
		}else{
			$cat_del=db('cat')->where('id','=',$id)->delete();
			if($cat_del){
				return $this->success('删除成功');
			}else{
				return $this->error('失败');
			}
		}
	}
	
}








?>
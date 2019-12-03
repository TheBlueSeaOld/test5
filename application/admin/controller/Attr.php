<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Db;
use app\admin\model\Attr as AttrModel;

/* 类别属性处理类 */
class Attr extends Common{
	/* 类别属性列表 */
	public function attr_list(){
		if(!@request()->param('type_id')){
			$this->error("无类别识别号,无法操作");
		}
		$data=db('attr')->where('type_id','=',request()->param('type_id'))->select();
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	/* 类别属性提交 */
	public function attr_log(){
		if(count(request()->param())<=1){
			return $this->error("未提交任何数据");
		}
		$data=array();
		for($i=0;$i<count($_POST['attr_name']);$i++){
			$data[$i]['attr_name']=$_POST['attr_name'][$i];
			$data[$i]['attr_type']=$_POST['attr_type'][$i];
			$data[$i]['attr_option_value']=$_POST['attr_option_value'][$i];
			$data[$i]['type_id']=$_POST['type_id'];
		}
		$attr=new AttrModel();
 		for($i=0;$i<count($data);$i++){
			if(is_array($data)){
				$result=$attr->validate(true)->save($data[$i]);
				if(!$result){
					return $this->error("失败");
				}
			}
		} 
		return $this->success('成功','type/type_list');
		
	}
	
	/* 类别属性删除 */
	public function attr_del(){
		if(!@request()->param('id')){
			return $this->error("参数错误");
		}
		$id=request()->param('id');
		//检测此类别属性是否被商品占用
/* 		$count=db('goods')->where('type_id','=',request()->param('type_id'))->count();
		if($count>0){
			return $this->error('此类别属性已被商品占用，无法删除');
		}else{ */
			$attr_del=db('attr')->where('attr_id','=',$id)->delete();
			if($attr_del){
				return $this->success('删除成功');
			}else{
				return $this->error('失败');
			}
//		}
	}
	
/* 	// 属性删除 
	public function attr_del(){
		if(!request()->param('id')){
			$this->error("未知参数");
		}
		$attr_del=db('attr')->delete(request()->param('id'));
		if($attr_del){
			return $this->redirect('attr_list',['type_id'=>request()->param('type_id'));
		}else{
			return $this->error('删除失败');
		}
	} */
	
	
}








?>
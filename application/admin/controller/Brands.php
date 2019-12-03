<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Db;
use app\admin\model\Brands as BrandsModel;

/* 品牌处理类 */
class Brands extends Common{
	/* 品牌列表 */
	public function brands_list(){
		$data=db('brands')->select();
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	/* 品牌提交 */
	public function brands_log(){
		$a=picture_upload($_FILES);
		if(!@$_FILES){
			return $this->error('the message have none;');
		}
		//整理数据
		$data=array(
			'brand_name'=>request()->param('brand_name'),
			'site_url_smallint'=>request()->param('site_url_smallint'),
		);
		$brands=new BrandsModel();
		if(@request()->param('id')){
			//修改操作
			$data['id']=request()->param('id');
			//删除原logo图片
			$logo_url=db('brands')->where('id','=',request()->param('id'))->find();
			unlink($logo_url['logo']);
			//上传logo图片
			$upload=picture_upload($_FILES);
			if($upload){
				$data['logo']=$upload;
				$result=$brands->validate(true)->update($data);
				if($result){
					return $this->success('yes');
				}else{
					return $this->error("no",$brands->getError());
				}
			}else{
				return $this->error("bad");
			}
		}else{
			//添加操作
			//上传logo图片
			$upload=picture_upload($_FILES);
			if($upload!==''){
				$data['logo']=$upload;
				$result=$brands->validate(true)->save($data);
				if($result){
					return $this->success('add yes');
				}else{
					return $this->error("add no",$brands->getError());
				}
			}else{
				return $this->error("add bad".$upload);
			}
		}
	}
	
	/* 品牌删除 */
	public function brands_del(){
		
		if(!@$_POST['id']){
			return $this->error("未删除任何选项");
		}
		$id=$_POST['id'];
		//检测此品牌是否被商品占用
		$result=Db::query("select * from goods where brand_id in (".implode(',',$id).")");
		if(count($result)>0){
			return $this->error('此品牌已被商品占用，无法删除');
		}else{
			$brands_del=db('brands')->delete($id);
			if($brands_del){
				return $this->redirect('brands_list');
			}else{
				return $this->error('失败');
			}
		}
	}
	
	/* 品牌修改 */
	public function brands_update(){
		if(!request()->param('id')){
			$this->error('非法访问');
		}
		$result=db('brands')->where('id','=',request()->param('id'))->find();
		$this->assign('brands_data',$result);
	}
	
}








?>
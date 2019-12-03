<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use think\Db;
use app\admin\controller\Page;
use app\admin\validate\Goods as GoodsModel;
use think\Validate;

/* 商品处理类 */
class Goods extends Common{
	/* 筛选后的商品列表 */
	public function goods_list(){
		//获取 type 数据
		$type_data=db('type')->select();
		$this->assign('type_data',$type_data);
		
		//获取 attr 数据
		$cat_data=db('cat')->select();
		$this->assign('cat_data',$cat_data);
		
		$result=Db::query("select * from goods");
		$count=count($result);
		if($count>0){
			$page=new Page($count,5);
			$limit=$page->limit();
			$goods_data=Db::query("select * from goods g left join goods_text t on g.id=t.goods_id limit ".$limit);
			$this->assign('goods_data',$goods_data);
			$this->assign('page',$page->show(3));
		}else{
			$this->assign('goods_data',array());
		}
		return $this->fetch();
	}
	
	/* 商品添加 */
	public function goods_add(){
		$cat_data=db('cat')->select();
		$this->assign('cat_data',$cat_data);
		$brands_data=db('brands')->select();
		$this->assign('brands_data',$brands_data);
		$type_data=db('type')->select();
		$this->assign('type_data',$type_data);
		return $this->fetch();
	}
	
	/* 商品提交 */
	public function goods_log(){
		//上传商品logo
		$logo=picture_upload($_FILES);
		$_POST['logo']=$logo;
		//添加商品信息
		$goods=validate('goods');
		$check=$goods->check($_POST);
		if($goods->getError()){
			var_dump($goods->getError());
		}
		$goods_add=db('goods')->insert($_POST);
		if(!$goods_add){
			return $this->error("goods is bad");
		}else{
			$goods_data=db('goods')->field('type_id,id')->where('goods_name','=',$_POST['goods_name'])->field('goods_name,id')->find();
			//添加商品描述
			$data['goods_id']=$goods_data['id'];
			$goods_textarea=db('goods_text')->insert($data);
			return $this->redirect('goods/goods_attr',['goods_id'=>$goods_data['id'],'type_id'=>$goods_data['type_id']]);
		}
	}
	
	/* 商品属性选择 */
	public function goods_attr(){
		//获取商品类型 type 数据
		$attr_data=db('attr')->where('type_id','=',request()->param('type_id'))->select();
		$goods_id=request()->param('goods_id');	
		
		//获取选中商品的 goods_attr 包含数据
		$goods_attr=db('goods_attr')->where('goods_id','=',$goods_id)->select();
		$goodsAS='<ul>';
		if(count($goods_attr)>0){
			//制作商品类型已拥有的库存数据字符串
			foreach($goods_attr as $v){
				$attr_name_arr=explode('|',$v['attr_name']);
				$attr_value_arr=explode('|',$v['attr_value']);
				$goodsAS.="<li>";
				for($i=0;$i<count($attr_name_arr);$i++){
					$goodsAS.=$attr_name_arr[$i] ." : ". $attr_value_arr[$i].'&nbsp&nbsp&nbsp';
				}
				$goodsAS.="库存：".$v['goods_have'];
				$goodsAS.="<a href='".url('goods/goods_attr_del',['id'=>$v['id']])."'>[ 删除 ]</a>";
				$goodsAS.="</li>";
			}
		}
		$goodsAS.="</ul>";
		
		
		//循环商品属性信息HTML字符串
		$data='';
		foreach($attr_data as $v){
			$data.=$v['attr_name']." : ";
			if($v['attr_type']==1){
				$data.=$v['attr_name'].": 是<input type='radio' name='".$v['attr_id'].'|'.$v['attr_name']."' value='1'> 否<input type='radio' checked name='".$v['attr_id'].'|'.$v['attr_name']."' value='0'>";
			}else{
				$datas=explode('|',$v['attr_option_value']);
				$data.="<select name='".$v['attr_id'].'|'.$v['attr_name']."'>";
				//循环属性值拆分后的信息
				foreach($datas as $f){
					$data.=" <option value='".$f."'>".$f."</option> ";		//这里的value通过属性名与属性值组合
				}
				$data.="</select>";
			}
		}
		$data.="库存 ：<input type='number' name='goods_have'>";
		$data.="价格 : <input type='number' name='attr_price'><br>";
		$this->assign('data',$data);
		$this->assign('goodsAS',$goodsAS);
		$this->assign('attr_data',$attr_data);
		$this->assign('goods_id',$goods_id);
		return $this->fetch();
	}
	
	/* 商品属性删除地址 */
	public function goods_attr_del(){
		$goods_attr_del=db('goods_attr')->delete(request()->param('id'));
		if($goods_attr_del){
			echo "<script>histtory.back(1);</script>";
		}else{
			return $this->error('删除失败');
		}
	}
	
	/* 商品属性添加提交 */
	public function goods_attr_log(){
		var_dump($_POST);
		//重组库存数据
		$result=array();
		$result=[
			'goods_id'=>$_POST['goods_id'],
			'goods_have'=>$_POST['goods_have'],
			'attr_price'=>$_POST['attr_price'],
			'attr_id'=>'',
			'attr_name'=>'',
			'attr_value'=>''
		];
		$count=count($_POST)-2;
		foreach($_POST as $k=>$v){
			$arr=explode('|',$k);
			if($k!=='goods_id' && $k!=='goods_have' && $k!=='attr_price'){
				//将属性的ID,名称,值分别做成一个字符串
				$result['attr_id'].=$arr[0].'|';
				$result['attr_name'].=$arr[1].'|';
				$result['attr_value'].=$v.'|';
			}
		}
		
		//添加到数据库
		$add=db('goods_attr')->insert($result);
		if($add){
			return $this->redirect('goods/goods_list');
		}else{
			return $this->error("添加失败");
		}
	}
	
	/* 商品查询 */
	public function goods_search(){
		
	}
	
	//商品的上架与下架的ajax提交地址
	public function goods_down(){
		if(!@request()->param('id')){
			$this->error("参数错误");
		}
		$is_delete=db('goods')->where('id','=',request()->param('id'))->update(array('is_delete'=>request()->param('is_delete')));
		if($is_delete){
			return 1;
		}else{
			return 0;
		}
			
	}
	
	/* 商品详情页 */
	public function goods_details(){
		if(!@request()->param('id')){
			return $this->error("无ID访问",'goods_list');
		}
		$goods_data=db('goods')->where('id','=',request()->param('id'))->find();
		$goods_type=db('type')->where('id','=',$goods_data['type_id'])->find();
		$this->assign('goods_type',$goods_type['type_name']);
		$brands=db('brands')->where('id','=',$goods_data['brand_id'])->find();
		$this->assign('brands',$brands['brand_name']);
		$cat=db('cat')->where('id','=',$goods_data['cat_id'])->find();
		$text=db('goods_text')->where('goods_id','=',request()->param('id'))->find();
		$this->assign('text',$text['textarea']);
		$this->assign('cat',$cat['cat_name']);
		$this->assign('goods_data',$goods_data);
		return $this->fetch();
	}
	
	/* 商品修改 */
	
	/* 商品提交 */
	
	/* 商品删除 */
	public function goods_delete(){
		if(!@$_POST['id']){
			return $this->error("未传递参数");
		}
		$id=$_POST['id'];
		if(is_array($id)){
			$del=db('goods')->delete($_POST['id']);
			return $this->success("删除成功",'goods/goods_list');
		}else{
			$del=db('goods')->delete($_POST['id']);
			return $this->success("删除成功",'goods/goods_list');
		}
	}
/* 	在goods_attr一并将库存处理了
	//商品库存
	public function goods_have(){
//		echo request()->param('id');die;
		//获取当前商品的数据与库存
		$goods_have_data=Db::query("select * from goods_have gh left join goods_attr ga on gh.goods_attr_id=ga.id where gh.goods_id=".request()->param('id'));
		//获取goods_attr字段数据
		$goods_attr_data=Db::query("select a.attr_name,ga.* from goods_attr ga left join attr a on ga.attr_id=a.id where goods_id=".request()->param('id'));
		//重组含有attr名字的goods_attr表数据
		$data=array();
		foreach($goods_attr_data as $v){
			$data[$v['attr_id']]['attr_name']=$v['attr_name'];
			$data[$v['attr_id']][]=$v['id'].'|'.$v['attr_value'];
		}
		$this->assign('goods_have_data',$goods_have_data);
		$this->assign('data',$data);
		$this->assign('goods_attr_data',$goods_attr_data);
		session('goods_id',request()->param('id'));
		return $this->fetch();
	}
	
	//商品库存提交
	public function goods_have_log(){
		//重组库存数据
		$data=array();
		$data['goods_id']=session('goods_id');
		$data['goods_have']=request()->param('goods_number');
		$data['goods_attr_id']='';
		foreach($_POST as $key=>$v){
			if($key!='goods_number'){
				$data['goods_attr_id'].=$key."|";
			}
		}
		$result=db('goods_have')->insert($data);
		if(!$result){
			$this->error("库存添加失败");
		}
		return $this->success("添加成功");
		
	}
	 */
	
	
	
	
}





?>
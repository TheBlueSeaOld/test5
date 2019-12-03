<?php
namespace app\admin\controller;
use think\Controller;

class Page extends Controller{
	private $total_rows;			//结果集总条数
	private $total_page;			//总页数
	private $onepage_rows;			//每页显示条数
	private $self_page;				//当前页
	private $url;					//URL地址
	private $page_rows;				//需要显示的页数
	private $start_id;				//当前页起始ID
	private $end_id;				//当前页结束ID；
	private $desc=array();

	//初始化配置
	function __construct($total,$rows,$page_rows=8,$desc=array()){
		if(isset($_GET['page'])){
			$_nowPage=is_numeric($_GET['page'])?floor($_GET['page']):$_GET['page'];
		}else{
			$_nowPage=1;
		}
		$this->total_rows=$total;
		$this->onepage_rows=min($rows,$this->total_rows);
		$this->total_page=ceil($this->total_rows/$this->onepage_rows);
		$this->page_rows=min($this->total_page,ceil($page_rows/2)*2);		//防止后面使用$page_rows与ceil($page_rows/2)不吻合
		$this->self_page=isset($_nowPage)?min(max($_nowPage,1),$this->total_page):1;
		$this->start_id=($this->self_page-1)*$this->onepage_rows;
		$this->end_id=min($this->total_rows,$this->self_page*$this->onepage_rows);
		$this->url=$this->requestUrl();												//配置URL地址
		$this->desc=$this->desc($desc);
	}
	//配置URL地址
	private function requestUrl(){
		$url=isset($_SERVER['REQUEST_URI'])?
			$_SERVER['REQUEST_URI']:$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$request_Arr=parse_url($url);		//将$url拆分为地址，和参数
		if(isset($request_Arr['query'])){
			//解析请求参数
			parse_str($request_Arr['query'],$arr);
			if(isset($arr['page'])){
				unset($arr['page']);
			}
			//合并路径及请求参数为标准URL地址
			$page=empty($arr)?'page=':'&page=';
			$url=$request_Arr['path']."?".http_build_query($arr).$page;
		}else{
			//没有请求参数
			$url=strstr($url,'?')?$url."page=":$url."?page=";
		}
		return $url;
	}
	/**
	* 配置分页文字描述
	* 'pre'=>'上一页'
	* 'next'=>'下一页'
	* 'first'=>'首页'
	* 'end'=>'末页'
	* 'unit'=>'条'
	**
	**/
	private function desc($desc){
		//默认文字描述
		$d=array(
			'pre'=>'上一页',
			'next'=>'下一页',
			'first'=>'首页',
			'end'=>'末页',
			'unit'=>'条'
		);
		if(empty($desc) || !is_array($desc)){
			return $d;
		}
		function filter($v){			//循环函数，下面用
			return !empty($v);
		}
		return array_merge($d,array_filter($desc,"filter"));
	}
	//SQL的limit语句
	public function limit(){
		return "{$this->start_id},{$this->onepage_rows}";
	}
	//上一页
	public function pre(){
		return $this->self_page>1?"<a href='".$this->url.($this->self_page-1)."'>{$this->desc['pre']}
			</a>":$this->desc['pre'];
	}
	//下一页
	public function next(){
		return $this->self_page<$this->total_page?"<a href='".$this->url.($this->self_page+1)."'>{$this->desc['next']}</a>":$this->desc['next'];
	}
	//首页
	public function first(){
		return $this->self_page>1?"<a href='{$this->url}1'>{$this->desc['first']}</a>":$this->desc['first'];
	}
	//尾页
	public function end(){
		return $this->self_page<$this->total_page?"<a href='{$this->url}{$this->total_page}'>{$this->desc['end']}</a>":$this->desc['end'];
	}
	//当前页记录
	public function nowpage(){
		return "第{$this->start_id}{$this->desc['unit']}-{$this->end_id}{$this->desc['unit']}";
	}
	//返回当前页码数
	public function selfnum(){
		return $this->self_page;
	}
	//count 统计数据信息
	public function count(){
		return "<span>总共有{$this->total_page}页，共有{$this->total_rows}条数据</span>";
	}
	//页码（被strlist,select...函数调用）
	private function pagelist(){
		$pagelist=array();
		//$start:显示页码列的第一个页码
		$start=max(1,
			min($this->self_page-ceil($this->page_rows/2),$this->total_page-$this->page_rows));
		//$end:显示的页码列中最后一个页码
		$end=min($start+$this->page_rows,$this->total_page);
		for($i=$start;$i<=$end;$i++){
			if($i==$this->self_page){
				$pagelist[$i]['url']='';
				$pagelist[$i]['str']=$i;
				continue;
			}
			$pagelist[$i]['url']=$this->url.$i;
			$pagelist[$i]['str']=$i;
		}
		return $pagelist;
	}
	//页码显示
	public function strlist(){
		$arr=$this->pagelist();
		$pagelist='';
		foreach($arr as $v){
			$pagelist.=empty($v['url'])?"<strong>{$v['str']}</strong>&nbsp":"<a
				href='{$v['url']}'>{$v['str']}</a>&nbsp";
		}
		return $pagelist;
	}
	//下拉列表分页
	public function select(){
		$arr=$this->pagelist();
		$str="<select class='pageSelect' onchange='javascript:location.href=
			this.options[selectedIndex].value'>";
		foreach($arr as $v){
			$str.=empty($v['url'])?"<option value='{$this->url}{$v['str']}' 
				selected='selected'>{$v['str']}</option>":"<option
				value='{$v['url']}'>{$v['str']}</option>";
		}
		$str.="</select>";
		return $str;
	}
	//直接输入页码进行跳转
	public function input(){
		$str="<input type='text' value='{$this->self_page}' id='pageinput' 
			class='pageinput' onkeydown=\"
			javascript:if(event.keyCode==13){
				location.href='{$this->url}'+this.value;
			};\"/>
			
			<button onclick=\"javascript:
			var url=document.getElementById('pageinput').value;
			location.href='{$this->url}'+url;
			\"/>
			跳转</button>
			";
		return $str;
	}
	//展示形式
	public function show($style_id=1){
//		echo "xxxx";die;
		switch($style_id){
			case 1:
				return $this->pre().$this->strlist().$this->next();
			case 2:
				return $this->pre().$this->strlist().$this->next().$this->count();
			case 3:
				return $this->first().$this->pre().$this->strlist().$this->next().$this->end().$this->select().$this->input().$this->count();
		}
	}
}


/*
	include "config.php";
	include "db.class.php";
	$db=new db("user");				//实例化数据库
	$total=$db->count();			//结果集总数目
	$pagelist=new page($total,3,3);
	$result=$db->sql("SELECT * FROM user {$pagelist->limit()}");
	echo $pagelist->show(3);



当地址栏page传入小数时，db.class.php的84行sql查询会报错，其实是上面limit的传参错了，不能传小数


*/





?>
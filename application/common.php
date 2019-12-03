<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function alert($a){
	echo "<script>alert('".$a."');</script>";
}
function alert_href($a,$b){
	echo "<script>alert('".$a."');location.href='".$b."'</script>";
}
function alert_back($a){
	echo "<script>alert('".$a."');history.back(-1)</script>";
}
function leval_list($data,$pid=0,$leval=1){
	$arr=array();
	foreach($data as $v){
		if($v['parent_id']==$pid){
			$arr[]=$v;
			$arr1=leval_list($data,$v['id'],$v['leval']);
			$arr=array_merge($arr,$arr1);
		}
	}
	return $arr;
}

/**
 * 角色选项的权限无限级分类 
 * @param $data 所有权限的数组集合
 * @param $pri_arr 当前角色的所有权限，没有参数则为添加操作，
*/
function pri_leval($data,$pri_arr=array()){
	$str='';
	foreach($data as $v){
		if($v['leval']==1){
			$str.="<div class='leval1'>";
			//判断此权限ID是否在当前角色的权限数组中
			if(in_array($v['id'],$pri_arr)){
				$checked="checked";
			}else{
				$checked='';
			}
			$str.="<input type='checkbox' ".$checked." name='privilege[]' value='".$v['id']."'>".$v['pri_name'];
			$str.='</div>';
			foreach($data as $p){
				if($p['parent_id']==$v['id']){
					//判断此权限ID是否在当前角色的权限数组中
					if(in_array($p['id'],$pri_arr)){
						$checked1="checked";
					}else{
						$checked1='';	
					}
					$str.="<div class='leval2'><input type='checkbox' ".$checked1." name='privilege[]' value='".$p['id']."'>".$p['pri_name']."</div>";
				}
			}
			
		}
	}
	return $str;
}

/* 图片上传操作 */
function picture_upload($FILE){	
	$type=$FILE['userfile']['type'];
	$name=$FILE['userfile']['name'];
	$url=$FILE['userfile']['tmp_name'];
	$size=$FILE['userfile']['size'];
	$error=$FILE['userfile']['error'];

	//初始化定义
	if(!defined('MAX_SIZE')){
		define("MAX_SIZE",2000000);													//创建上传最大值，放置配置验证失效
	}
	$NewSrc="./static/".$name;				//上传文件的最终地址,一般传到一个upload文件夹里
	$arr=array('image/jpeg','image/pjpeg','image/gif','image/png','image/x-png');	//限制上传文件类型的数组集合

	//判断上传是否有错误
	if($error>0){
		switch($error){
			case 1: echo "<script>alert('the file be to long');history.back();</script>";
				break;
			case 2: echo "<script>alert('error : picture is to big');history.back();</script>";
				break;
			case 3: echo "<script>alert('error : upload somthing');history.back();</script>";
				break;
			case 4: echo "<script>alert('error : no files');history.back();</script>";
				break;
		}
		exit;
	}
	//判断大小
	if($size>MAX_SIZE){
		echo "<script>alert('picture is too big for exsaple');history.back();</script>";exit;
	}

	//判断类型
	if(is_array($arr)){
		if(!in_array($FILE['userfile']['type'],$arr)){					//上传文件类型是否在规范内
			echo "<script>alert('error : data is not');history.back();</script>";exit;
		}
	}
	//正式上传文件
	if(is_uploaded_file($url)){											//已成功上传到临时文件夹
		if(!move_uploaded_file($url,$NewSrc)){							//还需要检测上传到的文件夹是否存在
			echo "<script>alert('error : upload error');history.back();</script>";
			return false;
		}
	}
	return $NewSrc;
}

//商品分类的无限级分类
function cat_leval($data,$url='',$pid=0,$leval=1){
	
	$result='';
	foreach($data as $v){
		if($v['parent_id']==$pid){
			$str=str_pad('',$leval*3,'---');
			$result.="<dd>".$str.$v['cat_name']." <a href='".$url."?parent_id=".$v['id']."&cat_leval=".$v['cat_leval']."'>[添加子分类]</a> </dd>";
			$result.=cat_leval($data,$url,$v['id'],$v['cat_leval']+1);
		}
	}
	return $result;
}

//nav无限级分类
function nav_leval($data,$pid=0,$leval=1){
	$nav=array();
	foreach($data as $v){
		if($v['parent_id']==$pid){
			if($v['leval']<=2){
				$v['child']=nav_leval($data,$v['id'],$leval+1);
			}
			$nav[]=$v;
		}
	}
	return $nav;
}












?>


















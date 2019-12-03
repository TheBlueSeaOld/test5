<?php
namespace app\admin\validate;
use think\Validate;

class Goods extends Validate{
	protected $rule=[
		'goods_name'=>'unique:goods|require',
	];
	protected $message=[
		'goods_name.unique'=>'this name is have',
		'goods_name.require'=>'goods_name must be not null'
	];
}


?>
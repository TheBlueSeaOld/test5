<?php
namespace app\index\validate;
use think\Validate;

class Users extends Validate{
	//验证规则
	protected $rule=[
		['name','require|min:5','昵称必须|最少五位数']
	];
}



?>
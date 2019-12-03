<?php
namespace app\admin\validate;
use think\Validate;

class Admin extends Validate{
	protected $rule=[
		'name'=>'unique:admin|min:3|max:15|require',
		'password'=>'min:6|max:16|require'
	];
	protected $message=[
		'name.unique'=>'管理员已存在',
		'name.require'=>'名称必填',
		'name.min'=>'名称最小三位',
		'name.max'=>'名称最多15位',
		'password.min'=>'密码最少6字节',
		'password.max'=>'密码最多16字节',
		'password.require'=>'密码必填'
	];
}




?>
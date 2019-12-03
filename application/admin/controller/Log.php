<?php
namespace app\admin\controller;
use think\Controller;

class Log extends Controller{
	public function index(){
		return $this->fetch();
	}
	public function logTo(){
		if(!isset($_POST['name']) || !isset($_POST['password'])){
			exit(alert_href('name or pwd is null',url('log/index')));
		}
		$userMsg=db('admin')->where('name','=',$_POST['name'])->find();
		if($userMsg){
			if($userMsg['password']==$_POST['password']){
				session('name',$_POST['name']);
				session('id',$userMsg['id']);
				$this->success("登录成功，即将跳转",'index/index');
			}else{
				exit(alert_back('the password is false'));
			}
		}else{
			exit(alert_back('the name is false'));
		}
	}
}



?>
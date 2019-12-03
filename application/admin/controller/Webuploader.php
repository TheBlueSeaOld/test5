<?php
namespace app\admin\controller;
use think\Controller;
use think\File;
class Webuploader  extends Controller
{
    public function index()
    {
         return $this->fetch();
    }
    function upload(){
        $file = $this->request->file('file');//file是传文件的名称，这是webloader插件固定写入的。因为webloader插件会写入一个隐藏input，不信你们可以通过浏览器检查页面
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
    }
}
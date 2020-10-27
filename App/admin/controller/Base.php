<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use communal\Communal;

class Base extends \think\Controller
{
    public function _initialize()
    {
        //检查登录
        Communal::checklogin();

        $uname = session('uname');
        $this->assign('uname', $uname);
    }
}
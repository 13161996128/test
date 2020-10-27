<?php
namespace app\admin\controller;
use think\Db;
use think\Request;

class Login  extends \think\Controller
{

    /*
    展示列表
     */
    public function index()
    {
        return $this->fetch();
    }
    /**
     * 登录
     */
    public function tologin() {

        $data = $user = array();
        $username = trim(input('useruname'));
        $password = trim(input('passwd'));
        $yzmcode = trim(input('yzmcode'));

        if(!$username) {
            echo 'USERNAME_IS_NULL';exit;
        }

        if(!$password) {
            echo 'PASSWORD_IS_NULL';exit;
        }

        if(!captcha_check($yzmcode)) {
            echo 'VERIFY_IS_ERROR';exit;
        }

        $password = md5($password);

        $user = db()->query("SELECT * FROM admin_user WHERE `username`='$username' AND `password`='$password'");

        if(empty($user)) {
            echo 'USER_IS_NULL_OR_PASS_ERROR';exit;
        }

        $uid = $user[0]['id'];

        //是否被封号
        /*
        if($user['islock']) {
            echo 'USER_ACCOUNT_IS_LOCK';exit;
        }
        */

        if($user[0]['username']) {
            $uname = $user[0]['username'];
        }

        session('uname', $uname);
        session('uid', $user[0]['id']);
        session('gid', $user[0]['gid']);
        session('user', $user);

        echo 'LOGIN_SUCCESS';exit;
    }
    /**
     * 退出
     */
    public function logout() {
        session(null);
        $this->redirect('/admin/login/index');
    }
}
<?php


namespace app\admin\controller;


use think\Controller;
use think\Db;

class Login extends Controller
{
    public function index()
    {
        return $this->fetch('login');
    }

    public function login()
    {
        $post     = $this->request->post();
        if(empty($post)){
            $this->redirect('login/index');
        }
        $validate = validate('Login');

        $validate->scene('login');

        $username  =  $post['username'];

        $user_id = Db::name('user')
            ->where('username', $username)
            ->value('id');
        if (!$validate->check($post)) {
            $this->error($validate->getError());
        } else {
            $sql_password = Db::name('user')
                ->where('username', $username)
                ->where('status',1)
                ->value('password');
            if (md5($post['password']) !== $sql_password) {
                $this->error('密码错误');
            } else {
                session('username', $username);
                session('user_id', $user_id);
                Db::name('user')
                    ->where('username',$username)
                    ->update(['last_login_ip'=>$_SERVER['REMOTE_ADDR'],'last_login_time'=>date('Y-m-d h:i:s',time())]);
                $this->success('登陆成功', 'index/index');
            }
        }
    }

    public function logOut()
    {
        session('username', null);
        session('user_id', null);
        $this->redirect('admin/login/index');
    }
}
<?php


namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Base extends Controller
{
    public function _initialize(Request $request = null)
    {
        $username  = session('username');
        if (empty($username)) {
            $this->redirect('admin/user/login');
        }
        $this->checkAuth();
        $this->getMenu();
    }

    public function checkAuth()
    {
        if (!Session::has('user_id')) {
            $this->redirect('admin/login/index');
        }

        $module     = $this->request->module();
        $controller = $this->request->controller();
        $action     = $this->request->action();
        $url = $module . '/' . $controller . '/' . $action;
        // 排除权限
        $not_check = ['admin/Index/index'];
//        $not_check = ['admin/'];

        if (!in_array($url, $not_check)) {
            $admin_id = Session::get('user_id');
            //判断是否有权限访问目录
            $check_list = $this->auth($admin_id,$url);
            if(!in_array($url,$check_list)){
                $this->error('没有权限','');
            }
        }
    }

    public function getMenu()
    {

    }

    public function auth($admin_id, $url)
    {
        $groupList = Db::name('auth_group_access')
            ->where('uid', $admin_id)
            ->alias('a')
            ->join('sm_auth_group s', 'a.group_id = s.id')
            ->select();
        $arr = [];
        foreach ($groupList as $v) {
            array_push($arr, $v['rules']);
        }
        $arr = array_unique(explode(',', implode(',', $arr)));
        sort($arr);
        $auth_id = implode(',',$arr);

        $data =Db::name('auth_rule')->where('id IN ('.$auth_id.')')->column('name');
        return $data;
    }
}
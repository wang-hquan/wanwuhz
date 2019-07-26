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
            $this->redirect('admin/login/index');
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
        $url =strtolower($module . '/' . $controller . '/' . $action);
        // 排除权限
        $not_check = ['admin/Index/index','admin/index/welcome'];

        if (!in_array($url, $not_check)) {
            $admin_id = Session::get('user_id');

            //判断是否有权限访问目录
            $auth_list = $this->auth($admin_id);
            foreach ($auth_list as &$v){
                $check_list[] = strtolower($v['name']);
            }
            if(!in_array($url,$check_list)  && $admin_id !=1){
                $this->error('没有权限','admin/login/index');
            }
        }
    }

    //生成菜单
    public function getMenu()
    {
        $admin_id = Session::get('user_id');
        $auth_list = $this->auth($admin_id);
        $menu = $this->getTree($auth_list);
//        echo '<pre>';
//        print_r($menu);exit;
        $this->assign('menu', $menu);
    }

    //获取权限路径
    public function auth($admin_id)
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
        if($auth_id==''){
            $this->error('此用户暂未激活,请联系管理员处理','/admin/login/logout');
        }
        $data =Db::name('auth_rule')
            ->where('status',1)
            ->where('id IN ('.$auth_id.')')
            ->order(['sort' => 'DESC', 'id' => 'ASC'])
            ->select();
        return $data;
    }


    //生成菜单树
    public  function getTree($data, $pid = 0)
    {

        $tree = '';
        foreach ($data as $k => $v) {
            if ($v['pid'] == $pid) {
                $result = $this->getTree($data, $v['id']);
                if($result != ''){
                  $v['children'] = $result;
                }
                $tree[] = $v;
                unset($data[$k]);
            }
        }
        return $tree;
    }


}
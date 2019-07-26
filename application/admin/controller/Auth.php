<?php


namespace app\admin\controller;


use think\Db;

class Auth extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    //获取权限路径列表
    function auth_list()
    {
        //获取权限列表

        $authList = Db::name('auth_rule')
            ->order(['sort' => 'DESC', 'id' => 'ASC'])
            ->select();
        $auth = $this->getAuthTree($authList);
        foreach ($auth as &$value) {
            $value['name'] = str_repeat('--', $value['level']) . $value['name'];
        }
        return success('0', $auth, 1000);
    }

    public function showAdd()
    {
        $authList = Db::name('auth_rule')
            ->order(['sort' => 'DESC', 'id' => 'ASC'])
            ->select();
        $auth = $this->getAuthTree($authList);
        foreach ($auth as &$value) {
            $value['title'] = str_repeat('-- ', $value['level']) . $value['title'];
        }
        $this->assign('auth', $auth);
        return $this->fetch('add');
    }

    //新增权限
    public function add()
    {
        $post = $this->request->post();

        Db::name('auth_rule')
            ->insert($post);
        $this->success('success');
    }

    public function edit()
    {
        $authList = Db::name('auth_rule')
            ->order(['sort' => 'DESC', 'id' => 'ASC'])
            ->select();
        $auth = $this->getAuthTree($authList);
        foreach ($auth as &$value) {
            $value['title'] = str_repeat('-- ', $value['level']) . $value['title'];
        }
        $this->assign('auth', $auth);

        $id = $this->request->param('id');
        $data = Db::name('auth_rule')->where('id', $id)->find();
        $this->assign('data', $data);
        return $this->fetch('edit');

    }

    public function editAuth()
    {
        $post = $this->request->post();
        $id = $post['id'];
        Db::name('auth_rule')->where('id', $id)->update($post);
        $this->success('success');
    }

    public function delete()
    {
        $id = $this->request->post('id');
        $juge = Db::name('auth_rule')
            ->where('pid', $id)
            ->find();
        if (!empty($juge)) {
            $this->error('请先删除子权限');
        } else {
            Db::name('auth_rule')->delete($id);
            $this->success('success');
        }
    }


    //生成路径tree
    public function getAuthTree($array, $pid = 0, $level = 0)
    {
        static $list = [];

        foreach ($array as $key => $v) {
            if ($v['pid'] == $pid) {
                $v['level'] = $level;
                $list[] = $v;
                unset($array[$key]);
                $this->getAuthTree($array, $v['id'], $level + 1);
            }
        }
        return $list;
    }
}
<?php


namespace app\admin\controller;


use think\Db;

class User extends Base
{
    public function index()
    {

        return $this->fetch('user_list');
    }

    public function user_list()
    {
        $post = $this->request->param();
        $count = Db::name('User')->count();
        if (isset($post['key'])) {
            $where = $post['key'];
            $where = array_filter($where);
        } else {
            $where = [];
        }
        $data = Db::name('User')
            ->where($where)
            ->order('id asc')
            ->page($post['page'], $post['limit'])
            ->select();
        return success('0', $data, $count);
    }

    //展示页面
    public function showAdd()
    {
        $group = Db::name('auth_group')
            ->where('status', 1)
            ->field('id,title')
            ->order('id desc')
            ->select();
        return $this->fetch('add', ['auth_group' => $group]);
    }

    //添加用户
    public function addUser()
    {
        $post = $this->request->post();
        $group_id = $post['group_id'];
        unset($post['group_id']);
        unset($post['check_password']);
        $post['password'] = md5($post['password']);
        $post['last_login_ip'] = '0.0.0.0';
        $post['create_time'] = date('Y-m-d h:i:s', time());
        $db = Db::name('user')
            ->insert($post);
        $userId = Db::name('user')->getLastInsID();
        Db::name('auth_group_access')
            ->insert(['uid' => $userId, 'group_id' => $group_id]);
        $this->success('success');
    }

    //编辑页面
    public function edit()
    {
        $id = $this->request->param('id');
        $data = Db::name('User')
            ->alias('a')
            ->join('auth_group_access b', 'b.uid=a.id', 'left')
            ->field('a.*,b.group_id')
            ->where('id', $id)
            ->find();
        $auth_group = Db::name('auth_group')
            ->field('id,title')
            ->order('id desc')
            ->select();
        $this->assign('auth_group', $auth_group);
        $this->assign('data', $data);
        return $this->fetch();
    }

//    确认修改
    public function editUser()
    {
        $post = $this->request->post();
        if ($post['id'] == 1) {
            $this->error('系统管理员无法修改');
        }
        $group_id = $post['group_id'];
        unset($post['group_id']);
        if (empty($post['password']) && empty($post['check_password'])) {
            unset($post['password']);
            unset($post['check_password']);
            $db = Db::name('user')
                ->where('id', $post['id'])
                ->update(
                    [
                        'status' =>$post['status'],
                        'username' => $post['username'],
                        'email' => $post['email'],
                    ]);
            Db::name('auth_group_access')
                ->where('uid', $post['id'])
                ->update(['group_id' => $group_id]);
            $this->success('编辑成功');
        } else {
                unset($post['check_password']);
                $post['password'] = md5($post['password']);
                $db               = Db::name('user')
                    ->where('id', $post['id'])
                    ->update($post);
                $this->success('编辑成功');
        }
    }

    //删除
    public function deleteUser()
    {
        $id = $this->request->post('id');
        $username =  Db::name('user')
            ->where('id',$id)
            ->value('username');
        if ((int) $id !== 1) {
            if($username!==session('username')){
                Db::name('user')->where('id', $id)->delete();
                Db::name('auth_group_access')->where('uid',$id)->delete();

                $this->success('删除成功');
            }else{
                $this->error('无法删除当前登录用户');
            }
        } else {
            $this->error('超级管理员无法删除');
        }
    }


}
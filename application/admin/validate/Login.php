<?php
namespace app\admin\validate;

use think\Validate;

class Login extends Validate
{
    protected $rule = [
        'username'       => 'require|length:2,30|unique:user',
        'password'       => 'require|min:5|max:30',
        'email'          => 'require|email|unique:user',
        'check_password' => 'require|confirm:password',
    ];
    protected $message = [
        'email.unique'           => '邮箱已存在，请更换邮箱',
        'username.require'       => '请输入用户名',
        'username.unique'        => '用户名重复',
        'username.length'           => '用户名最短2位',
//			'username.max'           => '用户名最长30位',
        'password.require'       => '请填写密码',
        'password.min'           => '用户名最短6位',
        'password.max'           => '用户名最长30位',
        'check_password.require' => '请确认密码',
        'check_password.confirm' => '输入密码不一致',
    ];
    protected $scene = [
        'login' => ['username' => 'require', 'password'],
        'edit'  => [
            'username' => 'require|unique:user',
            'email'    => 'require|email',
        ],
        'editPassword'  => [
            'username' => 'require|unique:user',
            'email'    => 'require|email',
            'password',
            'check_password',
        ],
    ];
}

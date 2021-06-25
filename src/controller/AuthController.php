<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use lgdz\hyperf\service\AuthService;
use lgdz\hyperf\service\UserService;
use lgdz\hyperf\Tools;

class AuthController
{
    /**
     * @Inject
     * @var AuthService
     */
    protected $AuthService;

    /**
     * @Inject
     * @var UserService
     */
    protected $UserService;

    // 账号密码登录
    public function login()
    {
        $body = Tools::Body();
        $result = $this->AuthService->loginByUsername($body->username, $body->password);
        return Tools::ok($result);
    }

    // 修改登录密码
    public function changePwd()
    {
        $this->UserService->update(Tools::U()->id, Tools::Body(['op' => 'ChangePassword']));
        return Tools::ok();
    }

    // 退出登录
    public function logout()
    {
        $this->AuthService->logout();
        return Tools::ok();
    }
}

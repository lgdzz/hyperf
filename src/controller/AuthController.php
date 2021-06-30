<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\service\AuthService;
use lgdz\hyperf\service\UserService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 */
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

    /**
     * 用户名登录
     * @RequestMapping(path="/l/login", methods="post")
     */
    public function login()
    {
        $body = Tools::Body();
        $result = $this->AuthService->loginByUsername($body->username, $body->password);
        return Tools::ok($result);
    }

    /**
     * 修改登录密码
     * @RequestMapping(path="/l/change_pwd", methods="put")
     * @Middleware(AuthUserMiddleware::class)
     */
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

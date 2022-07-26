<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\ValidatorMiddleware;
use lgdz\hyperf\model\Account;
use lgdz\hyperf\service\AccountService;
use lgdz\hyperf\service\AuthService;
use lgdz\hyperf\service\AuthService2;
use lgdz\hyperf\service\LoginLogService;
use lgdz\hyperf\service\UserService;
use lgdz\hyperf\Tools;
use lgdz\hyperf\validator\AuthValidator;

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
     * @Inject
     * @var AccountService
     */
    protected $AccountService;

    /**
     * 用户名登录
     * @RequestMapping(path="/l/login", methods="post")
     * @Validator(AuthValidator::class)
     * @Middleware(ValidatorMiddleware::class)
     */
    public function login()
    {
        $body = Tools::Body();
        $result = $this->AuthService->loginByUsername($body->username, $body->password);
        return Tools::Ok($result);
    }

    /**
     * 登录日志
     * @RequestMapping(path="/l/loginlog", methods="get")
     * @Middleware(AuthUserMiddleware::class)
     */
    public function loginlog()
    {
        $result = Tools::container()->get(LoginLogService::class)->index(Tools::Query());
        return Tools::Ok($result);
    }

    /**
     * 退出登录
     * @RequestMapping(path="/l/logout", methods="get")
     * @Middleware(AuthUserMiddleware::class)
     */
    public function logout()
    {
        $this->AuthService->logout(Tools::U()->id);
        return Tools::Ok();
    }

    /**
     * 修改登录密码
     * @RequestMapping(path="/l/change_pwd", methods="put")
     * @Middlewares({
     *     @Middleware(AuthUserMiddleware::class),
     *     @Middleware(ValidatorMiddleware::class)
     * })
     * @Validator(AuthValidator::class)
     */
    public function changePwd()
    {
        $this->UserService->update(Tools::U()->id, Tools::Body(['op' => 'ChangePassword']), false);
        return Tools::Ok();
    }

    /**
     * 账户列表
     * @RequestMapping(path="/l/accounts", methods="get")
     * @Middleware(AuthUserMiddleware::class)
     */
    public function accounts()
    {
        $result = $this->AccountService->index(Tools::Query(['user_id' => Tools::U()->id, 'status' => Account::status_ok]), ['role', 'org.grade', 'user']);
        return Tools::Ok($result);
    }

    /**
     * 账户路由(ant-design)
     * @RequestMapping(path="/l/router/{account_id}", methods="get")
     * @Middleware(AuthUserMiddleware::class)
     */
    public function router(int $account_id)
    {
        $result = $this->AuthService->getRouterConfig($account_id);
        return Tools::Ok($result);
    }

    /**
     * 账户路由2(element-ui)
     * @RequestMapping(path="/l/router2/{account_id}", methods="get")
     * @Middleware(AuthUserMiddleware::class)
     */
    public function router2(int $account_id)
    {
        $result = Tools::container()->get(AuthService2::class)->getRouterConfig($account_id);
        return Tools::Ok($result);
    }

    /**
     * 检查完善资料接口
     * @RequestMapping(path="/l/perfect_info", methods="get")
     * @Middlewares(
     *     @Middleware(AuthUserMiddleware::class),
     *     @Middleware(AccountMiddleware::class)
     * )
     */
    public function checkPerfectInfo()
    {
        return Tools::Ok(['status' => $this->AuthService->checkPerfectInfo()]);
    }

    /**
     * 保存完善信息
     * @RequestMapping(path="/l/perfect_info", methods="post")
     * @Middlewares(
     *     @Middleware(AuthUserMiddleware::class),
     *     @Middleware(AccountMiddleware::class)
     * )
     */
    public function savePerfectInfo()
    {
        $this->AuthService->savePerfectInfo(Tools::Body());
        return Tools::Ok();
    }
}

<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use lgdz\Factory;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\ValidatorMiddleware;
use lgdz\hyperf\service\UserService;
use lgdz\hyperf\Tools;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\validator\UserValidator;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * })
 */
class UserController
{
    /**
     * @Inject
     * @var UserService
     */
    protected $UserService;

    /**
     * @RequestMapping(path="/l/user", methods="get")
     */
    public function index()
    {
        $result = $this->UserService->index(Tools::Query());
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/user/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->UserService->user($this->UserService->findById($id))->hiddenPassword();
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/user", methods="post")
     * @Validator(UserValidator::class)
     * @Middleware(ValidatorMiddleware::class)
     */
    public function create()
    {
        $this->UserService->create(Tools::Body());
        Tools::Oplog('新建用户[基础]');
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/user/{id}", methods="put")
     * @Validator(UserValidator::class)
     * @Middleware(ValidatorMiddleware::class)
     */
    public function update(int $id)
    {
        $this->UserService->update($id, Tools::Body());
        Tools::Oplog('修改用户[基础]');
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/user/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->UserService->delete($id);
        Tools::Oplog('删除用户[基础]');
        return Tools::Ok();
    }
}

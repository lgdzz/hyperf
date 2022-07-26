<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\ValidatorMiddleware;
use lgdz\hyperf\validator\RoleValidator;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\service\RoleService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * })
 */
class RoleController
{
    /**
     * @Inject
     * @var RoleService
     */
    protected $RoleService;

    /**
     * @RequestMapping(path="/l/role", methods="get")
     */
    public function index()
    {
        $result = $this->RoleService->index(Tools::Query());
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/role/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->RoleService->role($this->RoleService->findById($id));
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/role", methods="post")
     * @Validator(RoleValidator::class)
     * @Middleware(ValidatorMiddleware::class)
     */
    public function create()
    {
        $this->RoleService->create(Tools::Body());
        Tools::Oplog('新建权限集[基础]');
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/role/{id}", methods="put")
     * @Validator(RoleValidator::class)
     * @Middleware(ValidatorMiddleware::class)
     */
    public function update(int $id)
    {
        $this->RoleService->update($id, Tools::Body());
        Tools::Oplog('修改权限集[基础]');
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/role/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->RoleService->delete($id);
        Tools::Oplog('删除权限集[基础]');
        return Tools::Ok();
    }
}

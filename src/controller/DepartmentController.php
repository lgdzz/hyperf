<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\service\DepartmentService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares(
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * )
 */
class DepartmentController
{
    /**
     * @Inject
     * @var DepartmentService
     */
    protected $DepartmentService;

    /**
     * 部门列表
     * @RequestMapping(path="/l/department", methods="get")
     */
    public function index()
    {
        $result = $this->DepartmentService->index(Tools::Query());
        return Tools::ok($result);
    }

    /**
     * 部门详情
     * @RequestMapping(path="/l/department/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->DepartmentService->department($this->DepartmentService->findById($id));
        return Tools::ok($result);
    }

    /**
     * 创建部门
     * @RequestMapping(path="/l/department", methods="post")
     */
    public function create()
    {
        $this->DepartmentService->create(Tools::Body());
        Tools::Oplog('新建部门[基础]');
        return Tools::ok();
    }

    /**
     * 更新部门
     * @RequestMapping(path="/l/department/{id}", methods="put")
     */
    public function update(int $id)
    {
        $this->DepartmentService->update($id, Tools::Body());
        Tools::Oplog('修改部门[基础]');
        return Tools::ok();
    }

    /**
     * 删除部门
     * @RequestMapping(path="/l/department/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->DepartmentService->delete($id);
        Tools::Oplog('删除部门[基础]');
        return Tools::ok();
    }
}

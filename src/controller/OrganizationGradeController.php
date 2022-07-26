<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\middleware\AuthUserPowerMiddleware;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\service\OrganizationGradeService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares(
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * )
 */
class OrganizationGradeController
{
    /**
     * @Inject
     * @var OrganizationGradeService
     */
    protected $OrgGradeService;

    /**
     * 组织类型架构
     * @RequestMapping(path="/l/orggrade", methods="get")
     */
    public function index()
    {
        $result = $this->OrgGradeService->index(Tools::Query(['pid' => Tools::Org()->id]));
        return Tools::ok($result);
    }

    /**
     * 组织类型详情
     * @RequestMapping(path="/l/orggrade/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->OrgGradeService->grade($this->OrgGradeService->findById($id));
        return Tools::ok($result);
    }

    /**
     * 创建组织类型
     * @RequestMapping(path="/l/orggrade", methods="post")
     */
    public function create()
    {
        $this->OrgGradeService->create(Tools::Body());
        Tools::Oplog('新建组织类型[基础]');
        return Tools::ok();
    }

    /**
     * 更新组织类型
     * @RequestMapping(path="/l/orggrade/{id}", methods="put")
     */
    public function update(int $id)
    {
        $this->OrgGradeService->update($id, Tools::Body());
        Tools::Oplog('修改组织类型[基础]');
        return Tools::ok();
    }

    /**
     * 删除组织类型
     * @RequestMapping(path="/l/orggrade/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->OrgGradeService->delete($id);
        Tools::Oplog('删除组织类型[基础]');
        return Tools::ok();
    }
}

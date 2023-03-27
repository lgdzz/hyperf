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
use lgdz\hyperf\middleware\ValidatorMiddleware;
use lgdz\hyperf\service\OrganizationService;
use lgdz\hyperf\validator\AuthValidator;
use lgdz\hyperf\annotation\Validator;
use lgdz\hyperf\service\AuthService;
use lgdz\hyperf\service\UserService;
use lgdz\hyperf\service\AccountService;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares(
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 *     @Middleware(AuthUserPowerMiddleware::class)
 * )
 */
class OrganizationController
{
    /**
     * @Inject
     * @var OrganizationService
     */
    protected $OrgService;

    /**
     * 组织架构
     * @RequestMapping(path="/l/organization", methods="get")
     */
    public function index()
    {
        $result = $this->OrgService->index(Tools::Query(['pid' => Tools::Org()->id]));
        return Tools::ok($result);
    }

    /**
     * 组织详情
     * @RequestMapping(path="/l/organization/{id}", methods="get")
     */
    public function read(int $id)
    {
        $result = $this->OrgService->org($this->OrgService->findById($id));
        return Tools::ok($result);
    }

    /**
     * 创建组织
     * @RequestMapping(path="/l/organization", methods="post")
     */
    public function create()
    {
        $this->OrgService->create(Tools::Body());
        Tools::Oplog('新建组织[基础]');
        return Tools::ok();
    }

    /**
     * 更新组织
     * @RequestMapping(path="/l/organization/{id}", methods="put")
     */
    public function update(int $id)
    {
        $this->OrgService->update($id, Tools::Body());
        Tools::Oplog('修改组织[基础]');
        return Tools::ok();
    }

    /**
     * 更新组织扩展
     * @RequestMapping(path="/l/organization-extends/{id}", methods="patch")
     */
    public function patchExtends(int $id)
    {
        $this->OrgService->patchExtends($id, Tools::Body());
        Tools::Oplog('修改组织extends[基础]');
        return Tools::ok();
    }

    /**
     * 删除组织
     * @RequestMapping(path="/l/organization/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->OrgService->delete($id);
        Tools::Oplog('删除组织[基础]');
        return Tools::ok();
    }
}

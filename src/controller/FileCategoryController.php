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
use lgdz\hyperf\service\FileCategoryService;
use lgdz\hyperf\service\service;
use lgdz\hyperf\Tools;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class),
 * })
 */
class FileCategoryController
{
    /**
     * @Inject
     * @var FileCategoryService
     */
    protected $service;

    /**
     * @RequestMapping(path="/l/file-category", methods="get")
     */
    public function index()
    {
        $result = $this->service->index(Tools::Query(['org_id' => Tools::Org()->id]));
        return Tools::Ok($result);
    }

    /**
     * @RequestMapping(path="/l/file-category", methods="post")
     */
    public function create()
    {
        $this->service->create(Tools::Body(['org_id' => Tools::Org()->id]));
        Tools::Oplog('文件分类新增[基础]');
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/file-category/{id}", methods="put")
     */
    public function update(int $id)
    {
        $this->service->update($id, Tools::Body(['org_id' => Tools::Org()->id]));
        Tools::Oplog('文件分类修改[基础]');
        return Tools::Ok();
    }

    /**
     * @RequestMapping(path="/l/file-category/{id}", methods="delete")
     */
    public function delete(int $id)
    {
        $this->service->delete($id);
        Tools::Oplog('文件分类删除[基础]');
        return Tools::Ok();
    }
}
